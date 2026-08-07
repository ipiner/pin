<?php

use Pin\Support\Str;

return [
    /**
     * API 文档调试来源标识
     *
     * 用于识别请求是否来自 API 调试工具或接口文档平台
     *
     * 通过请求头 X-Api-Document / Referer 判断
     */
    'x_api_document' => [
        /**
         * 是否启用
         */
        'enabled' => env('APP_ENV') !== 'production',
        /**
         * 允许的值
         */
        'allows' => Str::explode(
            env('X_API_DOCUMENT_ALLOWS', 'Apifox,Scramble,docs/api')
        ),
    ],
];
