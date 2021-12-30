<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use const ASSERT_ACTIVE;

class ConstructTest extends TestCase
{
    /**
     * @test
     */
    public function stringify_a_construct(): void
    {
        $construct = new Construct(ConstructTest::class, 'class');

        $string = (string) $construct;

        self::assertEquals(ConstructTest::class, $string);
    }

    /**
     * @test
     */
    public function invalid_types_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Construct(ConstructTest::class, 'invalid');
    }
}
