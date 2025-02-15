<?php

namespace Rubix\ML\Tests\CrossValidation\Reports;

use Rubix\ML\CrossValidation\Reports\Report;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use PHPUnit\Framework\TestCase;
use Generator;

class MulticlassBreakdownTest extends TestCase
{
    protected $report;

    public function setUp()
    {
        $this->report = new MulticlassBreakdown();
    }

    public function test_build_report()
    {
        $this->assertInstanceOf(MulticlassBreakdown::class, $this->report);
        $this->assertInstanceOf(Report::class, $this->report);
    }

    /**
     * @dataProvider generate_report_provider
     */
    public function test_generate_report(array $predictions, array $labels, array $expected)
    {
        $result = $this->report->generate($predictions, $labels);
        
        $this->assertEquals($expected, $result);
    }

    public function generate_report_provider() : Generator
    {
        yield [
            ['wolf', 'lamb', 'wolf', 'lamb', 'wolf'],
            ['lamb', 'lamb', 'wolf', 'wolf', 'wolf'],
            [
                'overall' => [
                    'accuracy' => 0.6,
                    'precision' => 0.5833333333333333,
                    'recall' => 0.5833333333333333,
                    'specificity' => 0.5833333333333333,
                    'negative_predictive_value' => 0.5833333333333333,
                    'false_discovery_rate' => 0.4166666666666667,
                    'miss_rate' => 0.4166666666666667,
                    'fall_out' => 0.4166666666666667,
                    'false_omission_rate' => 0.4166666666666667,
                    'f1_score' => 0.5833333333333333,
                    'mcc' => 0.16666666666666666,
                    'informedness' => 0.16666666666666652,
                    'markedness' => 0.16666666666666652,
                    'true_positives' => 3,
                    'true_negatives' => 3,
                    'false_positives' => 2,
                    'false_negatives' => 2,
                    'cardinality' => 5,
                ],
                'classes' => [
                    'wolf' => [
                        'accuracy' => 0.6,
                        'precision' => 0.6666666666666666,
                        'recall' => 0.6666666666666666,
                        'specificity' => 0.5,
                        'negative_predictive_value' => 0.5,
                        'false_discovery_rate' => 0.33333333333333337,
                        'miss_rate' => 0.33333333333333337,
                        'fall_out' => 0.5,
                        'false_omission_rate' => 0.5,
                        'f1_score' => 0.6666666666666666,
                        'mcc' => 0.16666666666666666,
                        'informedness' => 0.16666666666666652,
                        'markedness' => 0.16666666666666652,
                        'cardinality' => 3,
                        'density' => 0.6,
                        'true_positives' => 2,
                        'true_negatives' => 1,
                        'false_positives' => 1,
                        'false_negatives' => 1,
                    ],
                    'lamb' => [
                        'accuracy' => 0.6,
                        'precision' => 0.5,
                        'recall' => 0.5,
                        'specificity' => 0.6666666666666666,
                        'negative_predictive_value' => 0.6666666666666666,
                        'false_discovery_rate' => 0.5,
                        'miss_rate' => 0.5,
                        'fall_out' => 0.33333333333333337,
                        'false_omission_rate' => 0.33333333333333337,
                        'f1_score' => 0.5,
                        'mcc' => 0.16666666666666666,
                        'informedness' => 0.16666666666666652,
                        'markedness' => 0.16666666666666652,
                        'cardinality' => 2,
                        'density' => 0.4,
                        'true_positives' => 1,
                        'true_negatives' => 2,
                        'false_positives' => 1,
                        'false_negatives' => 1,
                    ],
                ],
            ],
        ];
    }
}
