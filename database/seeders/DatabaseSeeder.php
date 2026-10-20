<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('Demo data is only seeded in local and testing environments.');

            return;
        }

        $counselor = User::firstOrCreate(
            ['email' => 'counselor@example.com'],
            [
                'name' => 'Jordan Lee',
                'password' => Hash::make(config('app.demo_counselor_password')),
            ],
        );

        $samples = [
            [
                'title' => 'Career interests check-in',
                'description' => 'A quick reflection to help students notice the activities and topics that energize them.',
                'status' => 'published',
                'questions' => [
                    ['What kind of challenge sounds most interesting?', ['Designing something new', 'Solving a tricky problem', 'Helping someone reach a goal']],
                    ['Which school activity would you choose?', ['Art, media, or making things', 'Science, technology, or puzzles', 'Community projects or peer support']],
                    ['What would you like to learn more about?', ['How ideas become products', 'How systems and data work', 'How people learn and grow']],
                ],
            ],
            [
                'title' => 'Strengths in action',
                'description' => 'Explore skills students already use and strengths they would like to develop.',
                'status' => 'published',
                'questions' => [
                    ['When working on a group project, what do you often contribute?', ['Fresh ideas and creative approaches', 'A plan that keeps everyone organized', 'Encouragement and listening']],
                    ['Which task feels satisfying to finish?', ['Making or improving something', 'Finding the answer to a question', 'Supporting a person or group']],
                    ['What would you like to get better at?', ['Presenting my ideas', 'Working with tools or technology', 'Leading or collaborating with others']],
                ],
            ],
            [
                'title' => 'My ideal learning environment',
                'description' => 'Help students think about settings and ways of learning that work well for them.',
                'status' => 'draft',
                'questions' => [
                    ['Where do you feel most focused?', ['A lively place where people share ideas', 'A quiet space for independent work', 'A hands-on space where I can move around']],
                    ['How do you prefer to learn something new?', ['Try it and learn by doing', 'Read, research, and take notes', 'Talk it through with someone']],
                    ['What pace feels comfortable for a project?', ['A few short milestones along the way', 'Time to explore before deciding', 'Working together with regular check-ins']],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $questions = $sample['questions'];
            unset($sample['questions']);

            $assessment = $counselor->assessments()->firstOrCreate(
                ['title' => $sample['title']],
                $sample,
            );

            if ($assessment->questions()->doesntExist()) {
                foreach ($questions as $index => [$prompt, $choices]) {
                    $assessment->questions()->create([
                        'prompt' => $prompt,
                        'choices' => $choices,
                        'position' => $index + 1,
                    ]);
                }
            }
        }
    }
}
