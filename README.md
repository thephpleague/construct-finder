# Construct Finder

This library helps you locate classes, interfaces, traits, and enums in PHP code. The construct
finder locates all code constructs located in a directory.

## Installation

```bash
composer require league/construct-finder
```

## Usage

### Finding constructs

You can find all constructs or use a type specific finder.

```php
use League\ConstructFinder\ConstructFinder;

// Find all constructs
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')->findAll();

// Find all classes
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')->findClasses();

// Find all interfaces
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')->findInterfaces();

// Find all enums
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')->findEnums();

// Find all traits
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')->findTraits();
```

### Using a construct

Constructs are simple value objects that expose the name and the type.

```php
use League\ConstructFinder\Construct;
use League\ConstructFinder\ConstructFinder;
// Find all constructs
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')->findAll();

/** @var Construct $construct */
$construct = $constructs[0];

$name = $construct->name();
$name = (string) $construct;

$type = $construct->type(); // class, trait, interface, enum
```

### Finding in multiple directories

Provide multiple directories to search from in one go.

```php
use League\ConstructFinder\ConstructFinder;

// Find all constructs
$constructs = ConstructFinder::locatedIn(
    __DIR__ . '/SomeDirectory',
    __DIR__ . '/AnotherDirectory',
)->findAll();
```

### Excluding files based on exclude patterns

All patterns are match in full. You can use a wildcard (`*`) for fuzzy matching.

```php
use League\ConstructFinder\ConstructFinder;

// Find all constructs
$constructs = ConstructFinder::locatedIn(__DIR__ . '/SomeDirectory')
    ->exclude('*Test.php', '*/Tests/*')
    ->findAll();
```
