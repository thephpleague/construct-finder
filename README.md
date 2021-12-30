# Construct Finder

This library helps you locate classes, interfaces, traits, and enums in PHP code. The construct
finder locates all code constructs located in a directory.

## Installation

```bash
composer require league/construct-finder
```

## Usage

Finding constructs:

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

Using a construct

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
