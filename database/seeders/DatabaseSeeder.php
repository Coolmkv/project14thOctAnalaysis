<?php

namespace Database\Seeders;

use App\Models\Institute;
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
                'title' => 'RIASEC Career Interest Inventory',
                'description' => 'A structured RIASEC assessment to help students identify the kinds of work environments and tasks that may fit them best.',
                'status' => 'published',
                'questions' => [
                    ['Which kind of task sounds most engaging?', ['Realistic: Building, fixing, or working with tools', 'Investigative: Researching, testing, or solving complex problems', 'Artistic: Designing, creating, or expressing ideas in a visual or creative way', 'Social: Helping, teaching, or supporting other people', 'Enterprising: Leading projects, influencing others, or taking initiative', 'Conventional: Organizing, planning, or keeping a system running smoothly']],
                    ['What kind of work environment do you prefer?', ['Realistic: Hands-on work with materials, equipment, or physical tasks', 'Investigative: Structured problem-solving and experimentation', 'Artistic: Flexible, creative spaces with room for originality', 'Social: Collaborative environments where people are supported', 'Enterprising: Fast-moving settings with goals, leadership, and decision-making', 'Conventional: Clear routines, procedures, and organized systems']],
                    ['Which style of learning feels most natural?', ['Realistic: Learning by doing or practicing a skill directly', 'Investigative: Learning by asking questions and exploring ideas', 'Artistic: Learning by experimenting and creating', 'Social: Learning through conversation, feedback, and teamwork', 'Enterprising: Learning through challenges, momentum, and action', 'Conventional: Learning through clear steps, checklists, and structure']],
                    ['What kind of project would you most enjoy?', ['Realistic: Making or improving something practical', 'Investigative: Understanding a cause, pattern, or technical problem', 'Artistic: Creating a product, presentation, or experience', 'Social: Supporting a team, community, or person', 'Enterprising: Launching an idea or leading a plan forward', 'Conventional: Managing details, documentation, and quality standards']],
                    ['Which type of problem do you enjoy solving?', ['Realistic: Mechanical or hands-on challenges', 'Investigative: Scientific, analytical, or research-based questions', 'Artistic: Creative or expressive challenges', 'Social: Personal, communication, or community challenges', 'Enterprising: Strategic or competitive challenges', 'Conventional: Process, data, or organization challenges']],
                    ['When you imagine your future career, what feels most appealing?', ['Realistic: Building, repairing, operating, or making things happen', 'Investigative: Discovering, understanding, and improving systems', 'Artistic: Designing, creating, and bringing ideas to life', 'Social: Supporting, guiding, and connecting with people', 'Enterprising: Setting direction, leading change, and influencing outcomes', 'Conventional: Organizing work, managing information, and keeping things reliable']],
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
