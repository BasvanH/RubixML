<?php

namespace Rubix\ML\NeuralNet\Optimizers;

use Tensor\Tensor;
use Rubix\ML\NeuralNet\Parameters\Parameter;
use InvalidArgumentException;

use const Rubix\ML\EPSILON;

/**
 * AdaGrad
 *
 * Short for Adaptive Gradient, the AdaGrad Optimizer speeds up the learning of
 * parameters that do not change often and slows down the learning of parameters
 * that do enjoy heavy activity.
 *
 * References:
 * [1] J. Duchi et al. (2011). Adaptive Subgradient Methods for Online Learning
 * and Stochastic Optimization.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class AdaGrad implements Optimizer, Adaptive
{
    /**
     * The learning rate that controls the global step size.
     *
     * @var float
     */
    protected $rate;

    /**
     * The cache of sum of squared gradients.
     *
     * @var \Tensor\Tensor[]
     */
    protected $cache;

    /**
     * @param float $rate
     * @throws \InvalidArgumentException
     */
    public function __construct(float $rate = 0.01)
    {
        if ($rate <= 0.) {
            throw new InvalidArgumentException('Learning rate must be'
                . " greater than 0, $rate given.");
        }

        $this->rate = $rate;
    }
    
    /**
     * Warm the cache.
     *
     * @param \Rubix\ML\NeuralNet\Parameters\Parameter $param
     */
    public function warm(Parameter $param) : void
    {
        $this->cache[$param->id()] = get_class($param->w())::zeros(...$param->w()->shape());
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
        $norm = $this->cache[$param->id()];

        $norm = $norm->add($gradient->square());

        $this->cache[$param->id()] = $norm;

        return $gradient->multiply($this->rate)
            ->divide($norm->sqrt()->clipLower(EPSILON));
    }
}
