<?php

namespace Rubix\ML\Datasets;

use Rubix\ML\DataType;
use Rubix\ML\Other\Helpers\Stats;
use Rubix\ML\Transformers\Stateful;
use Rubix\ML\Transformers\Transformer;
use Rubix\ML\Kernels\Distance\Distance;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;
use ArrayIterator;
use ArrayAccess;
use Countable;

use function Rubix\ML\array_transpose;

use const Rubix\ML\EPSILON;

/**
 * Dataset
 *
 * In Rubix ML, data are passed in specialized in-memory containers called Dataset
 * objects. Dataset objects are extended table-like data structures with an internal
 * type system and many operations for wrangling. They can hold a heterogeneous mix
 * of categorical and continuous data and they make it easy to transport data in a
 * canonical way.
 *
 * > **Note:** By convention, categorical data are given as string type whereas
 * continuous data are given as either integer or floating point numbers.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
abstract class Dataset implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * The rows of samples and columns of features that make up the
     * data table i.e. the fixed-length feature vectors.
     *
     * @var array[]
     */
    protected $samples;

    /**
     * Stack a number of datasets on top of each other to form a single
     * dataset.
     *
     * @param array $datasets
     * @return self
     */
    abstract public static function stack(array $datasets);

    /**
     * @param array $samples
     * @param bool $validate
     * @throws \InvalidArgumentException
     */
    public function __construct(array $samples = [], bool $validate = true)
    {
        if ($validate and $samples) {
            $samples = array_values($samples);

            $proto = $samples ? array_values($samples[0]) : [];

            $n = is_array($proto) ? count($proto) : 1;

            $types = array_map([DataType::class, 'determine'], $proto);

            foreach ($samples as &$sample) {
                $sample = is_array($sample)
                    ? array_values($sample)
                    : [$sample];

                if (count($sample) !== $n) {
                    throw new InvalidArgumentException('The number of feature'
                        . " columns must be equal for all samples, $n expected "
                        . count($sample) . ' given.');
                }

                foreach ($sample as $column => $value) {
                    if (DataType::determine($value) !== $types[$column]) {
                        throw new InvalidArgumentException('Columns must contain'
                            . ' feature values of the same data type.');
                    }
                }
            }
        }

        $this->samples = $samples;
    }

    /**
     * Return the sample matrix.
     *
     * @return array[]
     */
    public function samples() : array
    {
        return $this->samples;
    }

    /**
     * Return the sample at the given row index.
     *
     * @param int $row
     * @return array
     */
    public function row(int $row) : array
    {
        return $this->offsetGet($row);
    }

    /**
     * Return the number of rows in the datasets.
     *
     * @return int
     */
    public function numRows() : int
    {
        return count($this->samples);
    }

    /**
     * Return the feature column at the given index.
     *
     * @param int $column
     * @return array
     */
    public function column(int $column) : array
    {
        return array_column($this->samples, $column);
    }

    /**
     * Return the number of feature columns in the dataset.
     *
     * @return int
     */
    public function numColumns() : int
    {
        return isset($this->samples[0]) ? count($this->samples[0]) : 0;
    }

    /**
     * Return an array of feature column data types autodectected using the first
     * sample in the dataset.
     *
     * @return int[]
     */
    public function types() : array
    {
        return array_map([DataType::class, 'determine'], $this->samples[0] ?? []);
    }

    /**
     * Return the unique data types.
     *
     * @return int[]
     */
    public function uniqueTypes() : array
    {
        return array_unique($this->types());
    }

    /**
     * Does the dataset consist of data of a single type?
     *
     * @return bool
     */
    public function homogeneous() : bool
    {
        return count($this->uniqueTypes()) === 1;
    }

    /**
     * Get the datatype for a feature column given a column index.
     *
     * @param int $column
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return int
     */
    public function columnType(int $column) : int
    {
        if (empty($this->samples)) {
            throw new RuntimeException('Cannot determine data type'
                . ' of an empty dataset.');
        }

        if (!isset($this->samples[0][$column])) {
            throw new InvalidArgumentException("Column $column does"
                . ' not exist.');
        }

        return DataType::determine($this->samples[0][$column]);
    }

    /**
     * Return a tuple containing the shape of the dataset i.e the number of
     * rows and columns.
     *
     * @var int[]
     */
    public function shape() : array
    {
        return [$this->numRows(), $this->numColumns()];
    }

    /**
     * Return the number of elements in the dataset.
     *
     * @return int
     */
    public function size() : int
    {
        return $this->numRows() * $this->numColumns();
    }

    /**
     * Rotate the dataset and return it in an array. i.e. rows become
     * columns and columns become rows.
     *
     * @return array
     */
    public function columns() : array
    {
        return array_transpose($this->samples);
    }

    /**
     * Return the columns that match a given data type.
     *
     * @param int $type
     * @return array
     */
    public function columnsByType(int $type) : array
    {
        $n = $this->numColumns();

        $columns = [];

        for ($i = 0; $i < $n; $i++) {
            if ($this->columnType($i) === $type) {
                $columns[$i] = $this->column($i);
            }
        }

        return $columns;
    }

    /**
     * Transform a feature column with a callback function.
     *
     * @param int $column
     * @param callable $callback
     * @throws \InvalidArgumentException
     * @return self
     */
    public function transformColumn(int $column, callable $callback) : self
    {
        if ($column < 0 or $column > $this->numColumns()) {
            throw new InvalidArgumentException('Column number must'
                . " be between 0 and {$this->numColumns()}, $column"
                . ' given.');
        }

        foreach ($this->samples as &$sample) {
            $sample[$column] = $callback($sample[$column]);
        }

        return $this;
    }

    /**
     * Apply a transformation to the dataset.
     *
     * @param \Rubix\ML\Transformers\Transformer $transformer
     * @return self
     */
    public function apply(Transformer $transformer) : self
    {
        if ($transformer instanceof Stateful) {
            if (!$transformer->fitted()) {
                $transformer->fit($this);
            }
        }

        $transformer->transform($this->samples);

        return $this;
    }

    /**
     * Return an array of statistics such as the central tendency, dispersion
     * and shape of each continuous feature column and the joint probabilities
     * of every categorical feature column.
     *
     * @return array
     */
    public function describe() : array
    {
        $stats = [];

        foreach ($this->columns() as $column => $values) {
            $type = $this->columnType($column);

            $desc = [];

            $desc['column'] = $column;
            $desc['type'] = DataType::TYPES[$type];

            switch ($type) {
                case DataType::CONTINUOUS:
                    [$mean, $variance] = Stats::meanVar($values);

                    $desc['mean'] = $mean;
                    $desc['variance'] = $variance;
                    $desc['std_dev'] = sqrt($variance ?: EPSILON);

                    $desc['skewness'] = Stats::skewness($values, $mean);
                    $desc['kurtosis'] = Stats::kurtosis($values, $mean);

                    $percentiles = Stats::percentiles($values, [
                        0, 25, 50, 75, 100,
                    ]);

                    $desc['min'] = $percentiles[0];
                    $desc['25%'] = $percentiles[1];
                    $desc['median'] = $percentiles[2];
                    $desc['75%'] = $percentiles[3];
                    $desc['max'] = $percentiles[4];

                    break 1;

                case DataType::CATEGORICAL:
                    $counts = array_count_values($values);

                    $total = count($values) ?: EPSILON;

                    $probabilities = [];

                    foreach ($counts as $category => $count) {
                        $probabilities[$category] = $count / $total;
                    }

                    $desc['num_categories'] = count($counts);
                    $desc['probabilities'] = $probabilities;

                    break 1;

                case DataType::RESOURCE:
                    $desc['php_type'] = get_resource_type(reset($values));

                    break 1;
            }

            $stats[] = $desc;
        }

        return $stats;
    }

    /**
     * Is the dataset empty?
     *
     * @return bool
     */
    public function empty() : bool
    {
        return empty($this->samples);
    }

    /**
     * Return a dataset containing only the first n samples.
     *
     * @param int $n
     * @return self
     */
    abstract public function head(int $n = 10);

    /**
     * Return a dataset containing only the last n samples.
     *
     * @param int $n
     * @return self
     */
    abstract public function tail(int $n = 10);

    /**
     * Take n samples from the dataset and return them in a new dataset.
     *
     * @param int $n
     * @return self
     */
    abstract public function take(int $n = 1);

    /**
     * Leave n samples on the dataset and return the rest in a new dataset.
     *
     * @param int $n
     * @return self
     */
    abstract public function leave(int $n = 1);

    /**
     * Return an n size portion of the dataset in a new dataset.
     *
     * @param int $offset
     * @param int $n
     * @return self
     */
    abstract public function slice(int $offset, int $n);

    /**
     * Remove a size n chunk of the dataset starting at offset and return it in
     * a new dataset.
     *
     * @param int $offset
     * @param int $n
     * @return self
     */
    abstract public function splice(int $offset, int $n);

    /**
     * Prepend this dataset with another dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @return \Rubix\ML\Datasets\Dataset
     */
    abstract public function prepend(Dataset $dataset);

    /**
     * Append this dataset with another dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @return \Rubix\ML\Datasets\Dataset
     */
    abstract public function append(Dataset $dataset);

    /**
     * Randomize the dataset.
     *
     * @return self
     */
    abstract public function randomize();

    /**
     * Run a filter over the dataset using the values of a given column.
     *
     * @param int $index
     * @param callable $fn
     * @return self
     */
    abstract public function filterByColumn(int $index, callable $fn);

    /**
     * Sort the dataset by a column in the sample matrix.
     *
     * @param int $index
     * @param bool $descending
     * @return self
     */
    abstract public function sortByColumn(int $index, bool $descending = false);

    /**
     * Split the dataset into two subsets with a given ratio of samples.
     *
     * @param float $ratio
     * @return array
     */
    abstract public function split(float $ratio = 0.5) : array;

    /**
     * Fold the dataset k - 1 times to form k equal size datasets.
     *
     * @param int $k
     * @return array
     */
    abstract public function fold(int $k = 10) : array;

    /**
     * Generate a collection of batches of size n from the dataset. If there are
     * not enough samples to fill an entire batch, then the dataset will contain
     * as many samples as possible.
     *
     * @param int $n
     * @return array
     */
    abstract public function batch(int $n = 50) : array;

    /**
     * Partition the dataset into left and right subsets by a specified feature
     * column.
     *
     * @param int $index
     * @param mixed $value
     * @return array
     */
    abstract public function partition(int $index, $value) : array;

    /**
     * Partition the dataset into left and right subsets based on their distance
     * between two centroids.
     *
     * @param array $leftCentroid
     * @param array $rightCentroid
     * @param \Rubix\ML\Kernels\Distance\Distance $kernel
     * @return array
     */
    abstract public function spatialPartition(array $leftCentroid, array $rightCentroid, Distance $kernel);

    /**
     * Generate a random subset without replacement.
     *
     * @param int $n
     * @return self
     */
    abstract public function randomSubset(int $n);

    /**
     * Generate a random subset of n samples with replacement.
     *
     * @param int $n
     * @return self
     */
    abstract public function randomSubsetWithReplacement(int $n);

    /**
     * Generate a random weighted subset with replacement.
     *
     * @param int $n
     * @param array $weights
     * @return self
     */
    abstract public function randomWeightedSubsetWithReplacement(int $n, array $weights);

    /**
     * Return a dataset with all duplicate rows removed.
     *
     * @return self
     */
    abstract public function deduplicate();

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->numRows();
    }

    /**
     * @param mixed $index
     * @param array $values
     * @throws \RuntimeException
     */
    public function offsetSet($index, $values) : void
    {
        throw new RuntimeException('Datasets cannot be mutated directly.');
    }

    /**
     * Does a given row exist in the dataset.
     *
     * @param mixed $index
     * @return bool
     */
    public function offsetExists($index) : bool
    {
        return isset($this->samples[$index]);
    }

    /**
     * @param mixed $index
     * @throws \RuntimeException
     */
    public function offsetUnset($index) : void
    {
        throw new RuntimeException('Datasets cannot be mutated directly.');
    }

    /**
     * Return a column from the dataset given by index.
     *
     * @param mixed $index
     * @throws \InvalidArgumentException
     * @return array
     */
    public function offsetGet($index) : array
    {
        return $this->samples[$index];
    }

    /**
     * Get an iterator for the samples in the dataset.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->samples);
    }
}
