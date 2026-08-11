<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Psr\Log\LogLevel;
use Throwable;

/**
 * 统一业务异常
 *
 * 基于标准 Exception 扩展的业务异常模型：
 * - 支持 IError 枚举驱动
 * - 支持 HTTP 状态码
 * - 支持结构化日志上下文
 * - 支持 API 错误输出信息分离
 *
 * 作为系统业务异常的基础载体
 */
class Exception extends \Exception
{
    /**
     * 是否记录日志
     *
     * null 表示使用默认规则：
     *  - statusCode >= 500 时记录
     *  - 其余情况默认不记录
     */
    public ?bool $report = null;

    /**
     * HTTP 状态码
     */
    protected int $statusCode = 500;

    /**
     * 响应头
     */
    protected array $headers = [];

    /**
     * 日志上下文
     */
    protected array $context = [];

    /**
     * 日志级别
     */
    protected string $logLevel = LogLevel::ERROR;

    /**
     * 用户可见错误信息
     */
    protected ?string $responseMessage = null;

    /**
     * 创建业务异常
     *
     * 支持 IError 自动映射
     */
    public function __construct(string|IError $message = '', int $code = 0, ?Throwable $previous = null)
    {
        if ($message instanceof IError) {
            $this->withStatusCode($message->statusCode());
            $code = $message->code();
            $message = $message->message();
        }

        parent::__construct(
            $message ?: Errors::ServerError->message(),
            $code,
            $previous
        );

        $this->initialize();
    }

    /**
     * 异常发生位置
     *
     * @return array{file:string,line:int}
     */
    public function getCaller(?string $file = null, ?int $line = null): array
    {
        return [
            'file' => $file ?: $this->getFile(),
            'line' => $line ?: $this->getLine(),
        ];
    }

    /**
     * 获取日志上下文
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * 获取响应头
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 获取日志级别
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * 获取是否记录日志
     *
     * 默认规则：
     * - 未显式设置时：statusCode === 500 才记录
     */
    public function getReport(): bool
    {
        return $this->report ?? $this->statusCode === 500;
    }

    /**
     * 获取用户可见错误信息
     */
    public function getResponseMessage(): ?string
    {
        return $this->responseMessage;
    }

    /**
     * 获取 HTTP 状态码
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 设置异常上下文
     */
    public function withContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * 设置响应头
     */
    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * 设置日志级别
     */
    public function withLogLevel(string $level): static
    {
        $this->logLevel = $level;

        return $this;
    }

    /**
     * 设置是否记录日志
     */
    public function withReport(bool $report = true): static
    {
        $this->report = $report;

        return $this;
    }

    /**
     * 设置响应消息
     */
    public function withResponseMessage(?string $message): static
    {
        $this->responseMessage = $message;

        return $this;
    }

    /**
     * 设置 HTTP 状态码
     */
    public function withStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * 异常初始化
     */
    protected function initialize(): void
    {
        //
    }
}
