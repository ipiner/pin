<?php

/**
 * 模型查询数据不存在异常
 */

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Support\Str;
use Override;
use Pin\Errors\Errors;
use Pin\Models\Model;

/**
 * 模型未找到异常适配器
 *
 * 将 Eloquent ModelNotFoundException 转换为统一业务异常：
 * - 标准化错误码
 * - 统一错误消息格式
 * - 增强模型可读性信息
 */
class ModelNotFoundException extends Exception
{
    /**
     * 包装 Laravel 模型未找到异常
     */
    public function __construct(protected readonly \Illuminate\Database\Eloquent\ModelNotFoundException $e)
    {
        parent::__construct(
            Errors::ModelNotFound->message(['model' => $this->modelLabel($e->getModel())]),
            Errors::ModelNotFound->code(),
            $e
        );
        $this->withStatusCode(404)->withContext(['message' => $e->getMessage()]);
    }

    /**
     * 映射到触发查询异常的位置
     */
    #[Override]
    public function getCaller(?string $file = null, ?int $line = null): array
    {
        $trace = $this->e->getTrace()[0];

        return parent::getCaller($trace['file'], $trace['line']);
    }

    /**
     * 模型名称
     */
    protected function modelLabel(string $model): string
    {
        return is_subclass_of($model, Model::class)
            ? $model::metadata()->label
            : Str::headline(class_basename($model));
    }
}
