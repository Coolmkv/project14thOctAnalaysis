<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\User;
use App\Support\RiaSecScorer;
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

        $riaSecProfile = [];
        if ($submission && RiaSecScorer::isRiaSecAssessment($assessment->title, $assessment->description, $assessment->assessment_type)) {
            $riaSecProfile = RiaSecScorer::calculateForAssessment($assessment->load('questions'), $submission);
        }

        return view('student.assessments.show', [
            'assessment' => $assessment->load('questions'),
            'submission' => $submission,
            'riaSecProfile' => $riaSecProfile,
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
        $usesRatingScale = $assessment->assessment_type === 'riasec'
            && $questions->contains(fn ($question) => in_array($question->riasec_domain, RiaSecScorer::ORDER, true));
        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:'.$questions->count()],
            'answers.*' => $usesRatingScale
                ? ['required', 'integer', 'between:1,5']
                : ['required', 'string', 'max:160'],
        ]);

        $submittedAnswers = $validated['answers'];
        $expectedIds = $questions->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
        $submittedIds = collect(array_keys($submittedAnswers))->map(fn ($id) => (string) $id)->sort()->values()->all();

        if ($submittedIds !== $expectedIds) {
            throw ValidationException::withMessages(['answers' => 'Please answer every question in this assessment.']);
        }

        foreach ($questions as $question) {
            if (! $usesRatingScale && ! in_array($submittedAnswers[$question->id] ?? null, $question->choices, true)) {
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
            ->withCount('submissions')
            ->latest()
            ->get();

        return view('assessments.index', compact('assessments'));
    }

    public function results(Request $request, Assessment $assessment): View
    {
        abort_unless($assessment->user_id === $request->user()->id || $request->user()->hasRole(User::ROLE_ADMIN), 403);

        $assessment->load(['questions', 'submissions.student']);

        $studentSummaries = $assessment->submissions
            ->map(function ($submission) use ($assessment) {
                $scores = RiaSecScorer::calculateForAssessment($assessment, $submission);
                $top = RiaSecScorer::topDomains($scores);

                return [
                    'student' => $submission->student,
                    'submitted_at' => $submission->submitted_at,
                    'scores' => $scores,
                    'top' => $top,
                    'answers' => $submission->answers,
                ];
            })
            ->values();

        return view('assessments.results', [
            'assessment' => $assessment,
            'isRiaSecAssessment' => RiaSecScorer::isRiaSecAssessment($assessment->title, $assessment->description, $assessment->assessment_type),
            'isRatingScaleAssessment' => RiaSecScorer::isDomainTagged($assessment),
            'studentSummaries' => $studentSummaries,
        ]);
    }

    public function create(): View
    {
        return view('assessments.create', [
            'questionsData' => [],
            'starterQuestions' => RiaSecScorer::starterQuestions(),
            'hasSubmissions' => false,
        ]);
    }

    public function edit(Request $request, Assessment $assessment): View
    {
        abort_unless($assessment->user_id === $request->user()->id || $request->user()->hasRole(User::ROLE_ADMIN), 403);

        $assessment->load('questions');
        $hasSubmissions = $assessment->submissions()->exists();
        $questionsData = $assessment->questions
            ->map(fn ($question) => [
                'prompt' => $question->prompt,
                'choices' => $question->choices,
                'riasec_domain' => $question->riasec_domain,
            ])
            ->values()
            ->all();

        if (! $hasSubmissions && $assessment->assessment_type === 'riasec' && ! RiaSecScorer::isDomainTagged($assessment)) {
            $questionsData = RiaSecScorer::starterQuestions();
        }

        return view('assessments.edit', [
            'assessment' => $assessment,
            'questionsData' => $questionsData,
            'starterQuestions' => RiaSecScorer::starterQuestions(),
            'hasSubmissions' => $hasSubmissions,
        ]);
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        abort_unless($assessment->user_id === $request->user()->id || $request->user()->hasRole(User::ROLE_ADMIN), 403);

        $validated = $this->validateAssessment($request, $assessment);

        DB::transaction(function () use ($assessment, $validated): void {
            $hasSubmissions = $assessment->submissions()->exists();

            $assessment->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'assessment_type' => $validated['assessment_type'],
                'status' => $validated['status'],
            ]);

            if (! $hasSubmissions) {
                $this->syncQuestions($assessment, $validated['questions'], $validated['assessment_type']);
            }
        });

        return redirect()->route('assessments.index')->with('status', 'Assessment updated successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAssessment($request);

        DB::transaction(function () use ($request, $validated): void {
            $assessment = $request->user()->assessments()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'assessment_type' => $validated['assessment_type'],
                'status' => $validated['status'],
            ]);

            $this->syncQuestions($assessment, $validated['questions'], $validated['assessment_type']);
        });

        return redirect()->route('assessments.index')->with('status', 'Assessment saved successfully.');
    }

    private function validateAssessment(Request $request, ?Assessment $assessment = null): array
    {
        if ($assessment && $assessment->submissions()->exists()) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:160'],
                'description' => ['nullable', 'string', 'max:2000'],
                'assessment_type' => ['required', Rule::in(['general', 'riasec'])],
                'status' => ['required', Rule::in(['draft', 'published'])],
            ]);

            if ($request->has('questions')) {
                throw ValidationException::withMessages([
                    'questions' => 'This assessment already has student responses. Create a new assessment to change its questions and keep those results accurate.',
                ]);
            }

            $validated['assessment_type'] = $assessment->assessment_type;
            $validated['questions'] = $assessment->questions()
                ->get()
                ->map(fn ($question) => [
                    'prompt' => $question->prompt,
                    'choices' => $question->choices,
                    'riasec_domain' => $question->riasec_domain,
                ])
                ->all();

            return $validated;
        }

        $isRiaSec = $request->input('assessment_type') === 'riasec';
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assessment_type' => ['required', Rule::in(['general', 'riasec'])],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'questions' => ['required', 'array', $isRiaSec ? 'min:18' : 'min:1', 'max:30'],
            'questions.*.prompt' => ['required', 'string', 'max:1000'],
        ];

        if ($isRiaSec) {
            $rules['questions.*.riasec_domain'] = ['required', Rule::in(RiaSecScorer::ORDER)];
        } else {
            $rules['questions.*.choices'] = ['required', 'array', 'min:2', 'max:8'];
            $rules['questions.*.choices.*'] = ['required', 'string', 'max:160'];
        }

        $validated = $request->validate($rules);

        if ($isRiaSec) {
            $domainCounts = array_count_values(array_column($validated['questions'], 'riasec_domain'));
            $underrepresented = array_filter(
                RiaSecScorer::ORDER,
                fn (string $domain) => ($domainCounts[$domain] ?? 0) < 3,
            );

            if ($underrepresented !== []) {
                throw ValidationException::withMessages([
                    'questions' => 'Add at least three activity prompts for each of the six RIASEC interest areas.',
                ]);
            }
        }

        return $validated;
    }

    private function syncQuestions(Assessment $assessment, array $questions, string $assessmentType): void
    {
        $assessment->questions()->delete();

        foreach (array_values($questions) as $position => $question) {
            $isRiaSec = $assessmentType === 'riasec';
            $assessment->questions()->create([
                'prompt' => $question['prompt'],
                'choices' => $isRiaSec
                    ? array_keys(RiaSecScorer::RATING_OPTIONS)
                    : array_values($question['choices']),
                'position' => $position + 1,
                'riasec_domain' => $isRiaSec ? $question['riasec_domain'] : null,
            ]);
        }
    }

}