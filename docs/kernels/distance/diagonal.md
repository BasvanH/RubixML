<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Kernels/Distance/Diagonal.php">Source</a></span>

# Diagonal
The Diagonal (a.k.a. *Chebyshev*) distance is a measure that constrains movement to horizontal, vertical, and diagonal movement from a point. An example of a game that uses diagonal movement is a chess board.

**Data Type Compatibility:** Continuous

### Parameters
This kernel does not have any parameters.

### Example
```php
use Rubix\ML\Kernels\Distance\Diagonal;

$kernel = new Diagonal();
```