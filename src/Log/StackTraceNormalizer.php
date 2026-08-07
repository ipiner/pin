<?php

declare(strict_types=1);

namespace Pin\Log;

use Throwable;

/**
 * StackTraceNormalizer
 *
 * 用于将 Throwable 的 stack trace 转换为统一、可读的结构。
 */
class StackTraceNormalizer
{
    /**
     * 标准化异常 trace
     */
    public function normalize(Throwable $e): array
    {
        $frames = [];
        $maxFrames = config('pin.logging.stack_trace.max_frames');
        $traces = $e->getTrace();
        $total = count($traces);

        foreach ($traces as $index => $frame) {
            if (count($frames) === $maxFrames) {
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
     * 格式化单个 trace frame。
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
     * 判断指定 frame 是否属于排除列表
     */
    protected function isExcludedFrame(array $frame): bool
    {
        return array_any(
            config('pin.logging.stack_trace.exclude_frames'),
            fn ($term) => $this->matchFrame($frame, $term)
        );
    }

    /**
     * 判断指定 frame 是否允许保留
     */
    protected function isIncludedFrame(array $frame): bool
    {
        $includes = config('pin.logging.stack_trace.include_frames');

        // 空白名单：默认允许全部 frame
        if ($includes === []) {
            return true;
        }

        return array_any(
            $includes,
            fn ($term) => $this->matchFrame($frame, $term)
        );
    }

    /**
     * 判断 frame 是否匹配指定规则
     */
    protected function matchFrame(array $frame, string $term): bool
    {
        $s = implode(' ', array_filter([
            $frame['file'] ?? null,
            $frame['class'] ?? null,
            $frame['function'] ?? null,
        ]));

        // 正则规则（以 # 开头）
        if (str_starts_with($term, '#')) {
            return (bool) preg_match($term, $s);
        }

        // 普通字符串包含匹配
        return str_contains($s, $term);
    }
}
