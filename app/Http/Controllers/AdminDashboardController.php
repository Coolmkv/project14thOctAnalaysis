<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
        $start = now()->subDays(6)->startOfDay();
        $submissionCounts = DB::table('assessment_submissions')
            ->selectRaw('DATE(submitted_at) as submission_date, COUNT(*) as total')
            ->where('submitted_at', '>=', $start)
            ->groupBy('submission_date')
            ->pluck('total', 'submission_date');

        $submissionTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($submissionCounts): array {
            $date = now()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'count' => (int) $submissionCounts->get($date->toDateString(), 0),
            ];
        });

        $institutes = Institute::query()
            ->withCount(['users' => fn ($query) => $query->where('role', User::ROLE_USER)])
            ->orderByDesc('users_count')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'instituteCount' => Institute::count(),
            'staffCount' => User::query()->whereIn('role', [
                User::ROLE_ADMIN,
                User::ROLE_COUNSELOR,
                User::ROLE_GROUP_ADMIN,
            ])->count(),
            'studentCount' => User::query()->where('role', User::ROLE_USER)->count(),
            'submissionCount' => DB::table('assessment_submissions')->count(),
            'submissionTrend' => $submissionTrend,
            'instituteChart' => $institutes,
            'assessments' => Assessment::query()
                ->with('author')
                ->withCount('submissions')
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    public function createAccountPage(): View
    {
        return view('admin.accounts.create', [
            'institutes' => Institute::query()->orderBy('name')->get(),
        ]);
    }

    public function institutesPage(): View
    {
        return view('admin.institutes.index', [
            'institutes' => Institute::query()->withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function importStudentsPage(): View
    {
        return view('admin.students.import', [
            'institutes' => Institute::query()->orderBy('name')->get(),
        ]);
    }

    public function staffPage(): View
    {
        return view('admin.staff.index', [
            'staffCount' => User::query()->whereIn('role', [
                User::ROLE_ADMIN,
                User::ROLE_COUNSELOR,
                User::ROLE_GROUP_ADMIN,
            ])->count(),
        ]);
    }

    public function studentsPage(): View
    {
        return view('admin.students.index', [
            'studentCount' => User::query()->where('role', User::ROLE_USER)->count(),
        ]);
    }

    public function staffData(Request $request): JsonResponse
    {
        return $this->userDataTable($request, true);
    }

    public function studentData(Request $request): JsonResponse
    {
        return $this->userDataTable($request, false);
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

        return redirect()->route('admin.accounts.create')->with('status', 'Account created successfully.');
    }

    public function createInstitute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'institute_name' => ['required', 'string', 'max:160', 'unique:institutes,name'],
        ]);

        Institute::create(['name' => $validated['institute_name']]);

        return redirect()->route('admin.institutes.index')->with('status', 'Institute created successfully.');
    }

    public function deleteInstitute(Institute $institute): RedirectResponse
    {
        $name = $institute->name;
        $institute->delete();

        return redirect()->route('admin.institutes.index')
            ->with('status', $name.' was deleted. Student accounts and assessment results were kept.');
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

        return redirect()->route('admin.students.import')
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

    private function userDataTable(Request $request, bool $staff): JsonResponse
    {
        $roles = $staff
            ? [User::ROLE_ADMIN, User::ROLE_COUNSELOR, User::ROLE_GROUP_ADMIN]
            : [User::ROLE_USER];
        $query = User::query()->whereIn('role', $roles);
        $recordsTotal = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('role', 'like', '%'.str_replace(' ', '_', $search).'%');
            });
        }

        $recordsFiltered = (clone $query)->count();
        $columns = $staff
            ? ['name', 'email', 'role', 'institutes']
            : ['name', 'email', 'institutes', 'assessment_submissions_count'];
        $orderIndex = filter_var($request->input('order.0.column', 0), FILTER_VALIDATE_INT);
        $orderIndex = $orderIndex !== false && isset($columns[$orderIndex]) ? $orderIndex : 0;
        $direction = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        $length = filter_var($request->input('length', 10), FILTER_VALIDATE_INT);
        $length = $length !== false ? min(max($length, 1), 100) : 10;
        $start = filter_var($request->input('start', 0), FILTER_VALIDATE_INT);
        $start = $start !== false ? max($start, 0) : 0;

        if ($columns[$orderIndex] !== 'institutes') {
            $query->orderBy($columns[$orderIndex], $direction);
        }
        if ($staff) {
            $query->with('institutes');
        } else {
            $query->with('institutes')->withCount('assessmentSubmissions');
        }

        $data = $query->skip($start)->take($length)->get()->map(function (User $user) use ($staff): array {
            $row = [
                'name' => $user->name,
                'email' => $user->email,
                'institutes' => $user->institutes->pluck('name')->join(', ') ?: '—',
            ];

            if ($staff) {
                $row['role'] = str($user->role)->replace('_', ' ')->title()->toString();
            } else {
                $row['completed_assessments'] = $user->assessment_submissions_count;
            }

            return $row;
        });

        return response()->json([
            'draw' => max(0, (int) $request->input('draw', 0)),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
