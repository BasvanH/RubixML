<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/NeuralNet/Optimizers/Momentum.php">Source</a></span>

# Momentum
Momentum adds to each step by accumulating velocity from past updates and adding a factor of the previous velocity to the current step. Momentum can help speed up training and escape bad local minima when compared with [Stochastic](stochastic.md) Gradient Descent.

### Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | rate | 0.001 | float | The learning rate that controls the global step size. |
| 2 | decay | 0.1 | float | The decay rate of the accumulated velocity. |

### Example
```php
use Rubix\ML\NeuralNet\Optimizers\Momentum;

$optimizer = new Momentum(0.001, 0.2);
```

### References
>- D. E. Rumelhart et al. (1988). Learning representations by back-propagating errors.