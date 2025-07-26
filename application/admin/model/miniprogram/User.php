<?php

namespace app\admin\model\miniprogram;
use think\Model;
use addons\miniprogram\library\WechatService;

class User extends Model
{
	// 表名
	protected $name = 'miniprogram_user';
	
	// 自动写入时间戳字段
	protected $autoWriteTimestamp = 'int';
	
	// 定义时间戳字段名
	protected $createTime = 'createtime';
	protected $updateTime = false;
	protected $deleteTime = false;
	
	public function fauser()
	{
		return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
	}
}
