<?php

declare(strict_types=1);

namespace Pin\Tests;

abstract class TestCase extends \Pin\Testing\TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::copyConfigFile('config.testing.php');
    }

    protected static function copyConfigFile(string $file)
    {
        if (! is_file($path = static::applicationBasePath().'/config/'.$file)) {
            stream_copy_to_stream(
                fopen(__DIR__.'/laravel/config/'.$file, 'rb'),
                fopen($path, 'wb')
            );
        }
    }
}
