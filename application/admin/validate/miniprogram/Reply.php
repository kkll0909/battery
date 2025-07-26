<?php

namespace app\admin\validate\miniprogram;
use think\Validate;

class Reply extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'keyword' => 'checkUnique|max:1000|unique:miniprogram_reply',
        'event_type' => 'checkRequire',
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
        'add'  => ['keyword', 'event_type'],
        'edit' => ['keyword', 'event_type'],
    ];

    //自定义验证规则
    protected function checkRequire($value, $rule, $data=[])
    {
        if ($value == 'ordinary' && !$data['keyword']) {
            return '关键字不能为空';
        } else {
            $where['keyword'] = $value;
            if (isset($data['id'])) {
                $where['id'] = ['<>', $data['id']];
            }
            if (\think\Db::name('miniprogram_reply')->where($where)->value('keyword')) {
                return '已经存在请不要重复添加';
            }
        }
        return true;
    }

    //自定义验证规则
    protected function checkUnique($value, $rule, $data=[])
    {
        $unique_keyword = '';
        $arr = explode(',', $value);
        if (is_array($arr)) {
            $where = [];
            if (isset($data['id'])) {
                $where['id'] = ['<>', $data['id']];
            }
            foreach ($arr as $val) {
                if (\think\Db::name('miniprogram_reply')->where($where)->where('find_in_set("' . $val . '",keyword)')->value('keyword')) {
                    $unique_keyword = $val;
                    continue;
                }
            }
        }
        return $unique_keyword ? '关键词“' . $unique_keyword . '”已经存在' : true;
    }

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'keyword' => __('Keyword'),
            'event_type' => __('Event_type'),
        ];
        parent::__construct($rules, $message, $field);
    }
}
