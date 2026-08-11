<?php

return [
    'success' => '请求成功',
    'failed' => '请求失败',

    'bad_request' => '请求异常',
    'forbidden' => '无权访问',
    'model_not_found' => ':model不存在',
    'token_mismatch' => '页面已过期，请重试或刷新页面',
    'validation_failed' => '数据验证失败',
    'too_many_requests' => '您的请求过于频繁，请稍后再试',
    'server_error' => '服务器错误',
    'access_denied' => '无权限执行该操作',
    'unknown' => '未知错误',

    'create_failed' => '创建失败',
    'update_failed' => '更新失败',
    'delete_failed' => '删除失败',
    'data_version_mismatch' => '更新失败，请刷新后重试',

    'auth_user_not_found' => '请登录',
    'auth_token_expired' => '登录已过期，请重新登录',
    'auth_token_invalid' => '登录状态无效，请重新登录',
    'auth_token_missing' => '请登录',

    'token_expired' => '令牌已过期',
    'token_invalid' => '无效的令牌',
    'token_missing' => '令牌不能为空',

    'password_decode_failed' => '无效的密码',
    'password_invalid' => '无效的密码',

    'password_too_short' => ':attribute不能少于:min个字符',
    'password_too_long' => ':attribute不能超过:max个字符',
    'password_requires_number' => ':attribute必须至少包含一个数字',
    'password_requires_letter' => ':attribute必须至少包含一个字母',
    'password_requires_lowercase' => ':attribute必须至少包含一个小写字母',
    'password_requires_uppercase' => ':attribute必须至少包含一个大写字母',
    'password_requires_mixed_case' => ':attribute必须同时包含大写字母和小写字母',
    'password_requires_symbol' => ':attribute必须至少包含一个符号',
    'password_insufficient_types' => ':attribute必须至少包含字母、数字、符号中的两种',
    'password_requires_all_types' => ':attribute必须同时包含字母、数字和符号',
    'password_contains_whitespace' => ':attribute不能包含空格',
    'password_sequence_too_long' => ':attribute不能包含超过:size个连续的字母或数字',
    'password_too_many_repeats' => ':attribute不能包含超过:size个连续重复字符',
];
