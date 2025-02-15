<?php

namespace Rubix\ML\Transformers;

use Rubix\ML\DataType;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Other\Specifications\DatasetIsCompatibleWithTransformer;
use InvalidArgumentException;
use RuntimeException;

use const Rubix\ML\EPSILON;

/**
 * Min Max Normalizer
 *
 * The *Min Max* Normalizer scales the input features to a value between
 * a user-specified range (default 0 to 1).
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class MinMaxNormalizer implements Transformer, Stateful, Elastic
{
    /**
     * The minimum value of the transformed features.
     *
     * @var float
     */
    protected $min;

    /**
     * The maximum value of the transformed features.
     *
     * @var float
     */
    protected $max;

    /**
     * The computed minimums of the fitted data.
     *
     * @var array|null
     */
    protected $minimums;

    /**
     * The computed maximums of the fitted data.
     *
     * @var array|null
     */
    protected $maximums;

    /**
     * The scale of each feature column.
     *
     * @var array|null
     */
    protected $scales;

    /**
     * The scaled minimums of each feature column.
     *
     * @var array|null
     */
    protected $mins;

    /**
     * @param float $min
     * @param float $max
     * @throws \InvalidArgumentException
     */
    public function __construct(float $min = 0., float $max = 1.)
    {
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum cannot be greater'
                . ' than maximum.');
        }

        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Return the data types that this transformer is compatible with.
     *
     * @return int[]
     */
    public function compatibility() : array
    {
        return DataType::ALL;
    }

    /**
     * Is the transformer fitted?
     *
     * @return bool
     */
    public function fitted() : bool
    {
        return $this->mins and $this->scales;
    }

    /**
     * Return the minmums of each feature column.
     *
     * @return array|null
     */
    public function minimums() : ?array
    {
        return $this->minimums;
    }

    /**
     * Return the maximums of each feature column.
     *
     * @return array|null
     */
    public function maximums() : ?array
    {
        return $this->maximums;
    }

    /**
     * Fit the transformer to the dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     */
    public function fit(Dataset $dataset) : void
    {
        DatasetIsCompatibleWithTransformer::check($dataset, $this);
        
        $this->minimums = $this->maximums = $this->scales = $this->mins = [];

        foreach ($dataset->types() as $column => $type) {
            if ($type === DataType::CONTINUOUS) {
                $this->minimums[$column] = INF;
                $this->maximums[$column] = -INF;
            }
        }

        $this->update($dataset);
    }

    /**
     * Update the fitting of the transformer.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     */
    public function update(Dataset $dataset) : void
    {
        if ($this->minimums === null or $this->maximums === null) {
            $this->fit($dataset);
            
            return;
        }

        DatasetIsCompatibleWithTransformer::check($dataset, $this);

        $n = $dataset->numColumns();

        for ($column = 0; $column < $n; $column++) {
            if ($dataset->columnType($column) === DataType::CONTINUOUS) {
                $values = $dataset->column($column);
                
                $min = min($values);
                $max = max($values);

                $min = min($min, $this->minimums[$column]);
                $max = max($max, $this->maximums[$column]);

                $scale = ($this->max - $this->min)
                    / (($max - $min) ?: EPSILON);

                $minHat = $this->min - $min * $scale;

                $this->minimums[$column] = $min;
                $this->maximums[$column] = $max;
                $this->scales[$column] = $scale;
                $this->mins[$column] = $minHat;
            }
        }
    }

    /**
     * Transform the dataset in place.
     *
     * @param array $samples
     * @throws \RuntimeException
     */
    public function transform(array &$samples) : void
    {
        if ($this->mins === null or $this->scales === null) {
            throw new RuntimeException('Transformer has not been fitted.');
        }

        foreach ($samples as &$sample) {
            foreach ($this->scales as $column => $scale) {
                $sample[$column] *= $scale;
                $sample[$column] += $this->mins[$column];
            }
        }
    }
}
