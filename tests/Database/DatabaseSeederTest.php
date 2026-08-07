<?php

declare(strict_types=1);

use Pin\Database\DatabaseSeeder;

it('discovers seeder classes from filesystem', function () {

    $seeder = new class extends DatabaseSeeder
    {
        public array $seeders;

        public function call($class, $silent = false, array $parameters = [])
        {
            $this->seeders = $class;

            return $this;
        }
    };

    expect($seeder->run()->seeders)->toBeEmpty();

    $seeders = $this->invoker($seeder)->seeders(__DIR__.'/seeders');
    expect($seeders)->toContain(
        'Database\\Seeders\\UserSeeder',
        'Database\\Seeders\\Content\\ArticleSeeder'
    )->not->toContain('Database\\Seeders\\Exclude');
});
