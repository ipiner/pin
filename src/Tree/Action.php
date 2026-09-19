<?php

declare(strict_types=1);

namespace Pin\Tree;

use Pin\Action\Action as BaseAction;
use Pin\Models\Model;
use Pin\Tree\Rules\TreeParentRule;
use Pin\Validation\Rules\Unique;

/**
 * 树节点写入校验。
 *
 * @template TModel of Model
 *
 * @extends BaseAction<TModel>
 */
abstract class Action extends BaseAction
{
    /**
     * @param  ModelService<TModel>  $service
     */
    public function __construct(public protected(set) ModelService $service)
    {
    }

    /**
     * 获取基础验证规则。
     *
     * @return array<string, mixed>
     */
    protected function basicRules(?int $id = null, ?int $pid = null): array
    {
        $id ??= (int) $this->context('id');
        $pid ??= (int) $this->payload('pid');

        return [
            /**
             * 名称
             */
            'name' => [
                'required',
                'string',
                'unique' => new Unique($this->service->modelClass)
                    ->where('pid', $pid)
                    ->ignore($id),
            ],

            /**
             * 父节点 ID，0 表示根节点。
             */
            'pid' => [
                'required',
                'integer',
                'min:0',
                'fake:in,0',
                new TreeParentRule(new TreeGuard($this->service), $id),
            ],

            /**
             * 排序值，-1 使用节点 ID。
             *
             * @example -1
             */
            'sort' => 'required|integer|min:-1|fake:in,-1',
        ];
    }
}
