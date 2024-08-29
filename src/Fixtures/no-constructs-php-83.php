<?php

declare(strict_types=1);

use League\ConstructFinder\Fixtures\SomeInterface;

new readonly class () implements SomeInterface {}; // PHP 8.3+