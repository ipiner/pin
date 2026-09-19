<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException as EloquentModelNotFoundException;
use Illuminate\Support\Str;
use Override;
use Pin\Errors\Errors;
use Pin\Models\Model;

/**
 * 模型未找到异常。
 */
class ModelNotFoundException extends Exception
{
    /**
     * 包装模型查询异常。
     */
    public function __construct(protected readonly EloquentModelNotFoundException $e)
    {
        parent::__construct(
            Errors::ModelNotFound->message(['model' => $this->modelLabel($e->getModel())]),
            Errors::ModelNotFound->code(),
            $e
        );

        $this->withStatusCode(404)->withContext(['message' => $e->getMessage()]);
    }

    /**
     * 获取查询调用位置。
     */
    #[Override]
    public function getCaller(?string $file = null, ?int $line = null): array
    {
        $trace = $this->e->getTrace()[0] ?? [];

        return parent::getCaller(
            $file ?: ($trace['file'] ?? $this->e->getFile()),
            $line ?: ($trace['line'] ?? $this->e->getLine())
        );
    }

    /**
     * 获取模型名称。
     */
    protected function modelLabel(string $model): string
    {
        return is_subclass_of($model, Model::class)
            ? $model::metadata()->label
            : Str::headline(class_basename($model));
    }
}
