<?php

declare(strict_types=1);

namespace Pin\Log;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;
use Monolog\LogRecord;
use Monolog\Utils;
use Override;
use Throwable;

/**
 * JSON 日志格式化器
 */
class JsonFormatter extends BaseJsonFormatter
{
    public function __construct(
        string $dateFormat = 'Y-m-d H:i:s',
        ?int $addJsonEncodeOption = null
    ) {
        parent::__construct();
        $this->dateFormat = $dateFormat;

        if (config('pin.logging.json_pretty_print')) {
            $this->setJsonPrettyPrint(true);
        }

        if ($addJsonEncodeOption !== null) {
            $this->addJsonEncodeOption($addJsonEncodeOption);
        }
    }

    /**
     * 标准化异常
     *
     * @return array<string, mixed>
     */
    #[Override]
    protected function normalizeException(Throwable $e, int $depth = 0): array
    {
        return $this->normalizeThrowable($e, $depth);
    }

    /**
     * 构建异常数据
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
            $data['context'] = $this->normalize($context, $depth + 1);
        }

        if (app(StackTracePolicy::class)->shouldInclude($e)) {
            $data['trace'] = app(StackTraceNormalizer::class)->normalize($e);
        }

        $previous = $e->getPrevious();

        if (! $previous) {
            return $data;
        }

        if ($depth >= $this->maxNormalizeDepth) {
            $data['previous'] = [
                'message' => sprintf(
                    'Over %d levels deep, aborting normalization',
                    $this->maxNormalizeDepth
                ),
            ];
        } else {
            $data['previous'] = $this->normalizeThrowable($previous, $depth + 1);
        }

        return $data;
    }

    /**
     * 标准化日志记录
     */
    #[Override]
    protected function normalizeRecord(LogRecord $record): array
    {
        $data = parent::normalizeRecord($record);
        $data = ['datetime' => $data['datetime']] + $data;
        $data['level_code'] = $data['level'];
        $data['level'] = $data['level_name'];
        unset($data['level_name']);

        return $data;
    }
}
