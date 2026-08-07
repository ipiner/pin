<?php

declare(strict_types=1);

namespace Pin\Scramble;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Pin\Support\ServiceProvider;

/**
 * OpenAPI 文档服务提供者
 *
 * 为 Scramble 文档补充认证配置。
 */
class ScrambleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if (! class_exists(Scramble::class)) {
            return;
        }

        Scramble::configure()->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->components->securitySchemes['bearer'] = SecurityScheme::http('bearer');
            $openApi->security[] = new SecurityRequirement([
                'bearer' => [],
            ]);
        });
    }
}
