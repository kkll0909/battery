<?php

namespace app\common\model\orders;

use think\Model;


class Orderpay extends Model
{

    

    

    // 表名
    protected $name = 'orderpay';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'paystatus_text',
    ];

    public function getPaystatusList()
    {
        //nopay,pay
        return ['nopay' => __('Nopay'),'pay'=>__('Pay')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['paystatus'] ?? '');
        $list = $this->getPaystatusList();
        return $list[$value] ?? '';
    }
    







}
