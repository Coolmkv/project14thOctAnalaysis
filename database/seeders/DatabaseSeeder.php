<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\User;
use App\Support\RiaSecScorer;
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
        $counselor->forceFill(['role' => User::ROLE_COUNSELOR])->save();

        User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make(config('app.demo_student_password')),
                'role' => User::ROLE_USER,
            ],
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Site Administrator',
                'password' => Hash::make(config('app.demo_admin_password')),
                'role' => User::ROLE_ADMIN,
            ],
        );
        $admin->forceFill(['role' => User::ROLE_ADMIN])->save();

        $institute = Institute::firstOrCreate(['name' => 'Demo Institute']);
        $groupAdmin = User::firstOrCreate(
            ['email' => 'group-admin@example.com'],
            [
                'name' => 'Demo Group Admin',
                'password' => Hash::make(config('app.demo_group_admin_password')),
                'role' => User::ROLE_GROUP_ADMIN,
            ],
        );
        $groupAdmin->forceFill(['role' => User::ROLE_GROUP_ADMIN])->save();
        $groupAdmin->institutes()->syncWithoutDetaching([$institute->id]);

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
                'title' => 'RIASEC Activity Interest Reflection',
                'description' => 'An exploratory reflection on how much students would enjoy a range of activities across six broad interest areas. This is not a validated career test.',
                'assessment_type' => 'riasec',
                'status' => 'published',
                'questions' => array_map(
                    fn (array $question) => [
                        $question['prompt'],
                        array_keys(RiaSecScorer::RATING_OPTIONS),
                        $question['riasec_domain'],
                    ],
                    RiaSecScorer::starterQuestions(),
                ),
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

            $isRiaSec = ($sample['assessment_type'] ?? 'general') === 'riasec';
            $assessment = $isRiaSec
                ? $counselor->assessments()
                    ->whereIn('title', [$sample['title'], 'RIASEC Career Interest Inventory'])
                    ->first()
                : null;
            $assessment ??= $counselor->assessments()->firstOrCreate(
                ['title' => $sample['title']],
                $sample,
            );
            $hasDomainTaggedQuestions = $assessment->questions()->whereNotNull('riasec_domain')->exists();

            if ($isRiaSec && ! $hasDomainTaggedQuestions && $assessment->submissions()->exists()) {
                $sample['title'] = 'RIASEC Activity Interest Reflection (New)';
                $assessment = $counselor->assessments()->firstOrCreate(
                    ['title' => $sample['title']],
                    $sample,
                );
                $hasDomainTaggedQuestions = $assessment->questions()->whereNotNull('riasec_domain')->exists();
            }

            if ($isRiaSec && ! $assessment->submissions()->exists()) {
                $assessment->update([
                    'title' => $sample['title'],
                    'description' => $sample['description'],
                    'assessment_type' => 'riasec',
                    'status' => $sample['status'],
                ]);
            } elseif ($assessment->assessment_type !== 'general') {
                $assessment->update(['assessment_type' => 'general']);
            }

            if ($assessment->questions()->doesntExist() || ($isRiaSec && ! $hasDomainTaggedQuestions)) {
                $assessment->questions()->delete();

                foreach ($questions as $index => $questionData) {
                    [$prompt, $choices, $domain] = array_pad($questionData, 3, null);
                    $assessment->questions()->create([
                        'prompt' => $prompt,
                        'choices' => $choices,
                        'position' => $index + 1,
                        'riasec_domain' => $domain,
                    ]);
                }
            }
        }
    }
}
