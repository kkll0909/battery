<?php

namespace app\admin\model\batmanage;

use think\Model;


class Belong extends Model
{

    

    

    // 表名
    protected $name = 'belong';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'stime_text',
        'etime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['show' => __('Show'),'close'=>__('Close')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }


    public function getStimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['stime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['etime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function bat()
    {
        return $this->belongsTo('app\admin\model\batmanage\Bat', 'batid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
