<?php

declare(strict_types=1);

namespace Pin\Support;

use JsonException;
use Pin\Exceptions\Exception;

/**
 * JSON 助手类
 */
class Json
{
    /**
     * 默认 JSON 编码选项
     */
    public const int DEFAULT_ENCODE_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    /**
     * 解码 JSON。
     *
     * @throws Exception
     */
    public static function decode(string $data, bool $returnArray = true): mixed
    {
        try {
            return json_decode($data, $returnArray, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e)
                ->withContext(['data' => $data]);
        }
    }

    /**
     * 编码 JSON。
     *
     * @throws Exception
     */
    public static function encode(mixed $data, int $options = self::DEFAULT_ENCODE_OPTIONS): string
    {
        try {
            return json_encode($data, $options | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e)
                ->withContext(['data' => $data]);
        }
    }
}
