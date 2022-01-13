<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use InvalidArgumentException;

use function in_array;

class Construct
{
    /** @var class-string */
    private string $name;

    /** @var 'trait'|'class'|'enum'|'interface' */
    private string $type;

    /**
     * @internal
     *
     * @param class-string $name
     * @param 'trait'|'class'|'enum'|'interface' $type
     */
    public function __construct(string $name, string $type)
    {
        if ( ! in_array($type, ['trait', 'class', 'enum', 'interface'])) {
            throw new InvalidArgumentException('Construct type must be one of: class, trait, enum, or interface.');
        }

        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return class-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return 'trait'|'class'|'enum'|'interface'
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return class-string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
