<?php

declare(strict_types=1);

namespace Pin\Support\Traits;

use Illuminate\Support\Facades\Validator as ValidatorFactory;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

/**
 * Validation 交互能力
 */
trait HasValidation
{
    use HasPayload;

    /**
     * 已验证的数据
     */
    protected ?array $validated;

    /**
     * 验证规则
     *
     * @var array<string, string|array>
     */
    protected array $rules;

    /**
     * 获取已验证数据
     */
    public function validated(): array
    {
        return $this->validated ?? $this->validate();
    }

    /**
     * 设置验证规则
     *
     * @param  array<string, string|array>  $rules
     * @return $this
     */
    public function withRules(array $rules): static
    {
        $this->validated = null;
        $this->rules = $rules;

        return $this;
    }

    /**
     * 权限校验失败后的处理
     *
     * 默认抛出 UnauthorizedException。
     *
     * @throws UnauthorizedException
     */
    protected function failedAuthorization(): void
    {
        throw new UnauthorizedException();
    }

    /**
     * 验证失败后的处理
     *
     * @throws ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        $exception = $validator->getException();

        throw new $exception($validator);
    }

    /**
     * 验证通过后的处理
     */
    protected function passedValidation(Validator $validator): void
    {
        $this->validated = $validator->validated();
    }

    /**
     * 执行权限校验
     */
    protected function passesAuthorization(): bool
    {
        return method_exists($this, 'authorize') ? $this->authorize() : true;
    }

    /**
     * 执行数据验证
     *
     * @return array 验证后的数据
     *
     * @throws ValidationException
     * @throws UnauthorizedException
     */
    protected function validate(): array
    {
        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $validator = $this->validator();
        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $this->passedValidation($validator);

        return $this->validated;
    }

    /**
     * 获取自定义验证 attributes
     */
    protected function validationAttributes(): array
    {
        return method_exists($this, 'attributes') ? $this->attributes() : [];
    }

    /**
     * 获取自定义验证消息
     */
    protected function validationMessages(): array
    {
        return method_exists($this, 'messages') ? $this->messages() : [];
    }

    /**
     * 获取验证规则
     */
    protected function validationRules(): array
    {
        return $this->rules ??= method_exists($this, 'rules') ? $this->rules() : [];
    }

    /**
     * 创建 Validator 实例
     */
    protected function validator(): Validator
    {
        return ValidatorFactory::make(
            $this->payload(),
            $this->validationRules(),
            $this->validationMessages(),
            $this->validationAttributes(),
        );
    }
}
