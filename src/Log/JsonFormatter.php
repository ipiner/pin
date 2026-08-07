<?php

declare(strict_types=1);

namespace Pin\Log;

use Monolog\Utils;
use Override;
use Throwable;

/**
 * JSON 日志格式化器
 *
 * 基于 Monolog\JsonFormatter 扩展，用于生成统一、结构化、可观测的 JSON 日志。
 */
class JsonFormatter extends \Monolog\Formatter\JsonFormatter
{
    public function __construct(string $dateFormat = 'Y-m-d H:i:s', ?int $addJsonEncodeOption = null)
    {
        parent::__construct();
        $this->dateFormat = $dateFormat;

        // 使用自定义 trace normalizer
        $this->includeStacktraces = false;

        if (config('pin.logging.json_pretty_print')) {
            $this->setJsonPrettyPrint(true);
        }

        if ($addJsonEncodeOption !== null) {
            $this->addJsonEncodeOption($addJsonEncodeOption);
        }
    }

    /**
     * 标准化异常对象。
     *
     * @return array<string, mixed>
     */
    #[Override]
    protected function normalizeException(Throwable $e, int $depth = 0): array
    {
        return $this->normalizeThrowable($e, $depth);
    }

    /**
     * 标准化 Throwable
     *
     * @return array<string, mixed>
     */
    protected function normalizeThrowable(Throwable $e, int $depth): array
    {
        $data = [
            'class' => Utils::getClass($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        if (method_exists($e, 'getContext') && ($context = $e->getContext())) {
            $data['context'] = $context;
        }

        if (app(StackTracePolicy::class)->shouldInclude($e)) {
            $data['trace'] = app(StackTraceNormalizer::class)->normalize($e);
        }

        $previous = $e->getPrevious();
        if (! $previous) {
            return $data;
        }

        if ($depth > $this->maxNormalizeDepth) {
            $data['previous'] = [
                'message' => 'Over '.$this->maxNormalizeDepth.' levels deep, aborting normalization',
            ];
        } else {
            $data['previous'] = $this->normalizeThrowable($previous, $depth + 1);
        }

        return $data;
    }

    /**
     * 将日志数据编码为 JSON。
     */
    #[Override]
    protected function toJson($data, bool $ignoreErrors = false): string
    {
        // 将 datetime 提前到 JSON 开头
        $data = ['datetime' => $data['datetime']] + $data;

        // level & level_code
        $level = $data['level'];
        $data['level'] = $data['level_name'];
        $data['level_code'] = $level;
        unset($data['level_name']);

        return parent::toJson($data, $ignoreErrors);
    }
}
