<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/CrossValidation/Metrics/MedianAbsoluteError.php">Source</a></span>

# Median Absolute Error
Median Absolute Error (MAD) is a robust measure of error, similar to [MAE](mean-absolute-error.md), that ignores highly erroneous predictions. Because MAD is a robust statistic, it works well even when used to measure non-normal distributions.

> **Note:** In order to maintain the convention of *maximizing* validation scores, this metric outputs the negative of the original score.

**Estimator Compatibility:** Regressor

**Output Range:** -∞ to 0

### Example
```php
use Rubix\ML\CrossValidation\Metrics\MedianAbsoluteError;

$metric = new MedianAbsoluteError();
```