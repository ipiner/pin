<?php

namespace Pin\Tests;

trait InteractsWithDatabase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom([
            '--path' => [
                __DIR__.'/../database/migrations',
                __DIR__.'/laravel/database/migrations',
            ],
        ]);
    }
}
