<?php

use Pin\Support\Str;

return [

    /**
     * 默认分页大小
     *
     * 当请求未传递 page_size 时，系统默认使用的分页条数。
     */
    'default_page_size' => env('DEFAULT_PAGE_SIZE', 15),

    /**
     * 允许的分页大小列表
     *
     * 用于限制前端允许请求的 page_size 范围。
     */
    'available_page_sizes' => env('AVAILABLE_PAGE_SIZES')
        ? Str::explodeToIntegers(env('AVAILABLE_PAGE_SIZES'))
        : [15, 30, 50, 100],

    /**
     * 页码参数名
     *
     * 用于表示当前页码。
     */
    'page_name' => 'page',

    /**
     * 分页大小参数名
     *
     * 用于表示每页返回的数据条数。
     */
    'page_size_name' => 'page_size',
];
