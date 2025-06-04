<?php

namespace app\admin\model;

use think\Model;


class Otalog extends Model
{

    

    

    // 表名
    protected $name = 'otalog';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'stime_text'
    ];
    

    



    public function getStimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['stime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
