<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use InvalidArgumentException;
use Stringable;

use function assert;
use function in_array;

class Construct implements Stringable
{
    /**
     * @internal
     */
    public function __construct(private string $name, private string $type)
    {
        if ( ! in_array($this->type, ['trait', 'class', 'enum', 'interface'])) {
            throw new InvalidArgumentException('Construct type must be one of: class, trait, enum, or interface.');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
