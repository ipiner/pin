<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Database\Eloquent\Builder;
use Override;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Pin\Models\Model;
use Pin\Services\ModelService as BaseModelService;

/**
 * 树节点服务
 *
 * @template TModel of Model
 *
 * @extends BaseModelService<TModel>
 */
class ModelService extends BaseModelService
{
    /**
     * 资源名称
     */
    public string $resourceName;

    /**
     * 创建有序查询
     *
     * @return Builder<TModel>
     */
    #[Override]
    protected function queryBuilder(): Builder
    {
        return $this->modelClass::orderedQuery()->queryable($this->queryable);
    }

    /**
     * 校验节点能否删除
     *
     * @param  TModel  $model
     *
     * @throws Exception
     */
    #[Override]
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
