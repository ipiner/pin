<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Override;
use Pin\Support\ServiceProvider;

/**
 * ID 生成器服务提供者
 */
class IdGeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * 注册 ID 生成器
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(
            IdGenerator::Timestamp->name(),
            static fn () => new TimestampId(config('pin.id-generator.timestamp.start_timestamp'))
        );

        $this->app->singleton(
            IdGenerator::Redis->name(),
            static fn () => new RedisId(config('pin.id-generator.redis'))
        );

        $this->app->singleton(
            IdGenerator::Snowflake->name(),
            static fn () => new SnowflakeId(config('pin.id-generator.snowflake'))
        );
    }

    /**
     * 获取延迟加载的服务
     */
    #[Override]
    public function provides(): array
    {
        return [
            IdGenerator::Redis->name(),
            IdGenerator::Timestamp->name(),
            IdGenerator::Snowflake->name(),
        ];
    }
}
