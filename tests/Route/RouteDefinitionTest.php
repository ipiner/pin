<?php

declare(strict_types=1);

use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Prefix;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;
use Pin\Route\RouteDefinition;

enum Routes: string implements Routable
{
    use InteractsWithRoute;
    case Index = 'GET:/api/users';
    case Detail = 'GET:/api/users/{id}';
    case Create = 'POST:/api/users';
    case Update = 'PUT:/api/users/{id}';
    case Delete = 'DELETE:/api/users/{id}';
    case NameInValue = 'GET:/api/users/name|name.in.value';
    case NameInAttribute = 'GET:/api/users/name | name.in.attribute';
    case NoApiPrefix = 'GET:/users';
    case V1 = 'GET:/api/v1/users/';
    case NestedApi = 'GET:/api/users/api/tokens';
    case InternalApi = 'GET:/users/api/tokens';
    #[Name('attribute.name')]
    case AttributeName = 'GET:/attribute';
    #[Name('attribute.name')]
    case ExplicitName = 'GET:/explicit|explicit.name';
    case ZeroName = 'GET:/zero|0';
}

#[Prefix('/api/prefix/')]
enum PrefixedRoutes: string implements Routable
{
    use InteractsWithRoute;

    case Index = 'GET:/users/';
    case Root = 'GET:/';
}

it('resolves route definition into route metadata', function (
    Routable $route,
    string $method,
    string $uri,
    string $name
) {
    $route = new RouteDefinition($route);

    expect($route->method)->toBe($method)
        ->and($route->uri)->toBe($uri)
        ->and($route->name)->toBe($name);
})->with([
    [Routes::Index, 'GET', '/api/users', 'users'],
    [Routes::Detail, 'GET', '/api/users/{id}', 'users.detail'],
    [Routes::Create, 'POST', '/api/users', 'users.create'],
    [Routes::Update, 'PUT', '/api/users/{id}', 'users.update'],
    [Routes::Delete, 'DELETE', '/api/users/{id}', 'users.delete'],
    [Routes::NameInValue, 'GET', '/api/users/name', 'name.in.value'],
    [Routes::NameInAttribute, 'GET', '/api/users/name', 'name.in.attribute'],
    [Routes::NoApiPrefix, 'GET', '/users', 'users'],
    [Routes::V1, 'GET', '/api/v1/users', 'v1.users'],
    [Routes::NestedApi, 'GET', '/api/users/api/tokens', 'users.api.tokens'],
    [Routes::InternalApi, 'GET', '/users/api/tokens', 'users.api.tokens'],
    [Routes::AttributeName, 'GET', '/attribute', 'attribute.name'],
    [Routes::ExplicitName, 'GET', '/explicit', 'explicit.name'],
    [Routes::ZeroName, 'GET', '/zero', '0'],
    [PrefixedRoutes::Index, 'GET', '/api/prefix/users', 'prefix.users'],
    [PrefixedRoutes::Root, 'GET', '/api/prefix', 'prefix'],
]);
