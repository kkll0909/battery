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
        'bxstauts_text',
        'bxtype_text',
        'isok_text'
    ];


    //wxup维修上报 wxjd维修派单 wxing正在维修 wxzd维修转单 wxwc维修完成
    public function getBxstautsList()
    {
        return ['wxup' => __('Wxup'), 'wxjd' => __('Wxjd'), 'wxing' => __('Wxing'), 'wxzd' => __('Wxzd'), 'wxwc' => __('Wxwc')];
    }

    //sbok可以使用 sbno无法使用 sbnoc无法充电
    public function getBxtypeList()
    {
        return ['sbok' => __('Sbok'), 'sbno' => __('Sbno'), 'sbnoc' => __('Sbnoc')];
    }

    public function getIsokList()
    {
        return ['0' => __('Wait'), '1' => __('Ok'), '2' => __('Fail')];
    }

    public function getBxstautsTextAttr($value, $data)
    {
        $value = $value ?: ($data['bxstauts'] ?? '');
        $list = $this->getBxstautsList();
        return $list[$value] ?? '';
    }

    public function getBxtypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['bxtype'] ?? '');
        $list = $this->getBxtypeList();
        return $list[$value] ?? '';
    }

    public function getIsokTextAttr($value, $data)
    {
        $value = $value ?: ($data['isok'] ?? '');
        $list = $this->getIsokList();
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

    public function user()
    {
        return $this->belongsTo('\app\admin\model\User', 'wxuser_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
