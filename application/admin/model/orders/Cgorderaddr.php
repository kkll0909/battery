<?php

namespace app\admin\model\orders;

use think\Model;


class Cgorderaddr extends Model
{

    

    

    // 表名
    protected $name = 'cgorder_addr';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
    ];

    public function getTypeList()
    {
        //nopay,pay
        return ['express' => __('Express'),'self'=>__('Self')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return $list[$value] ?? '';
    }

    







}
