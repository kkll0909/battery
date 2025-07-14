<?php

namespace app\admin\model\maint;

use think\Model;


class Maintenancelist extends Model
{

    

    

    // 表名
    protected $name = 'maintenancelist';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'wxtime_text'
    ];
    

    



    public function getWxtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['wxtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setWxtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
