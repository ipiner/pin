<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Database\Eloquent\Builder;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Pin\Models\Model;

/**
 * 树结构模型增删改查服务
 *
 * @template TModel of Model
 *
 * @extends \Pin\Services\ModelService<TModel>
 */
class ModelService extends \Pin\Services\ModelService
{
    /**
     * 资源名称
     */
    public string $resourceName;

    /**
     * 创建查询构建器
     */
    protected function queryBuilder(): Builder
    {
        return $this->modelClass::orderedQuery()->queryable($this->queryable);
    }

    /**
     * 删除前置检查
     *
     * @throws Exception
     */
    protected function deleting($model): void
    {
        parent::deleting($model);

        if ($this->modelClass::findBy('pid', $model->id)) {
            Errors::DeleteFailed->throw(
                "请先删除该{$this->resourceName}下的子{$this->resourceName}"
            );
        }
    }
}
