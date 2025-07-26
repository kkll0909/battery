<?php

namespace addons\miniprogram\library;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use app\miniprogram\service\WechatUserService;

/**
 * 小程序事件处理类
 * Class WechatEventLogic
 */
class WechatEvent implements EventHandlerInterface
{

    /**
     * @notes 接收到的消息
     * @author Xing <464401240@qq.com>
     */
    private $message = [];

    /**
     * @notes 事件处理入口（任务分发）
     * @author Xing <464401240@qq.com>
     */
    public function handle($message = null)
    {
        $this->message = $message;
        if (empty($this->message)) {
            return false;
        }
        $msg_id = \think\cache::tag('miniprogram')->get('MsgId' . $this->message['MsgId']);
        if (!$msg_id) {
            \think\cache::tag('miniprogram')->set('MsgId' . $this->message['MsgId'], 1, 60);
            $action = strtolower($this->message['MsgType']);
            if (method_exists($this, $action)) {
                return WechatService::application()->customer_service
                    ->message($this->$action())
                    ->to($this->message['FromUserName'])
                    ->send();
            }
        }
        return false;
    }

    /**
     * @notes 收到文本消息
     * @author Xing <464401240@qq.com>
     */
    private function text()
    {
        //您自己的其他业务逻辑在这里写，但最后一定要return消息处理方法，否则消息无法得到回复
        return (new MessageReply)->handle($this->message['Content']);
    }

    /**
     * @notes 收到图片消息
     * @author Xing <464401240@qq.com>
     */
    private function image()
    {
        //您自己的其他业务逻辑在这里写，但最后一定要return消息处理方法，否则消息无法得到回复
        return (new MessageReply)->handle('default');
    }
}