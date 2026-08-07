<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Pin\Models\Model;
use Pin\Support\Facades\Tree;

it('filters out middle node and its descendants', function () {
    $tree = collect([
        makeNode(1, 0, [1]),
        makeNode(2, 1, [1, 2]),
        makeNode(3, 2, [1, 2, 3]),
        makeNode(4, 2, [1, 2, 4]),
        makeNode(5, 1, [1, 5]),
    ]);

    $res = Tree::filter($tree, fn ($m) => $m->id != 2);

    expect($res->pluck('id')->all())->toEqual([1, 5]);
});

it('keeps all nodes if filter always returns true', function () {
    $tree = makeTree();
    $res = Tree::filter($tree, fn ($m) => true);
    expect($res)->toHaveCount(4);
});

it('removes all nodes if root node is hidden', function () {
    $tree = makeTree();
    $res = Tree::filter($tree, fn ($m) => $m->id != 1);
    expect($res)->toHaveCount(0);
});

it('removes children then parent is removed if no remaining children', function () {
    $tree = makeTree();
    $res = Tree::filter($tree, fn ($m) => $m->id == 1);
    expect($res->pluck('id')->all())->toEqual([]);
});

it('removes leaf node but keeps parent if other children exist', function () {
    $tree = makeTree();
    $res = Tree::filter($tree, fn ($m) => $m->id != 3);
    expect($res->pluck('id')->all())->toEqual([1, 4]);
});

it('removes node and all descendants', function () {
    $tree = makeTree();
    $res = Tree::filter($tree, fn ($m) => $m->id != 2);
    expect($res->pluck('id')->all())->toEqual([1, 4]);
});

/**
 * 构造测试数据
 */
function makeTree(): Collection
{
    return collect([
        makeNode(1, 0, [1]),
        makeNode(2, 1, [1, 2]),
        makeNode(3, 2, [1, 2, 3]),
        makeNode(4, 1, [1, 4]),
    ]);
}

function makeNode(int $id, int $pid, array $paths): Model
{
    $m = new Model();
    $m->id = $id;
    $m->pid = $pid;
    $m->paths = $paths;

    return $m;
}
