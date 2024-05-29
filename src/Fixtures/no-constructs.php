<?php

declare(strict_types=1);

namespace {
    function notAConstruct() {}
}

namespace Something {

    use League\ConstructFinder\Fixtures\SomeInterface;

    new class implements SomeInterface {};

    if (PHP_VERSION_ID > 80300) {
        new readonly class () implements SomeInterface {}; // PHP 8.3+
    }
}
