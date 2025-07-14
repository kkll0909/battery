<?php

namespace app\admin\model\maint;

use think\Model;


class Maintenance extends Model
{

    

    

    // 表名
    protected $name = 'maintenance';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'bxtime_text',
        'bxstauts_text'
    ];


    //wxup上报状态 wxjd维修接单 wxing正在维修 wxzd维修转单 wxwc维修完成
    public function getBxstautsList()
    {
        return ['wxup' => __('Wxup'), 'wxjd' => __('Wxjd'), 'wxing' => __('Wxing'), 'wxzd' => __('Wxzd'), 'wxwc' => __('Wxwc')];
    }

    public function getBxstautsTextAttr($value, $data)
    {
        $value = $value ?: ($data['bxstauts'] ?? '');
        $list = $this->getBxstautsList();
        return $list[$value] ?? '';
    }



    public function getBxtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['bxtime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setBxtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
