<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_links_students_to_login_and_registration(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertSee(route('counselor.login'));
    }

    public function test_student_portal_lists_only_published_assessments(): void
    {
        $student = User::factory()->create();
        [$published] = $this->makeAssessment();
        [$draft] = $this->makeAssessment('draft assessment', 'draft');

        $this->actingAs($student)
            ->get(route('student.assessments.index'))
            ->assertOk()
            ->assertSeeText($published->title)
            ->assertDontSeeText($draft->title);
    }

    public function test_student_can_submit_answers_that_match_the_question_choices(): void
    {
        $student = User::factory()->create();
        [$assessment, $questions] = $this->makeAssessment();

        $this->actingAs($student)
            ->post(route('student.assessments.submit', $assessment), [
                'answers' => [
                    $questions[0]->id => 'Making things',
                    $questions[1]->id => 'Working with people',
                ],
            ])
            ->assertRedirect(route('student.assessments.show', $assessment))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('assessment_submissions', [
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
        ]);
        $this->assertSame(1, $assessment->submissions()->count());
    }

    public function test_student_cannot_submit_an_answer_not_offered_by_the_assessment(): void
    {
        $student = User::factory()->create();
        [$assessment, $questions] = $this->makeAssessment();

        $this->actingAs($student)
            ->from(route('student.assessments.show', $assessment))
            ->post(route('student.assessments.submit', $assessment), [
                'answers' => [
                    $questions[0]->id => 'Invented answer',
                    $questions[1]->id => 'Working with people',
                ],
            ])
            ->assertRedirect(route('student.assessments.show', $assessment))
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('assessment_submissions', 0);
    }

    public function test_student_cannot_open_draft_assessment_or_counselor_authoring(): void
    {
        $student = User::factory()->create();
        $draft = $this->makeAssessment('draft assessment', 'draft');

        $this->actingAs($student)
            ->get(route('student.assessments.show', $draft))
            ->assertNotFound();

        $this->actingAs($student)
            ->get(route('assessments.create'))
            ->assertForbidden();
    }

    public function test_a_student_can_only_submit_an_assessment_once(): void
    {
        $student = User::factory()->create();
        [$assessment, $questions] = $this->makeAssessment();
        $answers = [
            $questions[0]->id => 'Making things',
            $questions[1]->id => 'Working with people',
        ];

        $this->actingAs($student)->post(route('student.assessments.submit', $assessment), ['answers' => $answers]);
        $this->actingAs($student)
            ->post(route('student.assessments.submit', $assessment), ['answers' => $answers])
            ->assertSessionHas('status', 'You have already completed this assessment.');

        $this->assertDatabaseCount('assessment_submissions', 1);
    }

    /** @return array{0: Assessment, 1: array} */
    private function makeAssessment(string $title = 'Published reflection', string $status = 'published'): array
    {
        $counselor = User::factory()->create(['role' => User::ROLE_COUNSELOR]);
        $assessment = $counselor->assessments()->create([
            'title' => $title,
            'description' => 'A thoughtful reflection.',
            'status' => $status,
        ]);

        $questions = [
            $assessment->questions()->create([
                'prompt' => 'What do you enjoy?',
                'choices' => ['Making things', 'Solving puzzles'],
                'position' => 1,
            ]),
            $assessment->questions()->create([
                'prompt' => 'How do you like to work?',
                'choices' => ['Working with people', 'Working independently'],
                'position' => 2,
            ]),
        ];

        return [$assessment, $questions];
    }
}