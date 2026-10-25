<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;

class RiaSecScorer
{
    public const ORDER = ['R', 'I', 'A', 'S', 'E', 'C'];

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

    public static function isRiaSecAssessment(?string $title = null, ?string $description = null): bool
    {
        $combined = strtolower(($title ?? '').' '.($description ?? ''));

        return str_contains($combined, 'riasec')
            || str_contains($combined, 'career interest')
            || str_contains($combined, 'interest inventory');
    }

    public static function calculateForAssessment(Assessment $assessment, AssessmentSubmission $submission): array
    {
        $scores = array_fill_keys(self::ORDER, 0);

        foreach ($assessment->questions as $question) {
            $selected = $submission->answers[$question->id] ?? null;
            $domain = self::detectDomain($selected);

            if ($domain !== null) {
                $scores[$domain] = ($scores[$domain] ?? 0) + 1;
            }
        }

        return $scores;
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
        $maxScore = max($values, 1);

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

        $normalized = strtolower($choice);

        foreach (self::DOMAIN_DETAILS as $code => $details) {
            $label = strtolower($details['label']);
            $short = strtolower($code);

            if (str_contains($normalized, $label) || str_contains($normalized, $short)) {
                return $code;
            }
        }

        return null;
    }
}
