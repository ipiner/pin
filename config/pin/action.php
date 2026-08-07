<?php

return [
    /**
     * 是否启用 Action Fake Response。
     *
     * 开启后，当 Action 被容器解析时：
     *
     *  ```
     *  app(CreateUserAction::class)
     *  ```
     *
     * 若当前请求包含 `_fake=1`，将自动中断 Action 执行，
     * 并返回 `JsonResponse`，响应内容为 `$action::fake()`
     *
     * ```
     * {
     *     "username": "xxx",
     *     "email": "xxx@example.com"
     * }
     * ```
     */
    'fake_response_enabled' => env(
        'FAKE_RESPONSE_ENABLED',
        env('APP_ENV', 'production') !== 'production'
    ),
];
