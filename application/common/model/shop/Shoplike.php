<?php

namespace app\common\model\shop;

use think\Model;


class Shoplike extends Model
{

    

    

    // 表名
    protected $name = 'shoplike';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'ctime_text',
        'type_text'
    ];


    public function getTypeList()
    {
        return ['collect' => __('Collect'),'like' => __('Like')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
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


}
