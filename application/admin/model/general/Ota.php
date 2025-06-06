<?php

namespace app\admin\model\general;

use think\Model;


class Ota extends Model
{

    

    

    // 表名
    protected $name = 'ota';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    public function getStatusList()
    {
        return ['show' => __('Show'), 'close' => __('Close')];
    }







}
