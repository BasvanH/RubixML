<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/CrossValidation/Metrics/RMSE.php">Source</a></span>

# RMSE
The Root Mean Squared Error is equivalent to the standard deviation of the error residuals in a regression problem. Since RMSE is just the square root of the [MSE](mean-squared-error.md), RMSE is also sensitive to outliers because larger errors have a disproportionately large effect on the score.

> **Note:** In order to maintain the convention of *maximizing* validation scores, this metric outputs the negative of the original score.

**Estimator Compatibility:** Regressor

**Output Range:** -∞ to 0

### Example
```php
use Rubix\ML\CrossValidation\Metrics\RMSE;

$metric = new RMSE();
```