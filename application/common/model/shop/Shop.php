<?php

namespace app\common\model\shop;

use think\Model;


class Shop extends Model
{

    

    

    // 表名
    protected $name = 'shop';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'isopen_text'
    ];
    

    
    public function getStatusList()
    {
        return ['show' => __('Show'),'close' => __('Close')];
    }

    public function getIsopenList()
    {
        return ['1' => __('Opening'),'2' => __('Closing')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }
    public function getIsopenTextAttr($value, $data)
    {
        $value = $value ?: ($data['isopen'] ?? '');
        $list = $this->getisopenList();
        return $list[$value] ?? '';
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
