<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use InvalidArgumentException;

use function in_array;

class Construct
{
    private string $name;

    private string $type;

    /**
     * @internal
     */
    public function __construct(string $name, string $type)
    {
        if ( ! in_array($type, ['trait', 'class', 'enum', 'interface'])) {
            throw new InvalidArgumentException('Construct type must be one of: class, trait, enum, or interface.');
        }

        $this->name = $name;
        $this->type = $type;
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
