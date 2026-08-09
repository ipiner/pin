<?php

declare(strict_types=1);

use App\Factories\MenuFactory;
use App\Models\Menu;
use App\Services\MenuService;
use Pin\Errors\Errors;
use Pin\Testing\Concerns\InteractsWithRedis;
use Pin\Tests\InteractsWithDatabase;
use Pin\Tree\Action;
use Pin\Tree\ModelService;
use Pin\Tree\TreeGuard;

uses(InteractsWithDatabase::class, InteractsWithRedis::class);

it('tests mutation', function () {
    $service = new MenuService();

    // create
    $item1 = $service->create(MenuFactory::new()->definition())->model;
    expect((string) $item1->id)->toBe($item1->path);
    expect($item1->sort)->toBe($item1->id);

    $item2 = $service->create([
        ...MenuFactory::new()->definition(),
        'pid' => $item1->id, 'sort' => 1]
    )->model;
    expect("{$item1->id}/{$item2->id}")->toBe($item2->path);
    expect($item2->sort)->toBe(1);

    $item3 = $service->create([
        ...MenuFactory::new()->definition(),
        'pid' => $item2->id]
    )->model;
    expect("{$item1->id}/{$item2->id}/{$item3->id}")->toBe($item3->path);

    // update
    // 二级 -> 一级
    $service->update($item2, [...MenuFactory::new()->definition(), 'pid' => 0], fn () => true);
    expect(Menu::find($item2->id)->path)->toBe("{$item2->id}");
    expect(Menu::find($item3->id)->path)->toBe("{$item2->id}/{$item3->id}");

    // 一级 -> 二级
    $item4 = $service->create(MenuFactory::new()->definition())->model;
    $service->update($item2, [...MenuFactory::new()->definition(), 'pid' => $item4->id]);
    expect(Menu::find($item2->id)->path)->toBe("{$item4->id}/{$item2->id}");
    expect(Menu::find($item3->id)->path)->toBe("{$item4->id}/{$item2->id}/{$item3->id}");

    // guard
    $guard = new TreeGuard($service);
    foreach ([
        '菜单不能互为子菜单' => [1, 1],
        '所属菜单不存在' => [1, -1],
        '菜单不能作为自己的子菜单' => [$item4->id, $item3->id],
        ' 菜单不能作为自己的子菜单 ' => [$item2->id, $item3->id],
    ] as $message => $args) {
        expect($guard->validatePid(...$args))->toBe(trim($message));
    }

    // rules
    $action = new class($service) extends Action
    {
    };
    $errors = [];
    $rules = $this->invoker($action)->basicRules(-1, -1);
    array_last($rules['name'])->validate(
        'a',
        'a',
        function ($result) use (&$errors) {
            $errors[] = $result;
        }
    );
    expect($errors)->toBeEmpty();

    array_last($rules['pid'])->validate(
        'a',
        -1,
        function ($result) use (&$errors) {
            $errors[] = $result;
        }
    );
    expect($errors)->not()->toBeEmpty();

    // delete
    expect($service->delete($item1)->deleted)->toBeTrue();
    expect($service->delete($item3)->deleted)->toBeTrue();

    $serviceFn = fn () => $service->delete($item4);
    expect($serviceFn)
        ->toThrow(Errors::DeleteFailed->exception(), '请先删除该菜单下的子菜单');
});

it('builds query sql as expected', function () {
    $service = (new ModelService())->withModel(Menu::class);

    $sql = $this->invoker($service)
        ->queryBuilder()
        ->toRawSql();

    expect($sql)->toBe(
        'select * from "menus" where "menus"."deleted_at" = 0 order by "level" asc, "sort" asc, "id" asc'
    );
});
