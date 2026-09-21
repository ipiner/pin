<?php

declare(strict_types=1);

namespace Pin\Console\Commands;

use Illuminate\Console\Command;

/**
 * 生成 IDE Helper 提示文件
 */
class IdeHelperCommand extends Command
{
    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '生成 IDE Helper 提示文件（models、meta、eloquent 等）';

    /**
     * 命令签名
     *
     * @var string
     */
    protected $signature = 'pin:ide-helper';

    /**
     * 执行生成命令
     */
    public function handle(): int
    {
        $commands = [
            'ide-helper:eloquent' => [],
            'ide-helper:generate' => [],
            'ide-helper:meta' => [],
            'ide-helper:models' => ['--nowrite' => true, '--write-mixin' => true],
        ];

        foreach ($commands as $command => $arguments) {
            if ($status = $this->call($command, $arguments)) {
                return $status;
            }
        }

        return self::SUCCESS;
    }
}
