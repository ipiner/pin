<?php

declare(strict_types=1);

namespace Pin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Pin\Database\Schema\Compiler;
use Pin\Database\Schema\Table;
use RuntimeException;

/**
 * 生成数据库表结构元数据
 */
class TableSchemasGenerateCommand extends Command
{
    /**
     * 命令描述
     */
    protected $description = '生成数据库表结构 metadata 文件';

    /**
     * 命令签名
     */
    protected $signature = 'pin:generate:table-schemas
        {--connection=default : 数据库连接名称}
        {--force : 强制覆盖已存在的 schema 文件}';

    /**
     * 数据库连接名称
     */
    protected string $connection = 'default';

    /**
     * 生成表结构文件
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $this->connection = $this->option('connection') ?: 'default';

        $tables = app(Compiler::class, ['connection' => $this->connection])->compile();

        app('files')->ensureDirectoryExists(database_path('schemas/'.$this->connection));
        $this->saveMetadata($tables);

        foreach ($tables as $name => $table) {
            $schema = $this->renderTableSchema($name, $table->label);

            if ($file = $this->save($name, $schema, $force)) {
                $this->info('Written table file: '.$file);
            }
        }

        return self::SUCCESS;
    }

    /**
     * 保存表结构和字段标签汇总
     *
     * @param  Collection<string, Table>  $tables
     */
    protected function saveMetadata(Collection $tables): void
    {
        $schemas = $tables->map(fn (Table $table) => [
            ...$table->toArray(),
            'columns' => $table->columns()->toArray(),
        ])->all();
        $attributes = $tables->map(fn (Table $table) => $table->attributes())->all();

        $this->info('Written schemas: '.$this->save('__schemas__', $schemas, true));
        $this->info('Written attributes: '.$this->save('__attributes__', $attributes, true));
    }

    /**
     * 生成单表模板
     */
    protected function renderTableSchema(string $name, string $label): string
    {
        $name = var_export($name, true);
        $label = var_export($label, true);

        return <<<PHP
[
    'label' => {$label},
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')[{$name}],
        // 自定义扩展字段
    ],
]
PHP;
    }

    /**
     * 保存表结构文件
     */
    protected function save(string $name, array|string $data, bool $force): ?string
    {
        $file = database_path("schemas/{$this->connection}/{$name}.php");

        if (! $force && is_file($file)) {
            return null;
        }

        $data = is_string($data) ? $data : var_export($data, true);

        if (! app('files')->put($file, "<?php\n\nreturn {$data};\n")) {
            throw new RuntimeException('Unable to write schema file: '.$file);
        }

        return $file;
    }
}
