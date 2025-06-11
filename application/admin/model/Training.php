<?php

namespace app\admin\model;

use think\Model;


class Training extends Model
{

    

    

    // 表名
    protected $name = 'training';
    
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
        'ntype_text',
        'recommend_text',
    ];
    

    
    public function getStatusList()
    {
        return ['show' => __('Show'),'close' => __('Close')];
    }

    public function getNtypeList()
    {
        return ['word' => __('Word'),'video' => __('Video')];
    }

    public function getRecommendList()
    {
        return ['1' => __('Yes'),'0' => __('No')];
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

    public function getNtypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['ntype'] ?? '');
        $list = $this->getNtypeList();
        return $list[$value] ?? '';
    }

    public function getRecommendTextAttr($value, $data)
    {
        $value = $value ?: ($data['recommend'] ?? '');
        $list = $this->getRecommendList();
        return $list[$value] ?? '';
    }

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
