<?php

declare(strict_types=1);

use Pin\Errors\Translator;

it('translates messages', function () {
    trans()->addPath(__DIR__.'/../../lang');

    // unbound
    $translator = app('translator');
    app()->offsetUnset('translator');

    expect(Translator::trans('pin::errors.success'))->toBe('pin::errors.success');

    // rebound
    app()->instance('translator', $translator);
    expect(Translator::trans('pin::errors.success'))->toBe('Success')
        ->and(Translator::trans('pin::errors.success', [], 'zh_CN'))->toBe('请求成功');
});

it('falls back translation', function () {
    $o = $this->invoker(Translator::class);

    expect($o->transFallback(':no :replace'))
        ->toBe(':no :replace')
        ->and($o->transFallback(
            'no replace',
            ['no' => 'no', 'replace' => 'replace']
        ))->toBe('no replace');
});
