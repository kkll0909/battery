<?php

namespace addons\miniprogram\validate;
use think\Validate;

/**
 * 微信登录验证
 */
class LoginValidate extends Validate
{
    protected $rule = [
        'code' => 'require',
    ];

    protected $message = [
        'code.require' => 'code缺少',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'mnp' => ['code'],
    ];
}