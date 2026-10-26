<?php

namespace Tests\Unit;

use App\Support\RiaSecScorer;
use PHPUnit\Framework\TestCase;

class RiaSecScorerTest extends TestCase
{
    public function test_chart_points_use_the_highest_score_as_the_scale(): void
    {
        $points = RiaSecScorer::chartPoints([
            'R' => 3,
            'I' => 0,
            'A' => 0,
            'S' => 0,
            'E' => 0,
            'C' => 0,
        ]);

        $this->assertEqualsWithDelta(150.0, $points['R'][0], 0.001);
        $this->assertEqualsWithDelta(60.0, $points['R'][1], 0.001);
        $this->assertEqualsWithDelta(150.0, $points['I'][0], 0.001);
        $this->assertEqualsWithDelta(150.0, $points['I'][1], 0.001);
    }

    public function test_chart_points_handle_all_zero_scores(): void
    {
        $points = RiaSecScorer::chartPoints(array_fill_keys(RiaSecScorer::ORDER, 0));

        foreach ($points as [$x, $y]) {
            $this->assertEqualsWithDelta(150.0, $x, 0.001);
            $this->assertEqualsWithDelta(150.0, $y, 0.001);
        }
    }
}
