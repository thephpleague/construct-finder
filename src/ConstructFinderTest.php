<?php

declare(strict_types=1);

namespace League\ConstructFinder;

use League\ConstructFinder\Fixtures\SomeClass;
use League\ConstructFinder\Fixtures\SomeEnum;
use League\ConstructFinder\Fixtures\SomeInterface;
use League\ConstructFinder\Fixtures\SomeTrait;
use PHPUnit\Framework\TestCase;

class ConstructFinderTest extends TestCase
{
    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function it_finds_constructs_of_any_type(): void
    {
        $expectedConstructs = [
            new Construct(SomeClass::class, 'class'),
            new Construct(SomeEnum::class, 'enum'),
            new Construct(SomeInterface::class, 'interface'),
            new Construct(SomeTrait::class, 'trait'),
        ];

        $constructs = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')->findAll();

        self::assertCount(4, $constructs);
        self::assertContainsOnlyInstancesOf(Construct::class, $constructs);
        self::assertEquals($expectedConstructs, $constructs);
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function it_finds_constructs_of_any_type_as_names(): void
    {
        $expectedConstructs = [
            SomeClass::class,
            SomeEnum::class,
            SomeInterface::class,
            SomeTrait::class,
        ];

        $constructs = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')->findAllNames();

        self::assertCount(4, $constructs);
        self::assertContainsOnly('string', $constructs);
        self::assertEquals($expectedConstructs, $constructs);
    }

    /**
     * @test
     */
    public function paths_can_be_excluded_using_patterns(): void
    {
        $constructs = ConstructFinder::locatedIn(__DIR__)
            ->exclude(
                '*Test.php',
                __DIR__ . '/*/*.php',
            )
            ->findAll();

        self::assertCount(2, $constructs);
        self::assertContainsOnlyInstancesOf(Construct::class, $constructs);
    }

    /**
     * @test
     */
    public function it_can_find_only_classes(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*Enum.php', // PHP 8.1
                '*83+.php', // PHP 8.3
            )->findClasses();

        self::assertCount(1, $classes);
        self::assertEquals('class', $classes[0]->type());
    }

    /**
     * @test
     * @requires PHP >= 8.3
     */
    public function it_can_find_only_classes_and_ignores_anonymous_ones(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')->findClasses();

        self::assertCount(1, $classes);
        self::assertEquals('class', $classes[0]->type());
    }

    /**
     * @test
     */
    public function it_can_find_only_class_names(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*Enum.php', // PHP 8.1
                '*83+.php', // PHP 8.3
            )
            ->findClassNames();

        self::assertCount(1, $classes);
        self::assertEquals(SomeClass::class, $classes[0]);
    }

    /**
     * @test
     */
    public function it_can_find_only_interfaces(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*Enum.php', // PHP 8.1
                '*83+.php', // PHP 8.3
            )
            ->findInterfaces();

        self::assertCount(1, $classes);
        self::assertEquals('interface', $classes[0]->type());
    }

    /**
     * @test
     */
    public function it_can_find_only_interface_names(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*Enum.php', // PHP 8.1
                '*83+.php', // PHP 8.3
            )
            ->findInterfaceNames();

        self::assertCount(1, $classes);
        self::assertEquals(SomeInterface::class, $classes[0]);
    }

    /**
     * @test
     */
    public function it_can_find_only_traits(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*Enum.php', // PHP 8.1
                '*83+.php', // PHP 8.3
            )
            ->findTraits();

        self::assertCount(1, $classes);
        self::assertEquals('trait', $classes[0]->type());
    }

    /**
     * @test
     */
    public function it_can_find_only_trait_names(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*Enum.php', // PHP 8.1
                '*83+.php', // PHP 8.3
            )
            ->findTraitNames();

        self::assertCount(1, $classes);
        self::assertSame(SomeTrait::class, $classes[0]);
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function it_can_find_only_enums(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*83+.php', // PHP 8.3
            )
            ->findEnums();

        self::assertCount(1, $classes);
        self::assertEquals('enum', $classes[0]->type());
    }

    /**
     * @test
     * @requires PHP >= 8.1
     */
    public function it_can_find_only_enums_names(): void
    {
        $classes = ConstructFinder::locatedIn(__DIR__ . '/Fixtures/')
            ->exclude(
                '*83+.php', // PHP 8.3
            )
            ->findEnumNames();

        self::assertCount(1, $classes);
        self::assertSame(SomeEnum::class, $classes[0]);
    }
}
