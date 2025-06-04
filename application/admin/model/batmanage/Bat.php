<?php

namespace app\admin\model\batmanage;

use think\Model;


class Bat extends Model
{

    

    

    // 表名
    protected $name = 'bat';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'islike_text',
        'battype_text',
        'balance_text',
        'chargedischargeswitch_text',
        'mosstatus_text',
    ];
    

    
    public function getStatusList()
    {
        return ['show' => __('Show'),'close'=>__('Close')];
    }

    public function getIslikeList()
    {
        return ['auto' => __('Auto'),'noauto'=>__('Noauto')];
    }

    public function getBattypeList()//1 三元 / 2 铁锂 / 3 钛锂
    {
        return ['1' => __('Ternary'),'2'=>__('Lithiumiron'),'3'=>__('Lithiumtitanium')];
    }

    public function getBalanceList()//0 禁止 / 1 开启
    {
        return ['0' => __('Proscribe'),'1'=>__('Enabled')];
    }

    public function getChargedischargeswitchList()//0 禁止充放 / 1 可充放 / 2 禁充 / 3 禁放
    {
        return ['0' => __('Proscribe'),'1'=>__('Enabled'),'2'=>__('Nocharging'),'3'=>__('Nodischarging')];
    }

    public function getMosstatusList()//0 空闲 / 1 充电中 / 2 放电中
    {
        return ['0' => __('Idle'),'1'=>__('Charging'),'2'=>__('Discharging')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }
    public function getIslikeTextAttr($value, $data)
    {
        $value = $value ?: ($data['islike'] ?? '');
        $list = $this->getIslikeList();
        return $list[$value] ?? '';
    }

    public function getBattypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['battype'] ?? '');
        $list = $this->getBattypeList();
        return $list[$value] ?? '';
    }

    public function getBalanceTextAttr($value, $data)
    {
        $value = $value ?: ($data['balance'] ?? '');
        $list = $this->getBalanceList();
        return $list[$value] ?? '';
    }

    public function getChargedischargeswitchTextAttr($value, $data)
    {
        $value = $value ?: ($data['chargedischargeswitch'] ?? '');
        $list = $this->getChargedischargeswitchList();
        return $list[$value] ?? '';
    }

    public function getMosstatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['mosstatus'] ?? '');
        $list = $this->getMosstatusList();
        return $list[$value] ?? '';
    }



    public function factory()
    {
        return $this->belongsTo('Factory', 'csid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
