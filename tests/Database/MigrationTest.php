<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Facades\DB;
use Pin\Database\Migration;

pest()->beforeEach(function () {
    $this->migration = migration();
    $this->invoker = $this->invoker($this->migration);
});

dataset('migrations', [
    'deleted: adds soft delete column' => [
        'method' => 'deleted',
        'arguments' => [],
        'expected' => [
            "alter table `users` add `deleted_at` int unsigned not null default '0' comment '删除时间戳'",
        ],
    ],
    'blameable: adds blameable columns' => [
        'method' => 'blameable',
        'arguments' => [],
        'expected' => [
            "alter table `users` add `created_by` int unsigned not null default '0' comment '创建用户id'",
            "alter table `users` add `updated_by` int unsigned not null default '0' comment '更新用户id'",
        ],
    ],

    'id: adds auto increment int id column' => [
        'method' => 'id',
        'arguments' => [],
        'expected' => [
            "alter table `users` add `id` int unsigned not null auto_increment primary key comment 'id|自增'",
        ],
    ],

    'id: adds generated bigint id column' => [
        'method' => 'id',
        'arguments' => [false, true],
        'expected' => [
            "alter table `users` add `id` bigint unsigned not null comment 'id|由id生成器生成'",
            'alter table `users` add primary key (`id`)',
        ],
    ],

    'json: adds nullable json column' => [
        'method' => 'json',
        'arguments' => ['context', 'comment'],
        'expected' => [
            "alter table `users` add `context` json null comment 'comment'",
        ],
    ],

    'json: adds not nullable json column' => [
        'method' => 'json',
        'arguments' => ['context', 'comment', false],
        'expected' => [
            "alter table `users` add `context` json not null comment 'comment'",
        ],
    ],

    'morphs: adds morph columns and unique index' => [
        'method' => 'morphs',
        'arguments' => ['relation', 'relation type', 'id'],
        'expected' => [
            "alter table `users` add `relation_type` varchar(255) not null comment 'relation type'",
            "alter table `users` add `relation_id` bigint unsigned not null comment 'id'",
            'alter table `users` add unique `users_relation_type_relation_id_unique`(`relation_type`, `relation_id`)',
            'alter table `users` add index `users_relation_id_index`(`relation_id`)',
        ],
    ],

    'morphs: adds morph columns and normal index' => [
        'method' => 'morphs',
        'arguments' => ['relation', 'relation type', 'id', false],
        'expected' => [
            "alter table `users` add `relation_type` varchar(255) not null comment 'relation type'",
            "alter table `users` add `relation_id` bigint unsigned not null comment 'id'",
            'alter table `users` add index `users_relation_type_relation_id_index`(`relation_type`, `relation_id`)',
            'alter table `users` add index `users_relation_id_index`(`relation_id`)',
        ],
    ],

    'requestId: adds request_id column' => [
        'method' => 'requestId',
        'arguments' => [],
        'expected' => [
            "alter table `users` add `request_id` varchar(36) not null comment '请求id'",
        ],
    ],

    'string: adds string column' => [
        'method' => 'string',
        'arguments' => ['username', '用户名'],
        'expected' => [
            "alter table `users` add `username` varchar(255) not null comment '用户名'",
        ],
    ],

    'string: adds custom length string column' => [
        'method' => 'string',
        'arguments' => ['username', '用户名', 30],
        'expected' => [
            "alter table `users` add `username` varchar(30) not null comment '用户名'",
        ],
    ],

    'string: adds default empty string column' => [
        'method' => 'string',
        'arguments' => ['username', '用户名', null, true],
        'expected' => [
            "alter table `users` add `username` varchar(255) not null default '' comment '用户名'",
        ],
    ],

    'timestamp: adds timestamp columns' => [
        'method' => 'timestamp',
        'arguments' => ['created_at', '创建时间'],
        'expected' => [
            "alter table `users` add `created_at` timestamp null comment '创建时间'",
        ],
    ],

    'timestamps: adds created_at and updated_at columns' => [
        'method' => 'timestamps',
        'arguments' => [],
        'expected' => [
            "alter table `users` add `created_at` timestamp null comment '创建时间'",
            "alter table `users` add `updated_at` timestamp null comment '更新时间'",
        ],
    ],

    'unsignedBigInteger: adds unsigned bigint column' => [
        'method' => 'unsignedBigInteger',
        'arguments' => ['uid', '用户id'],
        'expected' => [
            "alter table `users` add `uid` bigint unsigned not null comment '用户id'",
        ],
    ],

    'unsignedInteger: adds unsigned int column' => [
        'method' => 'unsignedInteger',
        'arguments' => ['uid', '用户id'],
        'expected' => [
            "alter table `users` add `uid` int unsigned not null comment '用户id'",
        ],
    ],

    'unsignedSmallInteger: adds unsigned small int column' => [
        'method' => 'unsignedSmallInteger',
        'arguments' => ['uid', '用户id'],
        'expected' => [
            "alter table `users` add `uid` smallint unsigned not null comment '用户id'",
        ],
    ],

    'unsignedTinyInteger: adds unsigned small int column' => [
        'method' => 'unsignedTinyInteger',
        'arguments' => ['uid', '用户id'],
        'expected' => [
            "alter table `users` add `uid` tinyint unsigned not null comment '用户id'",
        ],
    ],

    'version: adds version column' => [
        'method' => 'version',
        'arguments' => [],
        'expected' => [
            "alter table `users` add `v` int unsigned not null default '1' comment '数据版本号'",
        ],
    ],
]);

it('migrates', function (string $method, array $arguments, array $expected) {
    $this->invoker->$method(...$arguments);

    expect($this->migration->toSql())->toBe($expected);

})->with('migrations');

it('makes comment', function () {
    expect($this->invoker->makeComment('username', 'foo'))
        ->toBe('username|'.date('Ymd').'|foo');
});

function migration()
{
    $connection = DB::connection();
    $connection->setSchemaGrammar(
        new MySqlGrammar($connection)
    );

    return new class(new Blueprint($connection, 'users', fn () => null)) extends Migration
    {
        public function __construct(public Blueprint $table)
        {
            $this->useTable($table);
        }

        public function toSql(): array
        {
            return $this->table->toSql();
        }
    };
}
