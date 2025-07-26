<?php

namespace app\admin\validate\miniprogram;
use think\Validate;

class Template extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'tempkey' => 'require|max:50|unique:miniprogram_template',
        'name' => 'require|max:100|unique:miniprogram_template',
        'tempid' => 'require|max:100',
        'content' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'tempkey.require' => '请填写场景值',
        'tempkey.max' => '场景值最多50字符',
        'tempkey.unique' => '场景值已经存在了',
        'name.require' => '请填写模版名称',
        'name.max' => '模版名称长度不符',
        'name.unique' => '模版名称已经存在',
        'tempid.require' => '请填写模版id',
        'tempid.max' => '模版id长度不符',
        'content.content' => '请填写模版内容',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['tempkey', 'name', 'tempid', 'content'],
        'edit' => ['tempkey', 'name', 'tempid', 'content'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'tempkey' => __('Tempkey'),
            'name' => __('Name'),
            'tempid' => __('Tempid'),
            'content' => __('Content'),
        ];
        parent::__construct($rules, $message, $field);
    }
}
