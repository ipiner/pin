<?php

declare(strict_types=1);

use App\Models\User;
use Pin\Errors\Errors;
use Pin\Http\Controller;
use Pin\Services\Results\UpdateResult;
use Pin\Support\Json;

it('handles error responses', function () {
    $ctrl = new Controller();

    expect($ctrl->error(Errors::None)->toArray()['code'])
        ->toBe(Errors::Failed->code())
        ->and($ctrl->error(123)->toArray()['code'])
        ->toBe(123);
});

it('handles success responses', function () {
    $ctrl = new Controller();
    $res = Json::encode($ctrl->success(new UpdateResult(new User(), true)));
    $res = Json::decode($res);

    expect($res['code'])->toBe(Errors::None->code())
        ->and($res['message'])->toBe('Update successfully')
        ->and($res['data'])->toBe(['updated' => true]);

    $res = Json::encode($ctrl->success(message: 'ok'));
    $res = Json::decode($res);
    expect($res['message'])->toBe('ok')
        ->and($res['data'])->toBeNull();
});
