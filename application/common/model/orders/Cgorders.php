<?php

namespace app\common\model\orders;

use think\Model;


class Cgorders extends Model
{

    

    

    // 表名
    protected $name = 'cgorders';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'stime_text',
        'etime_text',
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        //nopay,pay
        return ['nopay' => __('Nopay'),'pay'=>__('Pay')];
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


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function fromadmin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'fromid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function touser()
    {
        return $this->belongsTo('app\admin\model\User', 'toid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function shop()
    {
        return $this->belongsTo('app\admin\model\shop\Shop', 'shopid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function cgaddr()
    {
        return $this->belongsTo('app\admin\model\orders\Cgorderaddr', 'oid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
