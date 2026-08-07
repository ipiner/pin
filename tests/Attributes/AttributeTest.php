<?php

declare(strict_types=1);

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

enum TestEnum
{
    #[TestCaseAttribute('case-value')]
    case Foo;

    case Bar;
}

test('resolve class attribute', function () {
    $attribute = Pin\Attributes\Attribute::get(
        TestClass::class,
        TestClassAttribute::class
    );

    expect($attribute)
        ->toBeInstanceOf(TestClassAttribute::class)
        ->and($attribute->value)->toBe('class-value');
});

test('resolve enum case attribute', function () {
    $attribute = Pin\Attributes\Attribute::get(
        TestEnum::Foo,
        TestCaseAttribute::class
    );

    expect($attribute)
        ->toBeInstanceOf(TestCaseAttribute::class)
        ->and($attribute->value)->toBe('case-value');
});

test('return null when attribute does not exist', function () {
    $attribute = Pin\Attributes\Attribute::get(
        TestClass::class,
        TestCaseAttribute::class
    );

    expect($attribute)->toBeNull();
});
