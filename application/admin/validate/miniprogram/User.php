<?php

namespace app\admin\validate\miniprogram;
use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [

    ];

    /**
     * 提示消息
     */
    protected $message = [

    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];
}
