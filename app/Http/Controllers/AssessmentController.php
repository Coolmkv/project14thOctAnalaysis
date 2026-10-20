<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssessmentController extends Controller
{
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