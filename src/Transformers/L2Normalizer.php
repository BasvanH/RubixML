<?php

namespace Rubix\ML\Transformers;

use Rubix\ML\DataType;

use const Rubix\ML\EPSILON;

/**
 * L2 Normalizer
 *
 * Transform each sample vector in the sample matrix such that each feature is divided by
 * the L2 norm (or *magnitude*) of that vector. The resulting sample will have continuous
 * features between 0 and 1.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class L2Normalizer implements Transformer
{
    /**
     * Return the data types that this transformer is compatible with.
     *
     * @return int[]
     */
    public function compatibility() : array
    {
        return [
            DataType::CONTINUOUS,
        ];
    }

    /**
     * Transform the dataset in place.
     *
     * @param array $samples
     */
    public function transform(array &$samples) : void
    {
        foreach ($samples as &$sample) {
            $norm = 0.;

            foreach ($sample as &$value) {
                $norm += $value ** 2;
            }

            $norm = sqrt($norm ?: EPSILON);

            foreach ($sample as &$value) {
                $value /= $norm;
            }
        }
    }
}
