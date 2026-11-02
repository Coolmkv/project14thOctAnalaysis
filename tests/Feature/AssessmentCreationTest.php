<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Support\RiaSecScorer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_authoring_requires_authentication(): void
    {
        $response = $this->get(route('assessments.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_assessment_with_questions(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_COUNSELOR]);

        $response = $this->actingAs($author)->post(route('assessments.store'), [
            'title' => 'Career interests check-in',
            'description' => 'Reflect on activities you enjoy.',
            'assessment_type' => 'general',
            'status' => 'draft',
            'questions' => [
                ['prompt' => 'What do you enjoy doing?', 'choices' => ['Designing', 'Helping', 'Investigating']],
                ['prompt' => 'Where do you like to work?', 'choices' => ['Outdoors', 'With a team']],
            ],
        ]);

        $response->assertRedirect(route('assessments.index'))
            ->assertSessionHas('status', 'Assessment saved successfully.');

        $assessment = Assessment::with('questions')->firstOrFail();
        $this->assertSame($author->id, $assessment->user_id);
        $this->assertSame('Career interests check-in', $assessment->title);
        $this->assertSame('general', $assessment->assessment_type);
        $this->assertCount(2, $assessment->questions);
        $this->assertSame(['Designing', 'Helping', 'Investigating'], $assessment->questions[0]->choices);
    }

    public function test_assessment_requires_at_least_one_question_with_two_choices(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_COUNSELOR]);

        $this->actingAs($author)
            ->from(route('assessments.create'))
            ->post(route('assessments.store'), [
                'title' => 'Incomplete assessment',
                'assessment_type' => 'general',
                'status' => 'draft',
                'questions' => [
                    ['prompt' => 'What interests you?', 'choices' => ['Only one']],
                ],
            ])
            ->assertRedirect(route('assessments.create'))
            ->assertSessionHasErrors(['questions.0.choices']);

        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_counselor_can_create_balanced_riasec_reflection(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_COUNSELOR]);
        $questions = RiaSecScorer::starterQuestions();

        $this->actingAs($author)
            ->post(route('assessments.store'), [
                'title' => 'My interest reflection',
                'description' => 'Explore activities that sound enjoyable.',
                'assessment_type' => 'riasec',
                'status' => 'draft',
                'questions' => $questions,
            ])
            ->assertRedirect(route('assessments.index'))
            ->assertSessionHas('status', 'Assessment saved successfully.');

        $assessment = Assessment::with('questions')->firstOrFail();
        $this->assertSame('riasec', $assessment->assessment_type);
        $this->assertCount(24, $assessment->questions);
        $this->assertSame(['1', '2', '3', '4', '5'], $assessment->questions[0]->choices);
        $this->assertSame('R', $assessment->questions[0]->riasec_domain);
        $this->assertSame(4, $assessment->questions->where('riasec_domain', 'R')->count());
    }

    public function test_riasec_reflection_requires_three_items_per_interest_area(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_COUNSELOR]);
        $questions = array_slice(RiaSecScorer::starterQuestions(), 0, 18);

        $this->actingAs($author)
            ->from(route('assessments.create'))
            ->post(route('assessments.store'), [
                'title' => 'Unbalanced RIASEC reflection',
                'assessment_type' => 'riasec',
                'status' => 'draft',
                'questions' => $questions,
            ])
            ->assertRedirect(route('assessments.create'))
            ->assertSessionHasErrors('questions');

        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_author_can_edit_existing_assessment(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_COUNSELOR]);
        $assessment = Assessment::factory()->create([
            'user_id' => $author->id,
            'title' => 'Original title',
            'description' => 'Original description',
            'status' => 'draft',
        ]);

        $assessment->questions()->createMany([
            ['prompt' => 'First question', 'choices' => ['A', 'B'], 'position' => 1],
            ['prompt' => 'Second question', 'choices' => ['C', 'D'], 'position' => 2],
        ]);

        $this->actingAs($author)
            ->put(route('assessments.update', $assessment), [
                'title' => 'Updated title',
                'description' => 'Updated description',
                'assessment_type' => 'general',
                'status' => 'published',
                'questions' => [
                    ['prompt' => 'Updated first question', 'choices' => ['One', 'Two']],
                    ['prompt' => 'Updated second question', 'choices' => ['Three', 'Four']],
                ],
            ])
            ->assertRedirect(route('assessments.index'))
            ->assertSessionHas('status', 'Assessment updated successfully.');

        $assessment->refresh();
        $this->assertSame('Updated title', $assessment->title);
        $this->assertSame('Updated description', $assessment->description);
        $this->assertSame('general', $assessment->assessment_type);
        $this->assertSame('published', $assessment->status);
        $this->assertSame(['One', 'Two'], $assessment->questions()->orderBy('position')->first()->choices);
        $this->assertSame(['Three', 'Four'], $assessment->questions()->orderBy('position')->skip(1)->first()->choices);
    }

    public function test_assessment_questions_cannot_change_after_a_student_submission(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_COUNSELOR]);
        $student = User::factory()->create();
        $assessment = Assessment::factory()->create([
            'user_id' => $author->id,
            'title' => 'Published reflection',
            'assessment_type' => 'general',
        ]);
        $question = $assessment->questions()->create([
            'prompt' => 'Original prompt',
            'choices' => ['One', 'Two'],
            'position' => 1,
        ]);
        $assessment->submissions()->create([
            'user_id' => $student->id,
            'answers' => [$question->id => 'One'],
            'submitted_at' => now(),
        ]);

        $this->actingAs($author)
            ->from(route('assessments.edit', $assessment))
            ->put(route('assessments.update', $assessment), [
                'title' => 'Changed reflection',
                'assessment_type' => 'general',
                'status' => 'published',
                'questions' => [
                    ['prompt' => 'Changed prompt', 'choices' => ['Three', 'Four']],
                ],
            ])
            ->assertRedirect(route('assessments.edit', $assessment))
            ->assertSessionHasErrors('questions');

        $this->assertDatabaseHas('assessments', [
            'id' => $assessment->id,
            'title' => 'Published reflection',
        ]);
        $this->assertDatabaseHas('assessment_questions', [
            'id' => $question->id,
            'prompt' => 'Original prompt',
        ]);
    }
}
