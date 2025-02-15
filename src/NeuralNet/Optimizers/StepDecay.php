<?php

namespace Rubix\ML\NeuralNet\Optimizers;

use Tensor\Tensor;
use Rubix\ML\NeuralNet\Parameters\Parameter;
use InvalidArgumentException;

/**
 * Step Decay
 *
 * A linear learning rate scheduler that reduces the learning rate by a factor
 * of the decay parameter whenever it reaches a new *floor*. The number of
 * steps needed to reach a new floor is defined by the *steps* parameter.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class StepDecay implements Optimizer
{
    /**
     * The learning rate that controls the global step size.
     *
     * @var float
     */
    protected $rate;

    /**
     * The size of every floor in steps. i.e. the number of steps to take before
     * applying another factor of decay.
     *
     * @var int
     */
    protected $steps;

    /**
     * The factor to decrease the learning rate by over a period of k steps.
     *
     * @var float
     */
    protected $decay;

    /**
     * The number of steps taken so far.
     *
     * @var int
     */
    protected $t = 0;

    /**
     * @param float $rate
     * @param int $steps
     * @param float $decay
     * @throws \InvalidArgumentException
     */
    public function __construct(float $rate = 0.01, int $steps = 100, float $decay = 1e-3)
    {
        if ($rate <= 0.) {
            throw new InvalidArgumentException('Learning rate must be'
                . " greater than 0, $rate given.");
        }

        if ($steps < 1) {
            throw new InvalidArgumentException('The number of steps per'
                . " floor must be greater than 0, $steps given.");
        }

        if ($decay < 0.) {
            throw new InvalidArgumentException('Decay rate must be'
                . " positive, $decay given.");
        }

        $this->rate = $rate;
        $this->steps = $steps;
        $this->decay = $decay;
    }

    /**
     * Take a step of gradient descent for a given parameter.
     *
     * @param \Rubix\ML\NeuralNet\Parameters\Parameter $param
     * @param \Tensor\Tensor $gradient
     * @return \Tensor\Tensor
     */
    public function step(Parameter $param, Tensor $gradient) : Tensor
    {
        $f = floor($this->t / $this->steps);

        $rate = $this->rate * (1. / (1. + $f * $this->decay));

        $this->t++;

        return $gradient->multiply($rate);
    }
}
