<?php

declare(strict_types=1);

namespace Pin\Log;

use Throwable;

/**
 * 异常堆栈格式化
 */
class StackTraceNormalizer
{
    /**
     * 格式化异常堆栈
     *
     * @return list<string>
     */
    public function normalize(Throwable $e): array
    {
        $maxFrames = (int) config('pin.logging.stack_trace.max_frames', 10);

        if ($maxFrames <= 0) {
            return [];
        }

        $frames = [];
        $trace = $e->getTrace();
        $total = count($trace);

        foreach ($trace as $index => $frame) {
            if (count($frames) >= $maxFrames) {
                break;
            }

            if ($this->isExcludedFrame($frame) || ! $this->isIncludedFrame($frame)) {
                continue;
            }

            $frames[] = $this->formatFrame($index, $total, $frame);
        }

        return $frames;
    }

    /**
     * 格式化堆栈帧
     */
    protected function formatFrame(int $index, int $total, array $frame): string
    {
        return sprintf(
            '#%d/%d %s:%s %s%s%s',
            $index,
            $total,
            $frame['file'] ?? '[internal]',
            $frame['line'] ?? '?',
            $frame['class'] ?? '',
            $frame['type'] ?? '',
            $frame['function'] ?? '',
        );
    }

    /**
     * 是否排除堆栈帧
     */
    protected function isExcludedFrame(array $frame): bool
    {
        return array_any(
            config('pin.logging.stack_trace.exclude_frames', []),
            fn ($term) => $this->matchFrame($frame, $term)
        );
    }

    /**
     * 是否保留堆栈帧
     */
    protected function isIncludedFrame(array $frame): bool
    {
        $includes = config('pin.logging.stack_trace.include_frames', []);

        if (! $includes) {
            return true;
        }

        return array_any(
            $includes,
            fn ($term) => $this->matchFrame($frame, $term)
        );
    }

    /**
     * 匹配堆栈帧
     */
    protected function matchFrame(array $frame, string $term): bool
    {
        $text = implode(' ', array_filter([
            $frame['file'] ?? null,
            $frame['class'] ?? null,
            $frame['function'] ?? null,
        ]));

        if (str_starts_with($term, '#')) {
            return preg_match($term, $text) === 1;
        }

        return str_contains($text, $term);
    }
}
