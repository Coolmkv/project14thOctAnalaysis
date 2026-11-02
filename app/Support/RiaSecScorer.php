<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;

class RiaSecScorer
{
    public const ORDER = ['R', 'I', 'A', 'S', 'E', 'C'];

    public const RATING_OPTIONS = [
        '1' => 'Not interested',
        '2' => 'Slightly interested',
        '3' => 'Moderately interested',
        '4' => 'Very interested',
        '5' => 'Extremely interested',
    ];

    public const DOMAIN_DETAILS = [
        'R' => [
            'label' => 'Realistic',
            'description' => 'Hands-on work with tools, machines, outdoor tasks, or practical problem-solving.',
        ],
        'I' => [
            'label' => 'Investigative',
            'description' => 'Curiosity, analysis, research, and understanding how systems or ideas work.',
        ],
        'A' => [
            'label' => 'Artistic',
            'description' => 'Creativity, self-expression, design, and original thinking.',
        ],
        'S' => [
            'label' => 'Social',
            'description' => 'Helping people, teaching, mentoring, and building relationships.',
        ],
        'E' => [
            'label' => 'Enterprising',
            'description' => 'Leadership, influence, strategy, and moving ideas into action.',
        ],
        'C' => [
            'label' => 'Conventional',
            'description' => 'Structure, organization, precision, and working with clear systems.',
        ],
    ];

    public static function isRiaSecAssessment(?string $title = null, ?string $description = null, ?string $assessmentType = null): bool
    {
        if ($assessmentType !== null) {
            return $assessmentType === 'riasec';
        }

        $combined = strtolower(($title ?? '').' '.($description ?? ''));

        return str_contains($combined, 'riasec')
            || str_contains($combined, 'interest inventory');
    }

    public static function calculateForAssessment(Assessment $assessment, AssessmentSubmission $submission): array
    {
        $scores = array_fill_keys(self::ORDER, 0);
        $counts = array_fill_keys(self::ORDER, 0);
        $hasDomainTaggedQuestions = false;

        foreach ($assessment->questions as $question) {
            $selected = $submission->answers[$question->id] ?? null;

            if (in_array($question->riasec_domain, self::ORDER, true)) {
                $hasDomainTaggedQuestions = true;
                $rating = filter_var($selected, FILTER_VALIDATE_INT);

                if ($rating !== false && $rating >= 1 && $rating <= 5) {
                    $scores[$question->riasec_domain] += $rating;
                    $counts[$question->riasec_domain]++;
                }

                continue;
            }

            $domain = self::detectDomain($selected);

            if ($domain !== null) {
                $scores[$domain] = ($scores[$domain] ?? 0) + 1;
            }
        }

        if ($hasDomainTaggedQuestions) {
            foreach (self::ORDER as $domain) {
                $scores[$domain] = $counts[$domain] > 0
                    ? round($scores[$domain] / $counts[$domain], 2)
                    : 0;
            }
        }

        return $scores;
    }

    public static function starterQuestions(): array
    {
        $prompts = [
            'R' => [
                'Build or repair a practical object using tools or equipment.',
                'Operate or adjust machines, devices, or technical equipment.',
                'Create something useful by working directly with materials.',
                'Do a hands-on task outdoors or in a physical work setting.',
            ],
            'I' => [
                'Investigate a question by gathering and comparing evidence.',
                'Use data or observations to find patterns and explain why they occur.',
                'Explore how a scientific, technical, or natural system works.',
                'Plan a fair test to compare possible explanations or solutions.',
            ],
            'A' => [
                'Create an original visual, digital, or performing-arts piece.',
                'Write a story, song, script, or other expressive piece.',
                'Develop an imaginative design that communicates an idea.',
                'Experiment with different creative approaches before choosing one.',
            ],
            'S' => [
                'Teach someone a skill or help them understand a new idea.',
                'Support a person as they work through a difficult situation.',
                'Help a group feel included, listened to, and able to cooperate.',
                "Contribute to an activity that improves people's wellbeing.",
            ],
            'E' => [
                'Present an idea and persuade others to consider it.',
                'Organize people and resources to move a project toward a goal.',
                'Lead a group discussion and help the group make a decision.',
                'Plan a small venture, event, or initiative and promote it.',
            ],
            'C' => [
                'Organize records or information so they are easy to retrieve.',
                'Follow a detailed process to complete work accurately.',
                'Check documents or results against clear standards.',
                'Use schedules, lists, or spreadsheets to track several tasks.',
            ],
        ];

        $questions = [];
        foreach (self::ORDER as $domain) {
            foreach ($prompts[$domain] as $prompt) {
                $questions[] = ['prompt' => $prompt, 'riasec_domain' => $domain];
            }
        }

        return $questions;
    }

    public static function isDomainTagged(Assessment $assessment): bool
    {
        $questions = $assessment->relationLoaded('questions')
            ? $assessment->questions
            : $assessment->questions()->get();

        return $questions->contains(
            fn ($question) => in_array($question->riasec_domain, self::ORDER, true),
        );
    }

    public static function ratingLabel(?string $rating): ?string
    {
        return $rating === null ? null : (self::RATING_OPTIONS[$rating] ?? null);
    }

    public static function topDomains(array $scores): array
    {
        $entries = [];

        foreach ($scores as $code => $score) {
            $entries[$code] = [
                'code' => $code,
                'label' => self::DOMAIN_DETAILS[$code]['label'],
                'score' => $score,
            ];
        }

        uasort($entries, fn (array $left, array $right) => $right['score'] <=> $left['score']);

        return array_values($entries);
    }

    public static function chartPoints(array $scores, float $center = 150.0, float $radius = 90.0): array
    {
        $values = array_values($scores);
        $maxScore = max(1, ...$values);

        $points = [];
        foreach (self::ORDER as $index => $code) {
            $angle = -M_PI / 2 + ($index / count(self::ORDER)) * (2 * M_PI);
            $distance = $radius * min(1.0, ($scores[$code] ?? 0) / $maxScore);
            $x = $center + cos($angle) * $distance;
            $y = $center + sin($angle) * $distance;
            $points[$code] = [$x, $y];
        }

        return $points;
    }

    protected static function detectDomain(?string $choice): ?string
    {
        if (! is_string($choice) || $choice === '') {
            return null;
        }

        $normalized = strtolower(trim($choice));

        foreach (self::DOMAIN_DETAILS as $code => $details) {
            $label = strtolower($details['label']);

            if (preg_match('/\b'.preg_quote($label, '/').'\b/i', $normalized) === 1
                || $normalized === strtolower($code)
                || preg_match('/[\[(]\s*'.preg_quote($code, '/').'\s*[\])]/i', $normalized) === 1) {
                return $code;
            }
        }

        return null;
    }
}
