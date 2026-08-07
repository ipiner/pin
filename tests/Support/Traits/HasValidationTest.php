<?php

declare(strict_types=1);

use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Pin\Actions\Action;
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
