<?php

declare(strict_types=1);

dataset('descriptions', ['user', 'category']);

it('creates', function (string $name) {
    expect(creates($name))->toBe("creates {$name} successfully");
})->with('descriptions');

it('fails to create', function (string $name) {
    expect(failsToCreate($name))->toBe("fails to create {$name}");
})->with('descriptions');

it('updates', function (string $name) {
    expect(updates($name))->toBe("updates {$name} successfully");
})->with('descriptions');

it('fails to update', function (string $name) {
    expect(failsToUpdate($name))->toBe("fails to update {$name}");
})->with('descriptions');

it('deletes', function (string $name) {
    expect(deletes($name))->toBe("deletes {$name} successfully");
})->with('descriptions');

it('fails to delete', function (string $name) {
    expect(failsToDelete($name))->toBe("fails to delete {$name}");
})->with('descriptions');

it('lists resources', function (string $name, string $value) {
    expect(lists($name))->toBe("lists {$value} successfully");
})->with([
    'user' => ['user', 'users'],
    'category' => ['category', 'categories'],
]);

it('validates create payload', function (string $name) {
    expect(validatesCreatePayload($name))->toBe("validates payload for updating {$name}");
})->with('descriptions');

it('validates create required', function (string $name) {
    expect(validatesCreateRequired($name))->toBe(
        "validates required fields for creating {$name}"
    );
})->with('descriptions');

it('validates update payload', function (string $name) {
    expect(validatesUpdatePayload($name))->toBe("validates payload for updating {$name}");
})->with('descriptions');

it('validates update required', function (string $name) {
    expect(validatesUpdateRequired($name))->toBe(
        "validates required fields for updating {$name}"
    );
})->with('descriptions');

it('ensures unique', function (string $name, string $field) {
    expect(ensuresUnique($name, $field))->toBe(
        "ensures {$name}'s {$field} is unique"
    );
})->with([
    'user' => ['user', 'username'],
    'category' => ['category', 'name'],
]);

it('run tests automatically', function (string $name) {
    expect(runsTestsAutomatically($name))->toBe(
        "runs {$name}'s tests automatically"
    );
})->with('descriptions');
