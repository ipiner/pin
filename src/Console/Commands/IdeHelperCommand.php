<?php

declare(strict_types=1);

namespace Pin\Console\Commands;

use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class IdeHelperCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成 IDE Helper 提示文件（models、meta、eloquent 等）';

    /**
     * The console command signature
     *
     * @var string
     */
    protected $signature = 'pin:ide-helper';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('ide-helper:eloquent');
        $this->call('ide-helper:generate');
        $this->call('ide-helper:meta');
        $this->call('ide-helper:models', ['--nowrite' => true, '--write-mixin' => true]);
    }
}
