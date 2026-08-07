<?php

declare(strict_types=1);

use Pin\Actions\Action;

it('generates fake data from validation rules', function () {
    $action = new class extends Action
    {
        protected function rules(): array
        {
            return [
                'name' => 'string',
                'email' => 'email',
            ];
        }
    };

    $data = $action::fake();

    expect(array_keys($data))
        ->toBe(['name', 'email'])
        ->and(strlen($data['name']))
        ->toBe(16)
        ->and($data['email'])
        ->toContain('@');
});

it('overrides and appends fake attributes', function () {
    $action = new class extends Action
    {
        protected function rules(): array
        {
            return [
                'name' => 'string',
                'email' => 'email',
            ];
        }
    };

    $data = $action::fake([
        'a' => 'a',
        'name' => 'foo',
    ]);

    expect(array_keys($data))
        ->toBe(['name', 'email', 'a'])
        ->and($data['name'])
        ->toBe('foo')
        ->and($data['a'])
        ->toBe('a');
});
