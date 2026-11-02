<?php

namespace Tests\Unit;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentSubmission;
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

    public function test_explicit_riasec_type_enables_riasec_profiles_without_title_matching(): void
    {
        $this->assertTrue(RiaSecScorer::isRiaSecAssessment('Interest check-in', null, 'riasec'));
        $this->assertFalse(RiaSecScorer::isRiaSecAssessment('Interest check-in', null, 'general'));
        $this->assertTrue(RiaSecScorer::isRiaSecAssessment('RIASEC Career Interest Inventory'));
    }

    public function test_choice_domain_matching_uses_labels_or_explicit_domain_codes(): void
    {
        $assessment = new Assessment();
        $question = new AssessmentQuestion();
        $question->setAttribute('id', 1);
        $assessment->setRelation('questions', collect([$question]));

        $submission = new AssessmentSubmission();
        $submission->setAttribute('answers', [1 => 'I enjoy working with people (S)']);

        $scores = RiaSecScorer::calculateForAssessment($assessment, $submission);

        $this->assertSame(1, $scores['S']);
        $this->assertSame(0, $scores['I']);
    }

    public function test_domain_tagged_activities_are_scored_as_average_interest_ratings(): void
    {
        $assessment = new Assessment();
        $realistic = new AssessmentQuestion();
        $realistic->setAttribute('id', 1);
        $realistic->setAttribute('riasec_domain', 'R');
        $social = new AssessmentQuestion();
        $social->setAttribute('id', 2);
        $social->setAttribute('riasec_domain', 'S');
        $assessment->setRelation('questions', collect([$realistic, $social]));

        $submission = new AssessmentSubmission();
        $submission->setAttribute('answers', [1 => '5', 2 => '3']);

        $scores = RiaSecScorer::calculateForAssessment($assessment, $submission);

        $this->assertSame(5.0, $scores['R']);
        $this->assertSame(3.0, $scores['S']);
        $this->assertSame(0, $scores['I']);
        $this->assertTrue(RiaSecScorer::isDomainTagged($assessment));
        $this->assertSame('Very interested', RiaSecScorer::ratingLabel('4'));
    }
}
