<?php

use Pin\Database\Config;
use Pin\Models\Model;

return [
    /**
     * 数据库连接配置
     */
    'connections' => [
        /**
         * default 连接配置
         */
        Model::CONNECTION_DEFAULT => Config::mysql(Model::CONNECTION_DEFAULT),
    ],
];
