<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

use function array_filter;
use function array_key_exists;
use function array_push;
use function array_values;
use function count;
use function define;
use function defined;
use function file_get_contents;
use function in_array;
use function is_a;
use function is_array;
use function iterator_to_array;
use function preg_match;
use function preg_quote;
use function str_ends_with;
use function str_replace;
use function token_get_all;
use function trim;

use function usort;

use const T_CLASS;
use const T_COMMENT;
use const T_DOC_COMMENT;
use const T_ENUM;
use const T_INTERFACE;
use const T_NAMESPACE;
use const T_NEW;
use const T_TRAIT;
use const T_WHITESPACE;
use const TOKEN_PARSE;

class ConstructFinder
{
    private array $locations;

    /**
     * @var string[]
     */
    private array $excludes = [];

    public function __construct(array $locations)
    {
        $this->locations = $locations;
    }

    public function exclude(string ...$patterns): self
    {
        $this->excludes = $this->prepPatterns($patterns);

        return $this;
    }

    /**
     * @return Construct[]
     */
    public function findAll(): array
    {
        $listing = $this->processExcludes($this->listAllFiles());
        $constructs = iterator_to_array($this->collectConstructs($listing), false);

        usort($constructs, fn(Construct $a, Construct $b) => $a->name() <=> $b->name());

        return array_values($constructs);
    }

    /**
     * @return Construct[]
     */
    public function findOfType(string $type): array
    {
        $all = $this->findAll();

        return array_values(array_filter($all, fn(Construct $c) => $c->type() === $type));
    }

    /**
     * @return Construct[]
     */
    public function findClasses(): array
    {
        return $this->findOfType('class');
    }

    /**
     * @return Construct[]
     */
    public function findEnums(): array
    {
        return $this->findOfType('enum');
    }

    /**
     * @return Construct[]
     */
    public function findInterfaces(): array
    {
        return $this->findOfType('interface');
    }

    public function findTraits(): array
    {
        return $this->findOfType('trait');
    }

    private function locatePathsIn(string $directory): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ( ! $file->isFile()) {
                continue;
            }

            $realPath = $file->getRealPath();

            if ( ! str_ends_with($realPath, '.php')) {
                continue;
            }

            yield $realPath;
        }
    }

    public static function locatedIn(string ... $directory): static
    {
        return new static($directory);
    }

    private static function findConstructsInPath(string $path): array
    {
        $source = file_get_contents($path) ?: '';
        $classes = [];
        $interestingTokens = [T_CLASS => 'class', T_INTERFACE => 'interface', T_TRAIT => 'trait'];

        if (defined('T_ENUM')) {
            $interestingTokens[T_ENUM] = 'enum';
        }

        $tokens = token_get_all($source, TOKEN_PARSE);

        $tokens = array_filter(
            $tokens,
            fn(array|string $token) => ! in_array($token[0], [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]),
        );
        $tokens = array_values($tokens);

        $namespace = '';

        foreach ($tokens as $index => $token) {
            if ( ! is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = static::collectNamespace($index + 1, $tokens);
            }

            if (array_key_exists($token[0], $interestingTokens) === false || static::isNew($index - 1, $tokens)) {
                continue;
            }

            $classToken = $tokens[$index + 1];
            $type = $interestingTokens[$token[0]];
            $name = trim("$namespace\\$classToken[1]", '\\');
            $classes[] = new Construct($name, $type);
        }

        return $classes;
    }

    private static function collectNamespace(int $index, array $tokens): string
    {
        $token = $tokens[$index] ?? '';

        if ( ! is_array($token)) {
            return '';
        }

        if ($token[0] === T_NAME_QUALIFIED) {
            return (string) $token[1];
        }

        return '';
    }

    private static function isNew(int $index, array $tokens): bool
    {
        $token = $tokens[$index] ?? '';

        if ( ! is_array($token)) {
            return false;
        }

        $type = $token[0] ?? '';

        return $type === T_NEW;
    }

    /**
     * @param string[] $patterns
     * @return string[]
     */
    private function prepPatterns(array $patterns): array
    {
        $wildcard = preg_quote('*', '~');

        foreach ($patterns as $i => $pattern) {
            $patterns[$i] = str_replace($wildcard, '(.+)', preg_quote($pattern, '~'));
        }

        return $patterns;
    }

    private function listAllFiles(): Generator
    {
        foreach ($this->locations as $location) {
            yield from $this->locatePathsIn($location);
        }
    }

    private function processExcludes(Generator $listing): Generator
    {
        foreach ($listing as $path) {
            foreach ($this->excludes as $pattern) {
                if (preg_match("~^$pattern$~", $path) === 1) {
                    goto exclude;
                }
            }

            yield $path;
            exclude:
        }
    }

    private function collectConstructs(Generator $listing): Generator
    {
        foreach ($listing as $path) {
            yield from $this->findConstructsInPath($path);
        }
    }
}