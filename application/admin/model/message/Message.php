<?php

namespace app\admin\model\message;

use think\Model;


class Message extends Model
{





    // 表名
    protected $name = 'message';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'ctime_text',
        'status_text',
        'type_text',
        'totype_text',
    ];



    public function getStatusList()
    {
        return ['show' => __('Show'),'close' => __('Close')];
    }

    public function getTypeList()
    {
        return ['msg' => __('Msg'),'sys' => __('Sys')];//,'order' => __('Order'),'pay' => __('Pay')
    }

    public function getTotypeList()
    {
        return ['plat' => __('Plat'),'member' => __('Member'),'user' => __('User')];
    }


    public function getCtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['ctime'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }

    public function getTotypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['totype'] ?? '');
        $list = $this->getTotypeList();
        return $list[$value] ?? '';
    }

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function msgread()
    {
        return $this->belongsTo('app\admin\model\message\Messageread', 'msgid', 'id',[],'LEFT')->setEagerlyType(0);
    }
}
