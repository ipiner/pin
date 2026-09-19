<?php

declare(strict_types=1);

use Pin\Errors\Errors;

it('validates whitespace', function () {
    $this->rule->value('1 2');
    expect($this->invoker->validateWhiteSpace())->toBe(Errors::PasswordContainsWhitespace->code());

    $this->rule->allowWhitespace();
    expect($this->invoker->validateWhiteSpace())->toBe(0);
});

it('rejects trailing newlines', function () {
    $this->rule->value("password\n");

    expect($this->invoker->validateWhitespace())->toBe(Errors::PasswordContainsWhitespace->code());
});
