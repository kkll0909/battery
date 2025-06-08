<?php

namespace app\admin\model\shop;

use think\Model;


class Shoplist extends Model
{

    

    

    // 表名
    protected $name = 'shoplist';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['1' => __('Publish'),'0' => __('Unpublish')];
    }

    public function getSbtypeList()
    {
        return ['buy' => __('Buy'),'zp' => __('Zp')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}
