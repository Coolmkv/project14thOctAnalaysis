<?php

namespace Tests\Feature;

use App\Models\Assessment;
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
        $author = User::factory()->create();

        $response = $this->actingAs($author)->post(route('assessments.store'), [
            'title' => 'Career interests check-in',
            'description' => 'Reflect on activities you enjoy.',
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
        $this->assertCount(2, $assessment->questions);
        $this->assertSame(['Designing', 'Helping', 'Investigating'], $assessment->questions[0]->choices);
    }

    public function test_assessment_requires_at_least_one_question_with_two_choices(): void
    {
        $author = User::factory()->create();

        $this->actingAs($author)
            ->from(route('assessments.create'))
            ->post(route('assessments.store'), [
                'title' => 'Incomplete assessment',
                'status' => 'draft',
                'questions' => [
                    ['prompt' => 'What interests you?', 'choices' => ['Only one']],
                ],
            ])
            ->assertRedirect(route('assessments.create'))
            ->assertSessionHasErrors(['questions.0.choices']);

        $this->assertDatabaseCount('assessments', 0);
    }
}