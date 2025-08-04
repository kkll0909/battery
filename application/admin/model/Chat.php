<?php

namespace app\admin\model;

use think\Model;


class Chat extends Model
{

    

    

    // 表名
    protected $name = 'chat';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'ctime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['100' => __('Status 100')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getCtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['ctime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function aadmin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'a_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function postadmin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'postid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
