<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;

it('handles payload assignment and faking', function () {
    $t = UserRoute::Create->testing($this);

    $invoker = $this->invoker($t);

    expect($invoker->payload)->toBeNull();

    $t->withPayload(['username' => 'foo']);
    expect($invoker->payload)->toBe(['username' => 'foo']);

    $t->fakePayload(['a' => 'a']);
    $payload = $invoker->payload;

    expect($payload['username'])->not->toBe('foo')
        ->and($payload['a'])->toBe('a');
});
