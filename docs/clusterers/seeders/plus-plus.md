<span style="float:right;"><a href="https://github.com/RubixML/RubixML/blob/master/src/Clusterers/Seeders/PlusPlus.php">Source</a></span>

# Plus Plus
This seeder attempts to maximize the likelihood of seeding distant clusters while still remaining random. It does so by sequentially selecting random samples weighted by their distance from the previous seed.

### Parameters
| # | Param | Default | Type | Description |
|---|---|---|---|---|
| 1 | kernel | Euclidean | object | The distance kernel used to compute the distance between samples. |

### Example
```php
use Rubix\ML\Clusterers\Seeders\PlusPlus;
use Rubix\ML\Kernels\Distance\Minkowski;

$seeder = new PlusPlus(new Minkowski(5.0));
```

### References
>- D. Arthur et al. (2006). k-means++: The Advantages of Careful Seeding.
>- A. Stetco et al. (2015). Fuzzy C-means++: Fuzzy C-means with effective seeding initialization.