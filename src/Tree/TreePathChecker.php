<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Collection;
use Pin\Models\Model;

/**
 * 树路径校验器
 */
class TreePathChecker
{
    /**
     * 校验树结构完整性
     *
     * @param  Collection<array-key, Model>  $models
     * @return list<array{id: int, rule: string, message: string}>
     */
    public function check(Collection $models): array
    {
        $errors = [];

        $idMap = $this->buildIdMap($models);

        foreach ($models as $model) {
            foreach ($this->checkModel($model, $idMap) as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * 按节点 ID 建立索引
     *
     * @param  Collection<array-key, Model>  $models
     * @return Collection<int, Model>
     */
    protected function buildIdMap(Collection $models): Collection
    {
        return $models->keyBy('id');
    }

    /**
     * 校验 level 与 paths 长度一致
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
     * 校验单个节点结构
     *
     * @param  Collection<int, Model>  $idMap
     * @return list<array{id: int, rule: string, message: string}>
     */
    protected function checkModel(Model $model, Collection $idMap): array
    {
        $errors = [];

        $paths = $model->paths ?? [];
        $pid = $model->pid;
        $id = $model->id;

        if ($error = $this->checkPathsNotEmpty($id, $paths)) {
            return [$error];
        }

        if ($error = $this->checkLevelConsistency($id, $model->level, $paths)) {
            $errors[] = $error;
        }

        if ($error = $this->checkSelfReference($id, $paths)) {
            $errors[] = $error;
        }

        if ($pid == 0) {
            if ($error = $this->checkRootNode($id, $paths)) {
                $errors[] = $error;
            }

            return $errors;
        }

        $parent = $idMap->get($pid);

        if ($error = $this->checkParentExists($id, $pid, $parent)) {
            $errors[] = $error;

            return $errors;
        }

        if ($error = $this->checkParentPathConsistency($id, $parent->paths ?? [], $paths)) {
            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * 校验父节点存在
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
        $expected = [...$parentPaths, $id];

        if ($expected !== $paths) {
            return $this->error(
                $id,
                'path_mismatch',
                sprintf('expect=%s got=%s', json_encode($expected), json_encode($paths))
            );
        }

        return null;
    }

    /**
     * 校验 paths 不为空
     */
    protected function checkPathsNotEmpty(int $id, array $paths): ?array
    {
        if ($paths === []) {
            return $this->error($id, 'paths_empty', 'paths empty');
        }

        return null;
    }

    /**
     * 校验根节点结构
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
     * 校验 paths 最后一位必须是自身 id
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
     * 构建统一错误结构
     *
     * @return array{id: int, rule: string, message: string}
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
