<?php

declare(strict_types=1);

it('gets and sets properties, methods, and nested values', function () {
    $invoker = $this->invoker(new TestInvoker());

    $invoker->instances = [];
    $invoker->config = [];

    expect($invoker->getId())->toBe(1)
        ->and($invoker->get('id'))->toBe(1)
        ->and($invoker->id)->toBe(1);

    expect($invoker->getName())->toBe('foo')
        ->and($invoker->name)->toBe('foo');

    $invoker->id = 2;
    $invoker->name = 'bar';

    expect($invoker->getId())->toBe(2)
        ->and($invoker->id)->toBe(2)
        ->and($invoker->getName())->toBe('bar')
        ->and($invoker->name)->toBe('bar');

    $invoker->setId(3);
    $invoker->setName('bar');

    expect($invoker->getId())->toBe(3)
        ->and($invoker->id)->toBe(3)
        ->and($invoker->getName())->toBe('bar')
        ->and($invoker->name)->toBe('bar');

    $invoker->set('instances', ['a' => true]);
    $invoker->set('instances.b', false);
    $invoker->set('config.a', true);

    expect($invoker->instances['a'])->toBeTrue()
        ->and($invoker->instances['b'])->toBeFalse()
        ->and($invoker->config['a'])->toBeTrue()
        ->and($invoker->get('instances.a'))->toBeTrue()
        ->and($invoker->get('instances.b'))->toBeFalse()
        ->and($invoker->get('config.a'))->toBeTrue();
});

class TestInvoker
{
    private static $instances = [];

    protected static string $name = 'foo';

    public function __construct(private int $id = 1, protected array $config = [])
    {
    }

    protected static function getName(): string
    {
        return static::$name;
    }

    protected static function setName(string $name): void
    {
        static::$name = $name;
    }

    private function getId(): int
    {
        return $this->id;
    }

    private function setId(int $id): void
    {
        $this->id = $id;
    }
}
