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
        'status_text',
        'sbtype_text',
        'usetype_text',
        'paytype_text',
    ];



    public function getStatusList()
    {
        return ['1' => __('Publish'),'0' => __('Unpublish')];
    }

    public function getSbtypeList()
    {
        return ['buy' => __('Buy'),'zp' => __('Zp')];
    }
    public function getUsetypeList()
    {
        return ['payuse' => __('Payuse'),'usepay' => __('Usepay')];
    }
    public function getPaytypeList()
    {
        return ['m' => __('M'),'j' => __('J'),'n' => __('N')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    public function getSbtypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['sbtype'] ?? '');
        $list = $this->getSbtypeList();
        return $list[$value] ?? '';
    }

    public function getUsetypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['usetype'] ?? '');
        $list = $this->getUsetypeList();
        return $list[$value] ?? '';
    }

    public function getPaytypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['paytype'] ?? '');
        $list = $this->getPaytypeList();
        return $list[$value] ?? '';
    }




}
