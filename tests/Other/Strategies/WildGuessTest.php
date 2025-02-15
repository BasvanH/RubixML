<?php

namespace Rubix\ML\Tests\Other\Strategies;

use Rubix\ML\Other\Strategies\Strategy;
use Rubix\ML\Other\Strategies\WildGuess;
use Rubix\ML\Other\Strategies\Continuous;
use PHPUnit\Framework\TestCase;

class WildGuessTest extends TestCase
{
    protected $values;

    protected $strategy;

    public function setUp()
    {
        $this->values = [1, 2, 3, 4, 5];

        $this->strategy = new WildGuess(0.5);
    }

    public function test_build_random_copy_paste_strategy()
    {
        $this->assertInstanceOf(WildGuess::class, $this->strategy);
        $this->assertInstanceOf(Continuous::class, $this->strategy);
        $this->assertInstanceOf(Strategy::class, $this->strategy);
    }

    public function test_range_guess()
    {
        $this->strategy->fit($this->values);

        $guess = $this->strategy->guess();

        $this->assertThat(
            $guess,
            $this->logicalAnd(
                $this->greaterThanOrEqual(1),
                $this->lessThanOrEqual(5)
            )
        );
    }
}
