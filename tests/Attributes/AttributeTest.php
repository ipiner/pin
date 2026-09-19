<?php

declare(strict_types=1);

namespace Pin\Tests\Attributes;

use Attribute;
use LogicException;
use Pin\Attributes\Attribute as AttributeReader;
use Pin\Support\Facades\RuntimeCache;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use UnitEnum;

#[Attribute(Attribute::TARGET_CLASS)]
class TestClassAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class TestCaseAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

#[TestClassAttribute('class-value')]
class TestClass
{
}

#[TestClassAttribute('enum-value')]
enum TestEnum
{
    #[TestCaseAttribute('case-value')]
    case Foo;

    case Bar;

    #[TestCaseAttribute('other-case-value')]
    case Baz;
}

enum TestBackedEnum: string
{
    #[TestCaseAttribute('backed-case-value')]
    case Foo = 'value-different-from-name';
}

class TestChildClass extends TestClass
{
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class TestRepeatableAttribute extends TestClassAttribute
{
}

#[TestRepeatableAttribute('first')]
#[TestRepeatableAttribute('second')]
class TestRepeatedClass
{
}

#[Attribute(Attribute::TARGET_CLASS)]
class TestFailingAttribute
{
    public function __construct()
    {
        throw new LogicException('Unable to construct the attribute.');
    }
}

#[TestFailingAttribute]
class TestFailingClass
{
}

class CountingAttributeReader extends AttributeReader
{
    public static int $resolutions = 0;

    protected static function resolve(ReflectionClass|ReflectionClassConstant $reflection, string $class): object|false
    {
        self::$resolutions++;

        return parent::resolve($reflection, $class);
    }
}

beforeEach(function () {
    RuntimeCache::flush();
    CountingAttributeReader::$resolutions = 0;
});

afterEach(function () {
    RuntimeCache::flush();
});

test('resolve class attribute', function () {
    $attribute = AttributeReader::get(
        TestClass::class,
        TestClassAttribute::class
    );

    expect($attribute)
        ->toBeInstanceOf(TestClassAttribute::class)
        ->and($attribute->value)->toBe('class-value');
});

test('resolve enum case attribute', function () {
    $attribute = AttributeReader::get(
        TestEnum::Foo,
        TestCaseAttribute::class
    );

    expect($attribute)
        ->toBeInstanceOf(TestCaseAttribute::class)
        ->and($attribute->value)->toBe('case-value');
});

test('return null when attribute does not exist', function () {
    $attribute = AttributeReader::get(
        TestClass::class,
        TestCaseAttribute::class
    );

    expect($attribute)->toBeNull();
});

it('reuses attribute instances without repeating reflection', function (UnitEnum|string $target, string $attribute) {
    $first = CountingAttributeReader::get($target, $attribute);

    expect($first)->toBeObject()
        ->and(CountingAttributeReader::get($target, $attribute))->toBe($first)
        ->and(CountingAttributeReader::$resolutions)->toBe(1);
})->with([
    [TestClass::class, TestClassAttribute::class],
    [TestEnum::Foo, TestCaseAttribute::class],
    [TestBackedEnum::Foo, TestCaseAttribute::class],
]);

it('caches missing attributes without repeating reflection', function (UnitEnum|string $target, string $attribute) {
    expect(CountingAttributeReader::get($target, $attribute))->toBeNull()
        ->and(CountingAttributeReader::get($target, $attribute))->toBeNull()
        ->and(CountingAttributeReader::$resolutions)->toBe(1);
})->with([
    [TestClass::class, TestCaseAttribute::class],
    [TestEnum::Bar, TestCaseAttribute::class],
]);

it('keeps class attributes and different enum cases separate', function () {
    expect(AttributeReader::get(TestEnum::class, TestClassAttribute::class)->value)->toBe('enum-value')
        ->and(AttributeReader::get(TestEnum::Foo, TestClassAttribute::class))->toBeNull()
        ->and(AttributeReader::get(TestEnum::Foo, TestCaseAttribute::class)->value)->toBe('case-value')
        ->and(AttributeReader::get(TestEnum::Baz, TestCaseAttribute::class)->value)->toBe('other-case-value')
        ->and(AttributeReader::get(TestBackedEnum::Foo, TestCaseAttribute::class)->value)->toBe('backed-case-value');
});

it('returns the first repeatable attribute and matches the attribute class exactly', function () {
    expect(AttributeReader::get(TestRepeatedClass::class, TestRepeatableAttribute::class)->value)->toBe('first')
        ->and(AttributeReader::get(TestRepeatedClass::class, TestClassAttribute::class))->toBeNull();
});

it('does not inherit attributes from the parent class', function () {
    expect(AttributeReader::get(TestChildClass::class, TestClassAttribute::class))->toBeNull();
});

it('recreates an attribute after clearing the runtime cache', function () {
    $first = AttributeReader::get(TestClass::class, TestClassAttribute::class);
    RuntimeCache::flush();

    expect(AttributeReader::get(TestClass::class, TestClassAttribute::class))->not->toBe($first);
});

it('propagates constructor errors without caching them as missing attributes', function () {
    for ($attempt = 0; $attempt < 2; $attempt++) {
        expect(fn () => CountingAttributeReader::get(TestFailingClass::class, TestFailingAttribute::class))
            ->toThrow(LogicException::class, 'Unable to construct the attribute.');
    }

    expect(CountingAttributeReader::$resolutions)->toBe(2);
});

it('propagates reflection errors for an unknown target', function () {
    expect(fn () => AttributeReader::get(__NAMESPACE__.'\\MissingClass', TestClassAttribute::class))
        ->toThrow(ReflectionException::class);
});
