<?php

namespace Rubix\ML\Tests\Datasets;

use Rubix\ML\DataType;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Labeled;
use PHPUnit\Framework\TestCase;
use ArrayIterator;
use ArrayAccess;
use Countable;

use function Rubix\ML\array_transpose;

class LabeledTest extends TestCase
{
    protected const SAMPLES = [
        ['nice', 'furry', 'friendly', 4.0],
        ['mean', 'furry', 'loner', -1.5],
        ['nice', 'rough', 'friendly', 2.6],
        ['mean', 'rough', 'friendly', -1.0],
        ['nice', 'rough', 'friendly', 2.9],
        ['nice', 'furry', 'loner', -5.0],
    ];

    protected const LABELS = [
        'not monster', 'monster', 'not monster',
        'monster', 'not monster', 'not monster',
    ];

    protected const TYPES = [
        DataType::CATEGORICAL,
        DataType::CATEGORICAL,
        DataType::CATEGORICAL,
        DataType::CONTINUOUS,
    ];

    protected const WEIGHTS = [
        1, 1, 2, 1, 2, 3,
    ];

    protected const RANDOM_SEED = 1;

    protected $dataset;

    public function setUp()
    {
        $this->dataset = new Labeled(self::SAMPLES, self::LABELS, false);

        srand(self::RANDOM_SEED);
    }

    public function test_build_dataset()
    {
        $this->assertInstanceOf(Labeled::class, $this->dataset);
        $this->assertInstanceOf(Dataset::class, $this->dataset);
        $this->assertInstanceOf(Countable::class, $this->dataset);
        $this->assertInstanceOf(ArrayAccess::class, $this->dataset);
    }

    public function test_stack_datasets()
    {
        $dataset1 = new Labeled([['sample1']], ['label1']);
        $dataset2 = new Labeled([['sample2']], ['label2']);
        $dataset3 = new Labeled([['sample3']], ['label3']);

        $dataset = Labeled::stack([$dataset1, $dataset2, $dataset3]);

        $this->assertInstanceOf(Labeled::class, $dataset);

        $this->assertEquals(3, $dataset->numRows());
        $this->assertEquals(1, $dataset->numColumns());
    }

    public function test_from_iterator()
    {
        $samples = new ArrayIterator(self::SAMPLES);
        $labels = new ArrayIterator(self::LABELS);

        $dataset = Labeled::fromIterator($samples, $labels);

        $this->assertInstanceOf(Labeled::class, $dataset);

        $this->assertEquals(self::SAMPLES, $dataset->samples());
        $this->assertEquals(self::LABELS, $dataset->labels());
    }

    public function test_unzip()
    {
        $table = iterator_to_array($this->dataset->zip());

        $dataset = Labeled::unzip($table);

        $this->assertInstanceOf(Labeled::class, $dataset);

        $this->assertEquals(self::SAMPLES, $dataset->samples());
        $this->assertEquals(self::LABELS, $dataset->labels());
    }

    public function test_get_samples()
    {
        $this->assertEquals(self::SAMPLES, $this->dataset->samples());
    }

    public function test_get_row()
    {
        $this->assertEquals(self::SAMPLES[2], $this->dataset->row(2));
        $this->assertEquals(self::SAMPLES[5], $this->dataset->row(5));
    }

    public function test_num_rows()
    {
        $this->assertEquals(6, $this->dataset->numRows());
    }

    public function test_get_column()
    {
        $expected = array_column(self::SAMPLES, 2);

        $this->assertEquals($expected, $this->dataset->column(2));
    }

    public function test_get_num_columns()
    {
        $this->assertEquals(4, $this->dataset->numColumns());
    }

    public function test_column_types()
    {
        $this->assertEquals(self::TYPES, $this->dataset->types());
    }

    public function test_unique_types()
    {
        $this->assertCount(2, $this->dataset->uniqueTypes());
    }

    public function test_homogeneous()
    {
        $this->assertFalse($this->dataset->homogeneous());
    }

    public function test_shape()
    {
        $this->assertEquals([6, 4], $this->dataset->shape());
    }

    public function test_size()
    {
        $this->assertEquals(24, $this->dataset->size());
    }

    public function test_column_type()
    {
        $this->assertEquals(self::TYPES[0], $this->dataset->columnType(0));
        $this->assertEquals(self::TYPES[1], $this->dataset->columnType(1));
        $this->assertEquals(self::TYPES[2], $this->dataset->columnType(2));
    }

    public function test_columns()
    {
        $expected = array_transpose(self::SAMPLES);

        $this->assertEquals($expected, $this->dataset->columns());
    }

    public function test_columns_by_type()
    {
        $expected = array_slice(array_transpose(self::SAMPLES), 0, 3);

        $columns = $this->dataset->columnsByType(DataType::CATEGORICAL);

        $this->assertEquals($expected, $columns);
    }

    public function test_empty()
    {
        $this->assertFalse($this->dataset->empty());
    }

    public function test_count()
    {
        $this->assertEquals(6, $this->dataset->count());
    }

    public function test_get_labels()
    {
        $this->assertEquals(self::LABELS, $this->dataset->labels());
    }

    public function test_zip()
    {
        $outcome = [
            ['nice', 'furry', 'friendly', 4.0, 'not monster'],
            ['mean', 'furry', 'loner', -1.5, 'monster'],
            ['nice', 'rough', 'friendly', 2.6, 'not monster'],
            ['mean', 'rough', 'friendly', -1.0, 'monster'],
            ['nice', 'rough', 'friendly', 2.9, 'not monster'],
            ['nice', 'furry', 'loner', -5.0, 'not monster'],
        ];
        
        $this->assertEquals($outcome, iterator_to_array($this->dataset->zip()));
    }

    public function test_transform_labels()
    {
        $transformer = function ($label) {
            return $label === 'not monster' ? 0 : 1;
        };

        $this->dataset->transformLabels($transformer);

        $expected = [
            0, 1, 0, 1, 0, 0,
        ];

        $this->assertEquals($expected, $this->dataset->labels());
    }

    public function test_get_label()
    {
        $this->assertEquals('not monster', $this->dataset->label(0));
        $this->assertEquals('monster', $this->dataset->label(1));
    }

    public function test_label_type()
    {
        $this->assertEquals(DataType::CATEGORICAL, $this->dataset->labelType());
    }

    public function test_possible_outcomes()
    {
        $this->assertEquals(
            ['not monster', 'monster'],
            $this->dataset->possibleOutcomes()
        );
    }

    public function test_randomize()
    {
        $samples = $this->dataset->samples();
        $labels = $this->dataset->labels();

        $this->dataset->randomize();

        $this->assertNotEquals($samples, $this->dataset->samples());
        $this->assertNotEquals($labels, $this->dataset->labels());
    }

    public function test_filter_by_column()
    {
        $isFriendly = function ($value) {
            return $value === 'friendly';
        };

        $filtered = $this->dataset->filterByColumn(2, $isFriendly);

        $samples = [
            ['nice', 'furry', 'friendly', 4.0],
            ['nice', 'rough', 'friendly', 2.6],
            ['mean', 'rough', 'friendly', -1.0],
            ['nice', 'rough', 'friendly', 2.9],
        ];

        $labels = ['not monster', 'not monster', 'monster', 'not monster'];

        $this->assertEquals($samples, $filtered->samples());
        $this->assertEquals($labels, $filtered->labels());
    }

    public function test_filter_by_label()
    {
        $notMonster = function ($label) {
            return $label === 'not monster';
        };

        $filtered = $this->dataset->filterByLabel($notMonster);

        $samples = [
            ['nice', 'furry', 'friendly', 4.0],
            ['nice', 'rough', 'friendly', 2.6],
            ['nice', 'rough', 'friendly', 2.9],
            ['nice', 'furry', 'loner', -5.0],
        ];

        $labels = ['not monster', 'not monster', 'not monster', 'not monster'];

        $this->assertEquals($samples, $filtered->samples());
        $this->assertEquals($labels, $filtered->labels());
    }

    public function test_sort_by_column()
    {
        $this->dataset->sortByColumn(1);

        $sorted = array_column(self::SAMPLES, 1);

        $labels = self::LABELS;

        array_multisort($sorted, $labels, SORT_ASC);

        $this->assertEquals($sorted, $this->dataset->column(1));
        $this->assertEquals($labels, $this->dataset->labels());
    }

    public function test_sort_by_label()
    {
        $this->dataset->sortByLabel();

        $samples = self::SAMPLES;
        $labels = self::LABELS;

        array_multisort($labels, $samples, SORT_ASC);

        $this->assertEquals($samples, $this->dataset->samples());
        $this->assertEquals($labels, $this->dataset->labels());
    }

    public function test_head()
    {
        $subset = $this->dataset->head(3);

        $this->assertInstanceOf(Labeled::class, $subset);
        $this->assertCount(3, $subset);
    }

    public function test_tail()
    {
        $subset = $this->dataset->tail(3);

        $this->assertInstanceOf(Labeled::class, $subset);
        $this->assertCount(3, $subset);
    }

    public function test_take()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->take(3);

        $this->assertCount(3, $subset);
        $this->assertCount(3, $this->dataset);
    }

    public function test_leave()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->leave(1);

        $this->assertCount(5, $subset);
        $this->assertCount(1, $this->dataset);
    }

    public function test_slice_dataset()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->slice(2, 2);

        $this->assertInstanceOf(Labeled::class, $subset);
        $this->assertCount(2, $subset);
        $this->assertCount(6, $this->dataset);
    }

    public function test_splice_dataset()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->splice(2, 2);

        $this->assertInstanceOf(Labeled::class, $subset);
        $this->assertCount(2, $subset);
        $this->assertCount(4, $this->dataset);
    }

    public function test_split_dataset()
    {
        [$left, $right] = $this->dataset->split(0.5);

        $this->assertCount(3, $left);
        $this->assertCount(3, $right);
    }

    public function test_stratified_split()
    {
        [$left, $right] = $this->dataset->split(0.5);

        $this->assertCount(3, $left);
        $this->assertCount(3, $right);
    }

    public function test_fold_dataset()
    {
        $folds = $this->dataset->fold(2);

        $this->assertCount(2, $folds);
        $this->assertCount(3, $folds[0]);
        $this->assertCount(3, $folds[1]);
    }

    public function test_stratified_fold()
    {
        $folds = $this->dataset->stratifiedFold(2);

        $this->assertCount(2, $folds);
        $this->assertCount(3, $folds[0]);
        $this->assertCount(3, $folds[1]);
    }

    public function test_stratify()
    {
        $strata = $this->dataset->stratify();

        $this->assertCount(2, $strata['monster']);
        $this->assertCount(4, $strata['not monster']);
    }

    public function test_batch_dataset()
    {
        $batches = $this->dataset->batch(2);

        $this->assertCount(3, $batches);
        $this->assertCount(2, $batches[0]);
        $this->assertCount(2, $batches[1]);
        $this->assertCount(2, $batches[2]);
    }

    public function test_partition()
    {
        [$left, $right] = $this->dataset->partition(1, 'rough');

        $this->assertInstanceOf(Labeled::class, $left);
        $this->assertInstanceOf(Labeled::class, $right);

        $this->assertCount(3, $left);
        $this->assertCount(3, $right);
    }

    public function test_random_subset()
    {
        $subset = $this->dataset->randomSubset(3);

        $this->assertCount(3, array_unique($subset->samples(), SORT_REGULAR));
    }

    public function test_random_subset_with_replacement()
    {
        $subset = $this->dataset->randomSubsetWithReplacement(3);

        $this->assertCount(3, $subset);
    }

    public function test_random_weighted_subset_with_replacement()
    {
        $subset = $this->dataset->randomWeightedSubsetWithReplacement(3, self::WEIGHTS);

        $this->assertCount(3, $subset);
    }

    public function test_prepend_dataset()
    {
        $this->assertCount(count(self::SAMPLES), $this->dataset);

        $dataset = new Labeled([['nice', 'furry', 'friendly']], ['not monster']);

        $merged = $this->dataset->prepend($dataset);

        $this->assertCount(count(self::SAMPLES) + 1, $merged);

        $this->assertEquals(['nice', 'furry', 'friendly'], $merged->row(0));
        $this->assertEquals('not monster', $merged->label(6));
    }

    public function test_append_dataset()
    {
        $this->assertCount(count(self::SAMPLES), $this->dataset);

        $dataset = new Labeled([['nice', 'furry', 'friendly']], ['not monster']);

        $merged = $this->dataset->append($dataset);

        $this->assertCount(count(self::SAMPLES) + 1, $merged);

        $this->assertEquals(['nice', 'furry', 'friendly'], $merged->row(6));
        $this->assertEquals('not monster', $merged->label(6));
    }

    public function test_describe_dataset()
    {
        $stats = $this->dataset->describe();

        $expected = [
            [
                'column' => 0,
                'type' => 'categorical',
                'num_categories' => 2,
                'probabilities' => [
                    'nice' => 0.6666666666666666,
                    'mean' => 0.3333333333333333,
                ],
            ],
            [
                'column' => 1,
                'type' => 'categorical',
                'num_categories' => 2,
                'probabilities' => [
                    'furry' => 0.5,
                    'rough' => 0.5,
                ],
            ],
            [
                'column' => 2,
                'type' => 'categorical',
                'num_categories' => 2,
                'probabilities' => [
                    'friendly' => 0.6666666666666666,
                    'loner' => 0.3333333333333333,
                ],
            ],
            [
                'column' => 3,
                'type' => 'continuous',
                'mean' => 0.3333333333333333,
                'variance' => 9.792222222222222,
                'std_dev' => 3.129252661934191,
                'skewness' => -0.4481030843690633,
                'kurtosis' => -1.1330702741786107,
                'min' => -5.0,
                '25%' => -1.375,
                'median' => 0.8,
                '75%' => 2.825,
                'max' => 4.0,
            ],
        ];

        $this->assertEquals($expected, $stats);
    }

    public function test_deduplicate()
    {
        $dataset = $this->dataset->deduplicate();

        $this->assertCount(6, $dataset);
    }

    public function test_transform_column()
    {
        $dataset = $this->dataset->transformColumn(3, 'abs');

        $expected = [4.0, 1.5, 2.6, 1.0, 2.9, 5.0];

        $this->assertEquals($expected, $dataset->column(3));
    }

    public function test_describe_labels()
    {
        $desc = $this->dataset->describeLabels();

        $expected = [
            'type' => 'categorical',
            'num_categories' => 2,
            'probabilities' => [
                'monster' => 0.3333333333333333,
                'not monster' => 0.6666666666666666,
            ],
        ];

        $this->assertEquals($expected, $desc);
    }
}
