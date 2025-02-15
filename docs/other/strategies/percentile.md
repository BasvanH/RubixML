<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Other/Strategies/Percentile.php">Source</a></span>

# Blurry Percentile
A strategy that always guesses the p-th percentile of the fitted data.

**Data Type:** Continuous

### Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | p | 50.0 | float | The percentile of the fitted data to use as a guess. |

### Additional Methods
This strategy does not have any additional methods.

### Example
```php
use Rubix\ML\Other\Strategies\Percentile;

$strategy = new Percentile(90.0);
```