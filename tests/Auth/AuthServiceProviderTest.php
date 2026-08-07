<?php

declare(strict_types=1);

use Pin\Auth\Guard;

it('registers custom auth guard', function () {
    expect(auth()->guard(Guard::NAME))->toBeInstanceOf(Guard::class);
});
