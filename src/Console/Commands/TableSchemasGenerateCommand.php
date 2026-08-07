<?php

declare(strict_types=1);

namespace Pin\Console\Commands;

use Illuminate\Console\Command;
use Pin\Database\Schema\Compiler;
use Pin\Database\Schema\Table;
use Pin\Support\Json;

/**
 * 根据数据库结构生成 schema metadata 文件
 */
class TableSchemasGenerateCommand extends Command
{
    /**
     * 控制台命令描述
     */
    protected $description = '生成数据库表结构 metadata 文件';

    /**
     * Artisan 命令签名
     */
    protected $signature = 'pin:generate:table-schemas 
        {--connection=default : 数据库连接名称}
        {--force : 强制覆盖已存在的 schema 文件}
    }';

    /**
     * 当前数据库连接名称
     */
    protected string $connection = 'default';

    /**
     * 命令入口
     */
    public function handle(): void
    {
        $force = (bool) $this->option('force');
        $this->connection = $this->option('connection') ?: 'default';

        // 创建输出目录（不存在则创建）
        app('files')->makeDirectory(
            database_path('schemas/'.$this->connection),
            0755,
            true,
            true
        );

        // 编译数据库表结构
        $compiler = new Compiler($this->connection);
        $tables = $compiler->compile();

        // 转换为数组结构（确保可序列化）
        $schemas = Json::decode(Json::encode($tables));

        // 提取字段 attributes
        $attributes = $tables
            ->map(fn (Table $table) => $table->attributes())
            ->toArray();

        // 写入汇总文件
        $this->info('Written schemas: '.$this->save('__schemas__', $schemas, true));
        $this->info('Written attributes: '.$this->save('__attributes__', $attributes, true));

        // 逐表生成 schema 文件
        foreach ($schemas as $name => $table) {
            $data = <<<PHP
[
    'label' => '{$table['label']}',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['{$name}'],
        // 自定义扩展字段（不会被覆盖）
    ],
]
PHP;
            if ($file = $this->save($name, $data, $force)) {
                $this->info('Written table file: '.$file);
            }
        }
    }

    /**
     * 保存 schema 文件
     *
     * @param  string  $name  文件名（不含路径，不含 .php）
     * @param  array|string  $data  写入内容（数组会自动转 var_export）
     * @param  bool  $force  是否强制覆盖已存在文件
     */
    protected function save(string $name, array|string $data, bool $force): ?string
    {
        $file = database_path("schemas/{$this->connection}/{$name}.php");

        // 非强制模式下，如果文件存在则跳过
        if (is_file($file) && ! $force) {
            return null;
        }

        // 统一转 PHP 可执行数组格式
        $data = is_string($data) ? $data : var_export($data, true);
        file_put_contents($file, "<?php\n\nreturn {$data};\n");

        return $file;
    }
}
