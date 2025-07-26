<?php

namespace app\admin\model\miniprogram;
use think\Model;

class Template extends Model
{
    // 表名
    protected $name = 'miniprogram_template';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = false;
    protected $deleteTime = false;
}
