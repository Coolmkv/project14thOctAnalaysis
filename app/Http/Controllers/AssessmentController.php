<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function studentIndex(Request $request): View
    {
        $assessments = Assessment::query()
            ->where('status', 'published')
            ->withCount('questions')
            ->with(['submissions' => fn ($query) => $query->where('user_id', $request->user()->id)])
            ->latest()
            ->get();

        return view('student.assessments.index', compact('assessments'));
    }

    public function studentShow(Request $request, Assessment $assessment): View
    {
        abort_unless($assessment->status === 'published', 404);

        $submission = $assessment->submissions()
            ->where('user_id', $request->user()->id)
            ->first();

        return view('student.assessments.show', [
            'assessment' => $assessment->load('questions'),
            'submission' => $submission,
        ]);
    }

    public function studentSubmit(Request $request, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->status === 'published', 404);

        if ($assessment->submissions()->where('user_id', $request->user()->id)->exists()) {
            return redirect()->route('student.assessments.show', $assessment)
                ->with('status', 'You have already completed this assessment.');
        }

        $questions = $assessment->questions()->get();
        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:'.$questions->count()],
            'answers.*' => ['required', 'string', 'max:160'],
        ]);

        $submittedAnswers = $validated['answers'];
        $expectedIds = $questions->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
        $submittedIds = collect(array_keys($submittedAnswers))->map(fn ($id) => (string) $id)->sort()->values()->all();

        if ($submittedIds !== $expectedIds) {
            throw ValidationException::withMessages(['answers' => 'Please answer every question in this assessment.']);
        }

        foreach ($questions as $question) {
            if (! in_array($submittedAnswers[$question->id] ?? null, $question->choices, true)) {
                throw ValidationException::withMessages([
                    'answers.'.$question->id => 'Choose one of the listed options.',
                ]);
            }
        }

        $assessment->submissions()->create([
            'user_id' => $request->user()->id,
            'answers' => $submittedAnswers,
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.assessments.show', $assessment)
            ->with('status', 'Your answers have been saved. You can return to the assessment list at any time.');
    }

    public function index(Request $request): View
    {
        $assessments = $request->user()->assessments()
            ->withCount('questions')
            ->latest()
            ->get();

        return view('assessments.index', compact('assessments'));
    }

    public function create(): View
    {
        return view('assessments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'questions' => ['required', 'array', 'min:1', 'max:30'],
            'questions.*.prompt' => ['required', 'string', 'max:1000'],
            'questions.*.choices' => ['required', 'array', 'min:2', 'max:8'],
            'questions.*.choices.*' => ['required', 'string', 'max:160'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $assessment = $request->user()->assessments()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            foreach ($validated['questions'] as $position => $question) {
                $assessment->questions()->create([
                    'prompt' => $question['prompt'],
                    'choices' => array_values($question['choices']),
                    'position' => $position + 1,
                ]);
            }
        });

        return redirect()->route('assessments.index')->with('status', 'Assessment saved successfully.');
    }
}