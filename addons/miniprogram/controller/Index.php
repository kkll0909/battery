<?php

namespace addons\miniprogram\controller;
use think\addons\Controller;
use addons\miniprogram\library\WechatService;

class Index extends Controller
{
    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    /**
     * @notes 微信小程序-服务器接口
     * @author Xing <464401240@qq.com>
     */
    public function wechat()
    {
        $result = (new WechatService)->observe();
        return response($result->getContent())->header([
            'Content-Type' => 'text/plain;charset=utf-8'
        ]);
    }

    /**
     * @notes 更新素材有效期
     * @author Xing <464401240@qq.com>
     * 请设置定时任务建议设置30分钟执行一次，也可根据自身情况调整
     * url:  https://域名/addons/miniprogram/index/updateMaterial
     */
    public function updateMaterial()
    {
        $time = time() - 60 * 60 * 24 * 3 + 3600;
        $list = \app\admin\model\miniprogram\Reply::where('updatetime', '<', $time)->select();
        foreach ($list as $row) {
            $row->save();
        }
        echo 'ok';
    }
}