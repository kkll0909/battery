<?php

namespace app\admin\model\miniprogram;
use think\Model;
use addons\miniprogram\library\WechatService;

class Reply extends Model
{

    // 表名
    protected $name = 'miniprogram_reply';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'reply_type_text',
        'status_text'
    ];

    public function getReplyTypeList()
    {
        return ['text' => __('Reply_type text'), 'image' => __('Reply_type image'), 'link' => __('Reply_type link'), 'miniprogrampage' => __('Reply_type miniprogrampage')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getReplyTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['reply_type']) ? $data['reply_type'] : '');
        $list = $this->getReplyTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected static function init()
    {
        //写入前
        self::beforeWrite(function ($row) {
            if (isset($row->event_type) && $row->event_type == 'default') {
                $row->keyword = 'default';
            } else if (isset($row->event_type) && $row->event_type == 'subscribe') {
                $row->keyword = 'subscribe';
            }

            //素材处理
            if (isset($row->reply_type) && in_array($row->reply_type, ['image', 'miniprogrampage'])) {
                $row->origin['content'] = json_decode($row->origin['content'] ?? '', true);
                $origin = $row->origin['content'][$row->reply_type] ?? '';
                $time = time() - 60 * 60 * 24 * 3 + 3600;
                $row->content = is_string($row->content) ? json_decode($row->content, true) : $row->content;
                if (isset($row->content[$row->reply_type])) {
                    if ($origin != $row->content[$row->reply_type] || $row->origin['updatetime'] < $time) {
                        if ($row->reply_type == 'image') {
                            $data = $row->content['image'];
                        }
                        if ($row->reply_type == 'miniprogrampage') {
                            $data = $row->content['miniprogrampage'];
                        }
                        $content = $row->content;
                        $content[$row->reply_type . '_media_id'] = WechatService::material($row->reply_type, $data);
                        $row->content = $content;
                    }
                }
            }

            $row->content = is_array($row->content) ? json_encode($row->content) : $row->content;
        });
    }
}