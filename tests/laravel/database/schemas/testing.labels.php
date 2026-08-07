<?php

return [
    'admins' => [
        'id' => 'Id',
        'username' => 'Username',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],
    'menus' => [
        'id' => 'Id',
        'pid' => 'Parent Id',
        'name' => 'Name',
        'path' => 'Path',
        'sort' => 'Sort',
    ],
    'migrations' => [
        'id' => 'Id',
        'migration' => 'Migration',
        'batch' => 'Batch',
    ],
    'operate_logs' => [
        'id' => 'Id',
        'uid' => 'Uid',
        'username' => 'Username',
        'user_type' => 'User Type',
        'action' => 'Action',
        'subject_type' => 'Logging Table',
        'subject_id' => 'Logging Id',
        'subject_name' => 'Logging Name',
        'changes' => 'Changes',
        'route' => 'Route',
        'ip' => 'Ip',
        'created_at' => 'Created At',
    ],
    'sequences' => [
        'id' => 'Id',
        'name' => 'Name',
        'value' => 'Value',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'users' => [
        'id' => 'Id',
        'username' => '用户名',
        'password' => 'Password',
        'realname' => 'Realname',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],
];
