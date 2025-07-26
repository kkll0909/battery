<?php

namespace addons\miniprogram\library;

use EasyWeChat\Kernel\Messages\{Text, Image, Link, MiniProgramPage};
use app\admin\model\miniprogram\Reply as ModelReply;
use app\admin\model\miniprogram\News  as ModelNews;

/**
 * 消息回复类
 */
class MessageReply
{

    /**
     * @notes 回复内容
     * @author Xing <464401240@qq.com>
     */
    private $content = [];

    /**
     * @notes 消息回复入口（任务分发）
     * @author Xing <464401240@qq.com>
     */
    public function handle($keyword = '', $item = [])
    {
        //未指定回复内容时，则通过关键词查找
        if (empty($item)) {
            $keyword = trim($keyword);
            $replyList = ModelReply::where('status', 'normal')->select();
            foreach ($replyList as $reply) {
                $keyword_arr = explode(',', $reply['keyword']);
                switch ($reply['matching_type']) {
                    //全词匹配
                    case 1:
                        in_array($keyword, $keyword_arr) && $item = $reply;
                        break;
                    //模糊匹配
                    case 2:
                        foreach ($keyword_arr as $val) {
                            stripos($keyword, $val) !== false && $item = $reply;
                        }
                        break;
                }
                if ($item) {
                    break; // 得到回复，中止循环
                }
            }
        }

        // 未指定或通过关键词也没有找到回复内容时，则查询默认回复
        if (empty($item)) {
            $item = ModelReply::where('keyword', 'default')->where('status', 'normal')->find();
        }

        // 找到回复的内容
        if (!empty($item)) {
            $this->content = json_decode($item->content, true);
            //根据回复类型调用不同的方法推送
            $action = $item['reply_type'];
            if (method_exists($this, $action)) {
                return $this->$action();
            }
        }
    }

    /**
     * 回复文本消息
     */
    public function text($msg = '')
    {
        if ($msg) {
            return new Text($msg);
        }
        if (isset($this->content['text']) && $this->content['text']) {
            return new Text($this->content['text']);
        }
        return false;
    }

    /**
     * 回复图文消息
     */
    private function link()
    {
        if (!isset($this->content['news']) || !$this->content['news'] || !is_numeric($this->content['news'])) {
            return false;
        }
        $data = ModelNews::get($this->content['news']);
        if (empty($data)) {
            return false;
        }
        return new Link([
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'url' => $data['url'] ?? '',
            'thumb_url' => cdnurl($data['pic'], true),
        ]);
    }

    /**
     * 回复图片消息
     */
    private function image()
    {
        if (!isset($this->content['image_media_id']) || !$this->content['image_media_id']) {
            return false;
        }
        return new Image($this->content['image_media_id']);
    }

    /**
     * 回复卡片消息
     */
    private function miniprogrampage()
    {
        return new MiniProgramPage(
            [
                'appid' => '',
                'title' => $this->content['miniprogrampage_title'],
                'pagepath' => $this->content['miniprogrampage_pagepath'],
                'thumb_media_id' => $this->content['miniprogrampage_media_id']
            ]
        );
    }

}