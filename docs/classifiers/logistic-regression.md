<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Classifiers/LogisticRegresion.php">Source</a></span>

# Logistic Regression
A linear classifier that uses the logistic (*sigmoid*) function to estimate the probabilities of exactly *two* classes. The model parameters (weights and bias) are solved using mini batch Gradient Descent with pluggable optimizers and cost functions that run on the neural network subsystem.

**Interfaces:** [Estimator](../estimator.md), [Learner](../learner.md), [Online](../online.md), [Probabilistic](../probabilistic.md), [Verbose](../verbose.md), [Persistable](../persistable.md)

**Data Type Compatibility:** Continuous

### Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | batch size | 100 | int | The number of training samples to process at a time. |
| 2 | optimizer | Adam | object | The gradient descent optimizer used to update the network parameters. |
| 3 | alpha | 1e-4 | float | The amount of L2 regularization to apply to the parameters of the network. |
| 4 | epochs | 1000 | int | The maximum number of training epochs. i.e. the number of times to iterate over the entire training set before terminating. |
| 5 | min change | 1e-4 | float | The minimum change in the training loss necessary to continue training. |
| 6 | window | 5 | int | The number of epochs without improvement in the training loss to wait before considering an early stop. |
| 7 | cost fn | CrossEntropy | object | The function that computes the loss associated with an erroneous activation during training. |

### Additional Methods
Return the training loss at each epoch:
```php
public steps() : array
```

Return the underlying neural network instance or `null` if untrained:
```php
public network() : Network|null
```

### Example
```php
use Rubix\ML\Classifiers\LogisticRegression;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\NeuralNet\CostFunctions\CrossEntropy;

$estimator = new LogisticRegression(10, new Adam(0.001), 1e-4, 100, 1e-4, 5, new CrossEntropy());
```
