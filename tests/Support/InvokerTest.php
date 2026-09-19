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

it('reuses the instance created from a class name', function () {
    $invoker = $this->invoker(TestInvoker::class);
    $invoker->id = 2;

    expect($invoker->getId())->toBe(2);

    $invoker->setId(3);

    expect($invoker->id)->toBe(3);
});

it('distinguishes nested keys from properties with the same name', function () {
    $invoker = $this->invoker(new TestInvoker(config: ['config' => ['id' => 1]]));

    expect($invoker->get('config.config'))->toBe(['id' => 1]);

    $invoker->set('config.config', ['id' => 2]);
    $invoker->set('instances.instances', ['id' => 3]);

    expect($invoker->config)->toBe(['config' => ['id' => 2]])
        ->and($invoker->get('instances.instances'))->toBe(['id' => 3]);
});

it('initializes a typed static property', function () {
    $invoker = $this->invoker(new class
    {
        private static array $items;
    });

    $invoker->items = ['id' => 1];

    expect($invoker->items)->toBe(['id' => 1]);
});

it('reads and writes dynamic properties', function () {
    $object = (object) ['id' => 1];
    $invoker = $this->invoker($object);

    expect($invoker->id)->toBe(1);

    $object->config = ['id' => 2];
    $invoker->id = 3;
    $invoker->set('config.id', 4);

    expect($object->id)->toBe(3)
        ->and($invoker->get('config.id'))->toBe(4)
        ->and($object->config)->toBe(['id' => 4]);
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
