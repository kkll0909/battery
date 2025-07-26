<?php

namespace app\admin\validate\miniprogram;
use think\Validate;

class News extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'title'       => 'require|length:1,255',
        'description' => 'require|length:1,255',
        'url'         => 'require|url',
        'pic'         => 'require',
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
        'add'   => ['title', 'description', 'url', 'pic'],
        'edit'  => ['title', 'description', 'url', 'pic'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'title' => __('Title'),
            'description' => __('Description'),
            'url' => __('Url'),
            'pic' => __('Pic'),
        ];
        parent::__construct($rules, $message, $field);
    }
    
}
