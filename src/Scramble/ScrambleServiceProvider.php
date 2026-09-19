<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Pin\Support\ServiceProvider;

/**
 * OpenAPI 文档服务提供者
 */
class ScrambleServiceProvider extends ServiceProvider
{
    /**
     * 配置文档认证
     */
    public function boot(): void
    {
        if (! class_exists(Scramble::class)) {
            return;
        }

        Scramble::configure()->withDocumentTransformers(static function (OpenApi $openApi) {
            $openApi->secure(SecurityScheme::http('bearer')->as('bearer'));
        });
    }
}
