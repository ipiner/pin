<?php

declare(strict_types=1);

use Pin\Support\Traits\HasPayload;

beforeEach(function () {
    $this->action = new class
    {
        use HasPayload;
    };
});

it('sets and gets payload key and value', function () {
    expect($this->action->payload('id', 1)->payload())->toBe(['id' => 1]);
});

it('sets multiple payload values', function () {
    expect($this->action->payload(['id' => 2, 'name' => 'foo'])->payload())
        ->toBe(['id' => 2, 'name' => 'foo']);
});

it('replaces the payload entirely', function () {
    expect($this->action->payload(null, ['id' => 1])->payload())->toBe(['id' => 1]);
});

it('retrieves nested payload value by key', function () {
    expect($this->action->payload(['user' => ['id' => 1]])->payload('user.id'))->toBe(1);
});

it('sets explicit null values and clears the payload', function () {
    $this->action->payload('user.id', null);

    expect($this->action->payload())->toBe(['user' => ['id' => null]])
        ->and($this->action->payload(null))->toBe($this->action)
        ->and($this->action->payload())->toBe([]);
});
