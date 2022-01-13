<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function array_filter;
use function array_key_exists;
use function array_values;
use function defined;
use function file_get_contents;
use function implode;
use function in_array;
use function is_array;
use function iterator_to_array;
use function preg_match;
use function preg_quote;
use function str_replace;
use function substr;
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
use const T_STRING;
use const T_TRAIT;
use const T_WHITESPACE;
use const TOKEN_PARSE;

class ConstructFinder
{
    /** @var array<string> */
    private array $locations;

    /** @var array<string> */
    private array $excludes = [];

    /**
     * @param array<string> $locations
     */
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
     * @return array<Construct>
     */
    public function findAll(): array
    {
        $listing = $this->processExcludes($this->listAllFiles());
        $constructs = iterator_to_array($this->collectConstructs($listing), false);

        usort($constructs, fn(Construct $a, Construct $b) => $a->name() <=> $b->name());

        return $constructs;
    }

    /**
     * @return array<class-string>
     */
    public function findAllNames(): array
    {
        return $this->convertConstructsToStrings($this->findAll());
    }

    /**
     * @param array<Construct> $constructs
     * @return array<class-string>
     */
    private function convertConstructsToStrings(array $constructs): array
    {
        $classNames = [];

        foreach ($constructs as $construct) {
            $classNames[] = $construct->name();
        }

        return $classNames;
    }

    /**
     * @param 'trait'|'class'|'enum'|'interface' $type
     * @return array<Construct>
     */
    public function findOfType(string $type): array
    {
        $all = $this->findAll();

        return array_values(array_filter($all, fn(Construct $c) => $c->type() === $type));
    }

    /**
     * @return array<Construct>
     */
    public function findClasses(): array
    {
        return $this->findOfType('class');
    }

    /**
     * @return array<class-string>
     */
    public function findClassNames(): array
    {
        return $this->convertConstructsToStrings($this->findClasses());
    }

    /**
     * @return array<Construct>
     */
    public function findEnums(): array
    {
        return $this->findOfType('enum');
    }

    /**
     * @return array<class-string>
     */
    public function findEnumNames(): array
    {
        return $this->convertConstructsToStrings($this->findEnums());
    }

    /**
     * @return array<Construct>
     */
    public function findInterfaces(): array
    {
        return $this->findOfType('interface');
    }

    /**
     * @return array<class-string>
     */
    public function findInterfaceNames(): array
    {
        return $this->convertConstructsToStrings($this->findInterfaces());
    }

    /**
     * @return array<Construct>
     */
    public function findTraits(): array
    {
        return $this->findOfType('trait');
    }

    /**
     * @return array<class-string>
     */
    public function findTraitNames(): array
    {
        return $this->convertConstructsToStrings($this->findTraits());
    }

    /**
     * @return Generator<string>
     */
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

            if ($realPath === false || substr($realPath, -4) !== '.php') {
                continue;
            }

            yield $realPath;
        }
    }

    public static function locatedIn(string ...$directory): self
    {
        return new self($directory);
    }

    /**
     * @return array<Construct>
     */
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
            fn($token) => ! in_array($token[0], [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]),
        );
        $tokens = array_values($tokens);

        $namespace = '';

        foreach ($tokens as $index => $token) {
            if ( ! is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = self::collectNamespace($index + 1, $tokens);
            }

            if (array_key_exists($token[0], $interestingTokens) === false || self::isNew($index - 1, $tokens)) {
                continue;
            }

            $classToken = $tokens[$index + 1];
            $type = $interestingTokens[$token[0]];
            $name = trim("$namespace\\$classToken[1]", '\\');
            // @phpstan-ignore-next-line since we know $name is a class-string
            $classes[] = new Construct($name, $type);
        }

        return $classes;
    }

    /**
     * @param array<int, array<int, int|string>|string> $tokens
     */
    private static function collectNamespace(int $index, array $tokens): string
    {
        $token = $tokens[$index] ?? '';

        if ( ! is_array($token)) {
            return '';
        }

        if (defined('T_NAME_QUALIFIED') && $token[0] === T_NAME_QUALIFIED) {
            return (string) $token[1];
        }

        $parts = [];

        while (true) {
            $token = $tokens[$index] ?? '';
            $index++;

            if ( ! is_array($token)) {
                break;
            }

            if ( ! in_array($token[0], [T_NS_SEPARATOR, T_STRING])) {
                break;
            }

            $parts[] = $token[1];
        }

        return implode('', $parts);
    }

    /**
     * @param array<int, array<int, int|string>|string> $tokens
     */
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
     * @param array<string> $patterns
     * @return array<string>
     */
    private function prepPatterns(array $patterns): array
    {
        $wildcard = preg_quote('*', '~');

        foreach ($patterns as $i => $pattern) {
            $patterns[$i] = str_replace($wildcard, '(.+)', preg_quote($pattern, '~'));
        }

        return $patterns;
    }

    /**
     * @return Generator<string>
     */
    private function listAllFiles(): Generator
    {
        foreach ($this->locations as $location) {
            yield from $this->locatePathsIn($location);
        }
    }

    /**
     * @param Generator<string> $listing
     *
     * @return Generator<string>
     */
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

    /**
     * @param Generator<string> $listing
     *
     * @return Generator<Construct>
     */
    private function collectConstructs(Generator $listing): Generator
    {
        foreach ($listing as $path) {
            yield from $this->findConstructsInPath($path);
        }
    }
}
