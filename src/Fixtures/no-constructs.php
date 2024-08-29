<?php

declare(strict_types=1);

namespace {
    function notAConstruct() {}
}

namespace Something {

    use League\ConstructFinder\Fixtures\SomeInterface;

    new class implements SomeInterface {};
}
