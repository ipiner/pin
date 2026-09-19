<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Psr\Log\LogLevel;
use Throwable;

/**
 * 业务异常。
 */
class Exception extends \Exception
{
    /**
     * 是否记录日志，null 使用默认规则。
     */
    public ?bool $report = null;

    /**
     * HTTP 状态码。
     */
    protected int $statusCode = 500;

    /**
     * 响应头。
     */
    protected array $headers = [];

    /**
     * 日志上下文。
     */
    protected array $context = [];

    /**
     * 日志级别。
     */
    protected string $logLevel = LogLevel::ERROR;

    /**
     * 响应消息。
     */
    protected ?string $responseMessage = null;

    /**
     * 创建业务异常。
     */
    public function __construct(
        string|IError $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
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
     * 获取异常位置。
     *
     * @return array{file: string, line: int}
     */
    public function getCaller(?string $file = null, ?int $line = null): array
    {
        return [
            'file' => $file ?: $this->getFile(),
            'line' => $line ?: $this->getLine(),
        ];
    }

    /**
     * 获取日志上下文。
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * 获取响应头。
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 获取日志级别。
     */
    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    /**
     * 是否记录日志，默认仅记录 HTTP 500 异常。
     */
    public function getReport(): bool
    {
        return $this->report ?? $this->statusCode === 500;
    }

    /**
     * 获取响应消息。
     */
    public function getResponseMessage(): ?string
    {
        return $this->responseMessage;
    }

    /**
     * 获取 HTTP 状态码。
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 设置日志上下文。
     */
    public function withContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    /**
     * 设置响应头。
     */
    public function withHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * 设置日志级别。
     */
    public function withLogLevel(string $level): static
    {
        $this->logLevel = $level;

        return $this;
    }

    /**
     * 设置是否记录日志。
     */
    public function withReport(bool $report = true): static
    {
        $this->report = $report;

        return $this;
    }

    /**
     * 设置响应消息。
     */
    public function withResponseMessage(?string $message): static
    {
        $this->responseMessage = $message;

        return $this;
    }

    /**
     * 设置 HTTP 状态码。
     */
    public function withStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * 初始化异常。
     */
    protected function initialize(): void
    {
    }
}
