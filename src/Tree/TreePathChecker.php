<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;
use Pin\Models\Model;

/**
 * TreeStructureChecker
 *
 * Tree 结构完整性校验器。
 *
 * 用于校验 Materialized Path（路径枚举）树结构是否合法。
 */
class TreePathChecker
{
    /**
     * 校验树结构完整性。
     *
     * @param  Collection<Model>  $models
     * @return array<int, array{
     *     id:int,
     *     rule:string,
     *     message:string
     * }>
     */
    public function check(Collection $models): array
    {
        $errors = [];

        /** @var Collection<int, Model> $idMap */
        $idMap = $this->buildIdMap($models);

        foreach ($models as $model) {
            $errors = [
                ...$errors,
                ...$this->checkModel($model, $idMap),
            ];
        }

        return $errors;
    }

    /**
     * 构建 id => model 映射。
     *
     * @param  Collection<Model>  $models
     * @return Collection<int, Model>
     */
    protected function buildIdMap(Collection $models): Collection
    {
        return $models->keyBy('id');
    }

    /**
     * 校验 level 与 paths 长度一致。
     */
    protected function checkLevelConsistency(
        int $id,
        int $level,
        array $paths
    ): ?array {
        if ($level !== count($paths)) {
            return $this->error(
                $id,
                'invalid_level',
                sprintf('level [%d] not equal paths length [%d]', $level, count($paths))
            );
        }

        return null;
    }

    /**
     * 校验单个节点结构。
     *
     * @param  Collection<int, Model>  $idMap
     * @return array<int, array{
     *     id:int,
     *     rule:string,
     *     message:string
     * }>
     */
    protected function checkModel(Model $model, Collection $idMap): array
    {
        $errors = [];

        $paths = $model->paths ?? [];
        $level = $model->level;
        $pid = $model->pid;
        $id = $model->id;

        // paths 不能为空
        if ($err = $this->checkPathsNotEmpty($id, $paths)) {
            $errors[] = $err;

            return $errors;
        }

        // level 必须等于 paths 长度
        if ($err = $this->checkLevelConsistency($id, $level, $paths)) {
            $errors[] = $err;
        }

        // paths 最后一位必须是自身 id
        if ($err = $this->checkSelfReference($id, $paths)) {
            $errors[] = $err;
        }

        // 根节点校验
        if ($pid == 0) {
            if ($err = $this->checkRootNode($id, $paths)) {
                $errors[] = $err;
            }

            return $errors;
        }

        // 父节点必须存在
        $parent = $idMap->get($pid);
        if ($err = $this->checkParentExists($id, $pid, $parent)) {
            $errors[] = $err;

            return $errors;
        }

        // paths 必须等于：父 paths + 当前 id
        if ($err = $this->checkParentPathConsistency($id, $parent->paths ?? [], $paths)) {
            $errors[] = $err;
        }

        return $errors;
    }

    /**
     * 校验父节点存在。
     */
    protected function checkParentExists(int $id, int $pid, ?Model $parent): ?array
    {
        if (! $parent) {
            return $this->error(
                $id,
                'parent_not_found',
                sprintf('parent [%d] not exist', $pid)
            );
        }

        return null;
    }

    /**
     * 校验父子路径一致性
     */
    protected function checkParentPathConsistency(
        int $id,
        array $parentPaths,
        array $paths
    ): ?array {
        $expect = [...$parentPaths, $id];

        if ($expect !== $paths) {
            return $this->error(
                $id,
                'path_mismatch',
                sprintf('expect=%s got=%s', json_encode($expect), json_encode($paths))
            );
        }

        return null;
    }

    /**
     * 校验 paths 不为空。
     */
    protected function checkPathsNotEmpty(int $id, array $paths): ?array
    {
        if ($paths === []) {
            return $this->error($id, 'paths_empty', 'paths empty');
        }

        return null;
    }

    /**
     * 校验根节点结构。
     *
     * 根节点必须：
     * - pid = 0
     * - paths 长度 = 1
     */
    protected function checkRootNode(int $id, array $paths): ?array
    {
        if (count($paths) !== 1) {
            return $this->error(
                $id,
                'invalid_root_node',
                sprintf('root node paths invalid: %s', json_encode($paths))
            );
        }

        return null;
    }

    /**
     * 校验 paths 最后一位必须是自身 id。
     */
    protected function checkSelfReference(int $id, array $paths): ?array
    {
        $last = end($paths);

        if ($last !== $id) {
            return $this->error(
                $id,
                'invalid_self_reference',
                sprintf('paths last segment [%s] not self id [%d]', json_encode($last), $id)
            );
        }

        return null;
    }

    /**
     * 构建统一错误结构。
     *
     * @return array{
     *     id:int,
     *     rule:string,
     *     message:string
     * }
     */
    protected function error(int $id, string $rule, string $message): array
    {
        return [
            'id' => $id,
            'rule' => $rule,
            'message' => $message,
        ];
    }
}
