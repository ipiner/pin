<?php

declare(strict_types=1);

namespace Pin\Tree;

use Illuminate\Support\Facades\Request;
use Pin\Models\Model;
use Pin\Tree\Rules\TreeParentRule;
use Pin\Validation\Rules\Unique;

/**
 * InteractsWithTreeValidation
 *
 * Tree 数据写入校验层
 *
 * @template TModel of Model
 */
abstract class Action extends \Pin\Action\Action
{
    public function __construct(public protected(set) ModelService $service)
    {
    }

    /**
     * 获取 Tree 基础验证规则集合
     *
     * @return array<string, mixed>
     */
    protected function basicRules(?int $id = null, ?int $pid = null): array
    {
        $id ??= (int) $this->context->get('id');
        $pid ??= (int) Request::json('pid');

        return [
            /**
             * 名称
             */
            'name' => [
                'required',
                'string',
                /**
                 * 同级唯一性约束：
                 * 在同一 pid 下 name 必须唯一
                 */
                'unique' => new Unique($this->service->modelClass)
                    ->where('pid', $pid)
                    ->ignore($id),
            ],

            /**
             * 父id，`0` 表示一级
             */
            'pid' => [
                'required',
                'integer',
                'min:0',
                'fake:in,0',
                new TreeParentRule(new TreeGuard($this->service), $id),
            ],

            /**
             * 排序值， `-1` 时使用记录id
             *
             * @example -1
             */
            'sort' => 'required|integer|min:-1|fake:in,-1',
        ];
    }
}
