<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/CrossValidation/Metrics/MeanAbsoluteError.php">Source</a></span>

# Mean Absolute Error
A scale-dependent metric that measures the average absolute error between a set of predictions and their ground truth labels. MAE has the same units of measurement as the labels being estimated.

> **Note:** In order to maintain the convention of *maximizing* validation scores, this metric outputs the negative of the original score.

**Estimator Compatibility:** Regressor

**Output Range:** -∞ to 0

### Example
```php
use Rubix\ML\CrossValidation\Metrics\MeanAbsoluteError;

$metric = new MeanAbsoluteError();
```