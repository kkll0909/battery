<?php

namespace app\admin\model\department;

use think\Model;


class Withdraw extends Model
{

    

    

    // 表名
    protected $name = 'withdraw';
    
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
    

    //nopay,pay,fail
    public function getStatusList()
    {
        return ['nopay' => __('Nopay'),'pay' => __('Pay'),'fail' => __('Fail')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }




}
