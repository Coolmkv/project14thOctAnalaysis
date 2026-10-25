<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function adminDashboard(): View
    {
        return view('admin.dashboard', [
            'staff' => User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_COUNSELOR, User::ROLE_GROUP_ADMIN])
                ->with('institutes')
                ->latest()
                ->get(),
            'institutes' => Institute::query()->withCount('users')->orderBy('name')->get(),
            'students' => User::query()
                ->where('role', User::ROLE_USER)
                ->with('institutes')
                ->withCount('assessmentSubmissions')
                ->latest()
                ->paginate(25, ['*'], 'students_page'),
            'assessments' => Assessment::query()
                ->with('author')
                ->withCount('submissions')
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }

    public function groupAdminDashboard(Request $request): View
    {
        $institutes = $request->user()->institutes()->orderBy('name')->get();
        $selectedInstitute = null;

        if ($institutes->isNotEmpty()) {
            $instituteId = $request->query('institute');
            $selectedInstitute = $instituteId
                ? $institutes->firstWhere('id', (int) $instituteId)
                : $institutes->first();

            abort_if($instituteId && ! $selectedInstitute, 404);
        }

        $students = $selectedInstitute
            ? $selectedInstitute->users()
                ->where('role', User::ROLE_USER)
                ->withCount('assessmentSubmissions')
                ->with(['assessmentSubmissions' => fn ($query) => $query->with('assessment')->latest('submitted_at')])
                ->orderBy('name')
                ->paginate(25)
            : null;

        return view('group-admin.dashboard', compact('institutes', 'selectedInstitute', 'students'));
    }

    public function createUser(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower((string) $request->input('email'))]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_COUNSELOR, User::ROLE_GROUP_ADMIN])],
            'password' => ['required', 'string', 'confirmed', Password::min(12)],
            'institute_ids' => ['required_if:role,'.User::ROLE_GROUP_ADMIN, 'array'],
            'institute_ids.*' => ['integer', 'distinct', 'exists:institutes,id'],
        ]);

        if (User::query()->whereRaw('LOWER(email) = ?', [$validated['email']])->exists()) {
            throw ValidationException::withMessages(['email' => 'This email already belongs to an account.']);
        }

        if ($validated['role'] === User::ROLE_GROUP_ADMIN && empty($validated['institute_ids'])) {
            throw ValidationException::withMessages([
                'institute_ids' => 'Assign at least one institute to this group admin.',
            ]);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        if ($user->hasRole(User::ROLE_GROUP_ADMIN)) {
            $user->institutes()->sync($validated['institute_ids']);
        }

        return redirect()->route('admin.dashboard')->with('status', 'Account created successfully.');
    }

    public function createInstitute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_name' => ['required', 'string', 'max:160', 'unique:institutes,name'],
        ]);

        Institute::create(['name' => $validated['institute_name']]);

        return redirect()->route('admin.dashboard')->with('status', 'Institute created successfully.');
    }

    public function importStudents(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_id' => ['required', 'integer', 'exists:institutes,id'],
            'students_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $institute = Institute::findOrFail($validated['institute_id']);
        $rows = $this->readStudentCsv($request->file('students_file')->getRealPath());
        $this->validateStudentRows($rows);
        $temporaryPassword = $this->createStudentAccounts($rows, $institute);

        return redirect()->route('admin.dashboard')
            ->with('status', count($rows).' student accounts imported into '.$institute->name.'.')
            ->with('temporary_password', $temporaryPassword);
    }

    public function importGroupStudents(Request $request, Institute $institute): RedirectResponse
    {
        abort_unless($request->user()->institutes()->whereKey($institute->id)->exists(), 403);

        $request->validate([
            'students_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->readStudentCsv($request->file('students_file')->getRealPath());
        $this->validateStudentRows($rows);
        $temporaryPassword = $this->createStudentAccounts($rows, $institute);

        return redirect()->route('group-admin.dashboard', ['institute' => $institute->id])
            ->with('status', count($rows).' student accounts imported into '.$institute->name.'.')
            ->with('temporary_password', $temporaryPassword);
    }

    /**
     * @return list<array{name: string, email: string}>
     */
    private function readStudentCsv(string|false $path): array
    {
        if (! $path || ! ($handle = fopen($path, 'r'))) {
            throw ValidationException::withMessages(['students_file' => 'The uploaded file could not be read.']);
        }

        try {
            $header = fgetcsv($handle, escape: '');
            if (! is_array($header)) {
                throw ValidationException::withMessages(['students_file' => 'The CSV file is empty.']);
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
            $header = array_map(fn ($value) => Str::lower(trim((string) $value)), $header);
            if (count($header) !== 2 || array_diff(['name', 'email'], $header) || count(array_unique($header)) !== 2) {
                throw ValidationException::withMessages([
                    'students_file' => 'Use a CSV with exactly these column headers: name,email.',
                ]);
            }

            $nameIndex = array_search('name', $header, true);
            $emailIndex = array_search('email', $header, true);
            $rows = [];
            $line = 1;

            while (($record = fgetcsv($handle, escape: '')) !== false) {
                $line++;
                if ($record === [null] || count(array_filter($record, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                if (count($rows) >= 500) {
                    throw ValidationException::withMessages(['students_file' => 'Import no more than 500 students at a time.']);
                }

                $row = [
                    'name' => trim((string) ($record[$nameIndex] ?? '')),
                    'email' => Str::lower(trim((string) ($record[$emailIndex] ?? ''))),
                ];
                if (count($record) !== 2) {
                    throw ValidationException::withMessages([
                        'students_file' => 'Row '.$line.': each row must contain exactly a name and an email address.',
                    ]);
                }

                $validator = Validator::make($row, [
                    'name' => ['required', 'string', 'max:120'],
                    'email' => ['required', 'string', 'email', 'max:255'],
                ]);
                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'students_file' => 'Row '.$line.': '.$validator->errors()->first(),
                    ]);
                }

                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['students_file' => 'The CSV contains no student rows.']);
        }

        return $rows;
    }

    /**
     * @param  list<array{name: string, email: string}>  $rows
     */
    private function validateStudentRows(array $rows): void
    {
        $emails = array_column($rows, 'email');
        if (count(array_unique($emails)) !== count($emails)) {
            throw ValidationException::withMessages(['students_file' => 'The CSV contains duplicate email addresses.']);
        }

        $placeholders = implode(',', array_fill(0, count($emails), '?'));
        if (User::query()->whereRaw('LOWER(email) IN ('.$placeholders.')', $emails)->exists()) {
            throw ValidationException::withMessages([
                'students_file' => 'An email address in this CSV already belongs to an account.',
            ]);
        }
    }

    /**
     * @param  list<array{name: string, email: string}>  $rows
     */
    private function createStudentAccounts(array $rows, Institute $institute): string
    {
        $temporaryPassword = Str::random(16);

        DB::transaction(function () use ($rows, $institute, $temporaryPassword): void {
            foreach ($rows as $row) {
                $student = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => User::ROLE_USER,
                    'password' => $temporaryPassword,
                    'must_change_password' => true,
                ]);
                $student->institutes()->attach($institute->id);
            }
        });

        return $temporaryPassword;
    }
}
