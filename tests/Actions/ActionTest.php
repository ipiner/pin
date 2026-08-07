<?php

declare(strict_types=1);

use Pin\Actions\Action;
use Pin\Exceptions\FakeResponseException;

it('sets modelClass after boot', function () {
    $action = new TestAction();
    $action->boot();
    expect($action->modelClass)->toBe('App\\Models\\Test');
});

it('builds queryable object from rules and payload', function () {
    $action = new TestAction();
    $action->boot();
    $action->payload([])->withRules(['name' => 'q:eq'])->boot();

    expect($action->queryable()->conditions['name']->type)->toBe('eq');
});

it('injects payload, route parameters and route name after resolving', function () {
    $this->app['request']->query->set('name', 'foo');
    $action = app(TestAction::class);

    expect($action->payload()['name'])->toBe('foo')
        ->and($action->context()->__route_name__)->toBeNull();
});

it('does not throw fake response exception when fake response is disabled', function () {
    config(['pin.action.fake_response_enabled' => false]);
    $this->app['request']->query->set('_fake', '1');
    app(TestAction::class);

    expect(true)->toBeTrue();
});

it('throws fake response exception when fake response is enabled', function () {
    config(['pin.action.fake_response_enabled' => true]);
    $this->app['request']->query->set('_fake', '1');

    app(TestAction::class);

})->throws(FakeResponseException::class);

/**
 * @internal
 */
class TestAction extends Action
{
}
