<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Other/Strategies/Constant.php">Source</a></span>'

# Constant
Always guess the same value.

**Data Type:** Continuous

### Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | value | 0.0 | float | The value to constantly guess. |

### Additional Methods
This strategy does not have any additional methods.

### Example
```php
use Rubix\ML\Other\Strategies\Constant;

$strategy = new Constant(0.0);
```