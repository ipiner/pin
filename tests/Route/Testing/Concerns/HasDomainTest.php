<?php

declare(strict_types=1);

use App\Routes\User\UserRoute;

it('returns correct domain and allows overriding', function () {
    $t = UserRoute::Index->testing($this);
    $invoker = $this->invoker($t);

    expect($invoker->domain)->toBe('User');

    $t->withDomain('Order');
    expect($invoker->domain)->toBe('Order');
});
