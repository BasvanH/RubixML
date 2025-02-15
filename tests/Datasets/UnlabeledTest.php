<?php

namespace Rubix\ML\Tests\Datasets;

use Rubix\ML\DataType;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Unlabeled;
use PHPUnit\Framework\TestCase;
use ArrayIterator;
use ArrayAccess;
use Countable;

use function Rubix\ML\array_transpose;

class UnlabeledTest extends TestCase
{
    protected const SAMPLES = [
        ['nice', 'furry', 'friendly', 4.0],
        ['mean', 'furry', 'loner', -1.5],
        ['nice', 'rough', 'friendly', 2.6],
        ['mean', 'rough', 'friendly', -1.0],
        ['nice', 'rough', 'friendly', 2.9],
        ['nice', 'furry', 'loner', -5.0],
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

    protected const RANDOM_SEED = 0;

    protected $dataset;

    public function setUp()
    {
        $this->dataset = new Unlabeled(self::SAMPLES, false);

        srand(self::RANDOM_SEED);
    }

    public function test_build_dataset()
    {
        $this->assertInstanceOf(Unlabeled::class, $this->dataset);
        $this->assertInstanceOf(Dataset::class, $this->dataset);
        $this->assertInstanceOf(Countable::class, $this->dataset);
        $this->assertInstanceOf(ArrayAccess::class, $this->dataset);
    }

    public function test_stack_datasets()
    {
        $dataset1 = new Unlabeled([['sample1']]);
        $dataset2 = new Unlabeled([['sample2']]);
        $dataset3 = new Unlabeled([['sample3']]);

        $dataset = Unlabeled::stack([$dataset1, $dataset2, $dataset3]);

        $this->assertInstanceOf(Unlabeled::class, $dataset);

        $this->assertEquals(3, $dataset->numRows());
        $this->assertEquals(1, $dataset->numColumns());
    }

    public function test_from_iterator()
    {
        $samples = new ArrayIterator(self::SAMPLES);

        $dataset = Unlabeled::fromIterator($samples);

        $this->assertInstanceOf(Unlabeled::class, $dataset);

        $this->assertEquals(self::SAMPLES, $dataset->samples());
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

    public function test_randomize()
    {
        $samples = $this->dataset->samples();

        $this->dataset->randomize();

        $this->assertNotEquals($samples, $this->dataset->samples());
    }

    public function test_filter_by_column()
    {
        $isFriendly = function ($value) {
            return $value === 'friendly';
        };

        $filtered = $this->dataset->filterByColumn(2, $isFriendly);

        $expected = [
            ['nice', 'furry', 'friendly', 4.0],
            ['nice', 'rough', 'friendly', 2.6],
            ['mean', 'rough', 'friendly', -1.0],
            ['nice', 'rough', 'friendly', 2.9],
        ];

        $this->assertEquals($expected, $filtered->samples());
    }

    public function test_sort_by_column()
    {
        $this->dataset->sortByColumn(2);

        $sorted = array_column(self::SAMPLES, 2);

        sort($sorted);

        $this->assertEquals($sorted, $this->dataset->column(2));
    }

    public function test_head()
    {
        $subset = $this->dataset->head(3);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(3, $subset);
    }

    public function test_tail()
    {
        $subset = $this->dataset->tail(3);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(3, $subset);
    }

    public function test_take()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->take(3);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(3, $subset);
        $this->assertCount(3, $this->dataset);
    }

    public function test_leave()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->leave(1);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(5, $subset);
        $this->assertCount(1, $this->dataset);
    }

    public function test_slice_dataset()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->slice(2, 2);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(2, $subset);
        $this->assertCount(6, $this->dataset);
    }

    public function test_splice_dataset()
    {
        $this->assertCount(6, $this->dataset);

        $subset = $this->dataset->splice(2, 2);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(2, $subset);
        $this->assertCount(4, $this->dataset);
    }

    public function test_split_dataset()
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
        [$left, $right] = $this->dataset->partition(2, 'loner');

        $this->assertInstanceOf(Unlabeled::class, $left);
        $this->assertInstanceOf(Unlabeled::class, $right);

        $this->assertCount(2, $left);
        $this->assertCount(4, $right);
    }

    public function test_random_subset()
    {
        $subset = $this->dataset->randomSubset(3);

        $this->assertCount(3, array_unique($subset->samples(), SORT_REGULAR));
    }

    public function test_random_subset_with_replacement()
    {
        $subset = $this->dataset->randomSubsetWithReplacement(3);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(3, $subset);
    }

    public function test_random_weighted_subset_with_replacement()
    {
        $subset = $this->dataset->randomWeightedSubsetWithReplacement(3, self::WEIGHTS);

        $this->assertInstanceOf(Unlabeled::class, $subset);
        $this->assertCount(3, $subset);
    }

    public function test_prepend_dataset()
    {
        $this->assertCount(count(self::SAMPLES), $this->dataset);

        $dataset = new Unlabeled([['nice', 'furry', 'friendly']]);

        $merged = $this->dataset->prepend($dataset);

        $this->assertCount(count(self::SAMPLES) + 1, $merged);

        $this->assertEquals(['nice', 'furry', 'friendly'], $merged->row(0));
    }

    public function test_append_dataset()
    {
        $this->assertCount(count(self::SAMPLES), $this->dataset);

        $dataset = new Unlabeled([['nice', 'furry', 'friendly']]);

        $merged = $this->dataset->append($dataset);

        $this->assertCount(count(self::SAMPLES) + 1, $merged);

        $this->assertEquals(['nice', 'furry', 'friendly'], $merged->row(6));
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
}
