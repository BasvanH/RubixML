<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Other/Strategies/Prior.php">Source</a></span>

# Prior
A strategy where the probability of guessing a class is equal to the class's prior probability.

**Data Type:** Categorical

### Parameters
This strategy does not have any parameters.

### Additional Methods
Return the prior probabilities of each class:
```php
public priors() : array
```

### Example
```php
use Rubix\ML\Other\Strategies\Prior;

$strategy = new Prior();
```