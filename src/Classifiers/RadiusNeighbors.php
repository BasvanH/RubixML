<?php

namespace Rubix\ML\Classifiers;

use Rubix\ML\Learner;
use Rubix\ML\DataType;
use Rubix\ML\Estimator;
use Rubix\ML\Persistable;
use Rubix\ML\Probabilistic;
use Rubix\ML\Datasets\Dataset;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Graph\Trees\Spatial;
use Rubix\ML\Graph\Trees\BallTree;
use Rubix\ML\Other\Traits\ProbaSingle;
use Rubix\ML\Kernels\Distance\Distance;
use Rubix\ML\Other\Traits\PredictsSingle;
use Rubix\ML\Other\Specifications\DatasetIsCompatibleWithEstimator;
use InvalidArgumentException;
use RuntimeException;

use function Rubix\ML\argmax;

use const Rubix\ML\EPSILON;

/**
 * Radius Neighbors
 *
 * Radius Neighbors is a spatial tree-based classifier that takes the weighted vote
 * of each neighbor within a fixed user-defined radius measured by a kernelized
 * distance function. Since the radius of the search can be constrained, Radius
 * Neighbors is more robust to outliers than K Nearest Neighbors.In addition, Radius
 * Neighbors acts as a quasi anomaly detector by flagging samples that have 0
 * neighbors within radius.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class RadiusNeighbors implements Estimator, Learner, Probabilistic, Persistable
{
    use PredictsSingle, ProbaSingle;

    /**
     * The radius within which points are considered neighboors.
     *
     * @var float
     */
    protected $radius;

    /**
     * Should we use the inverse distances as confidence scores when making
     * predictions?
     *
     * @var bool
     */
    protected $weighted;

    /**
     * The spatial tree used to run range searches.
     *
     * @var \Rubix\ML\Graph\Trees\Spatial
     */
    protected $tree;

    /**
     * The class label for any samples that have no neighbors within the
     * specified radius.
     *
     * @var string
     */
    protected $anomalyClass;

    /**
     * The unique class outcomes.
     *
     * @var array
     */
    protected $classes = [
        //
    ];

    /**
     * @param float $radius
     * @param bool $weighted
     * @param \Rubix\ML\Graph\Trees\Spatial|null $tree
     * @throws \InvalidArgumentException
     */
    public function __construct(
        float $radius = 1.0,
        bool $weighted = true,
        ?Spatial $tree = null,
        string $anomalyClass = 'outlier'
    ) {
        if ($radius <= 0.) {
            throw new InvalidArgumentException('Radius must be'
                . " greater than 0, $radius given.");
        }

        $this->radius = $radius;
        $this->weighted = $weighted;
        $this->tree = $tree ?? new BallTree();
        $this->anomalyClass = trim($anomalyClass);
    }

    /**
     * Return the integer encoded estimator type.
     *
     * @return int
     */
    public function type() : int
    {
        return self::CLASSIFIER;
    }

    /**
     * Return the data types that this estimator is compatible with.
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
     * Has the learner been trained?
     *
     * @return bool
     */
    public function trained() : bool
    {
        return !$this->tree->bare();
    }

    /**
     * Return the base spatial tree instance.
     *
     * @return \Rubix\ML\Graph\Trees\Spatial
     */
    public function tree() : Spatial
    {
        return $this->tree;
    }

    /**
     * Train the learner with a dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @throws \InvalidArgumentException
     */
    public function train(Dataset $dataset) : void
    {
        if (!$dataset instanceof Labeled) {
            throw new InvalidArgumentException('Learner requires a'
                . ' labeled training set.');
        }

        DatasetIsCompatibleWithEstimator::check($dataset, $this);

        $this->classes = $dataset->possibleOutcomes();
        $this->classes[] = $this->anomalyClass;

        $this->tree->grow($dataset);
    }

    /**
     * Make predictions from a dataset.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return array
     */
    public function predict(Dataset $dataset) : array
    {
        if ($this->tree->bare()) {
            throw new RuntimeException('The estimator has not been trained.');
        }

        DatasetIsCompatibleWithEstimator::check($dataset, $this);

        $predictions = [];

        foreach ($dataset as $sample) {
            [$samples, $labels, $distances] = $this->tree->range($sample, $this->radius);

            if (empty($labels)) {
                $predictions[] = $this->anomalyClass;

                continue 1;
            }

            if ($this->weighted) {
                $weights = array_fill_keys($labels, 0.);

                foreach ($labels as $i => $label) {
                    $weights[$label] += 1. / (1. + $distances[$i]);
                }
            } else {
                $weights = array_count_values($labels);
            }

            $predictions[] = argmax($weights);
        }

        return $predictions;
    }

    /**
     * Estimate probabilities for each possible outcome.
     *
     * @param \Rubix\ML\Datasets\Dataset $dataset
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return array
     */
    public function proba(Dataset $dataset) : array
    {
        if ($this->tree->bare()) {
            throw new RuntimeException('The estimator has not'
                . ' been trained.');
        }

        DatasetIsCompatibleWithEstimator::check($dataset, $this);

        $template = array_fill_keys($this->classes, 0.);

        $probabilities = [];

        foreach ($dataset as $sample) {
            [$samples, $labels, $distances] = $this->tree->range($sample, $this->radius);

            $dist = $template;

            if (empty($labels)) {
                $dist[$this->anomalyClass] = 1.;

                $probabilities[] = $dist;

                continue 1;
            }

            if ($this->weighted) {
                $weights = array_fill_keys($labels, 0.);

                foreach ($labels as $i => $label) {
                    $weights[$label] += 1. / (1. + $distances[$i]);
                }
            } else {
                $weights = array_count_values($labels);
            }

            $total = array_sum($weights) ?: EPSILON;

            foreach ($weights as $class => $weight) {
                $dist[$class] = $weight / $total;
            }

            $probabilities[] = $dist;
        }

        return $probabilities;
    }
}
