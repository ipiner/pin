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
     * 将 JSON 字符串解码为数组或对象
     *
     * @param  string  $data  要解码的 JSON 字符串
     * @param  bool  $returnArray  是否返回数组，默认 true；false 返回对象
     * @return mixed 返回解码后的数组或对象
     *
     * @throws Exception JSON 解码失败时抛出自定义异常
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
     * 将数据编码为 JSON 字符串
     *
     * @param  mixed  $data  要编码的数据
     * @param  int  $options  JSON 编码选项
     * @return string 返回 JSON 字符串
     *
     * @throws Exception JSON 编码失败时抛出自定义异常
     */
    public static function encode(mixed $data, int $options = self::DEFAULT_ENCODE_OPTIONS): string
    {
        try {
            return json_encode($data, $options);
        } catch (JsonException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e)
                ->withContext(['data' => $data]);
        }
    }
}
