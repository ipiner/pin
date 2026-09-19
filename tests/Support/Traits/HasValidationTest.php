<?php

declare(strict_types=1);

use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Pin\Action\Action;
use Pin\Support\Invoker;

beforeEach(function () {
    $this->action = new HasValidationAction();

    $this->action->boot();
    $this->invoker = new Invoker($this->action);
});

it('throws UnauthorizedException when authorization fails', function () {
    $this->invoker->authorize = false;
    $this->action->validated();
})->throws(UnauthorizedException::class);

it('throws ValidationException when validation fails', function () {
    $this->action->validated();
})->throws(ValidationException::class);

it('returns validated data on success', function () {
    $this->action->payload('name', 'foo');
    expect($this->action->validated())->toBe(['name' => 'foo']);
});

it('works with custom rules', function () {
    expect($this->action->withRules(['foo' => 'nullable'])->validated())->toBe([]);
});

it('validates again after changing the payload', function (Closure $change) {
    $this->action->payload('name', 'foo');

    expect($this->action->validated())->toBe(['name' => 'foo']);

    $change($this->action);
    $this->action->validated();
})->with([
    'single value' => [fn ($action) => $action->payload('name', null)],
    'multiple values' => [fn ($action) => $action->payload(['name' => null])],
    'replacement' => [fn ($action) => $action->payload(null, [])],
    'clear' => [fn ($action) => $action->payload(null)],
])->throws(ValidationException::class);

it('keeps validated data cached when reading the payload', function () {
    $this->action->payload('name', 'foo');
    $validated = $this->action->validated();
    $this->action->authorize = false;

    expect($this->action->payload())->toBe($validated)
        ->and($this->action->payload('name'))->toBe('foo')
        ->and($this->action->validated())->toBe($validated);
});

/**
 * @internal
 */
class HasValidationAction extends Action
{
    public $authorize = true;

    protected function authorize(): bool
    {
        return $this->authorize;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|q:eq',
        ];
    }
}
