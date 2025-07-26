<?php

namespace addons\miniprogram\controller;
use app\common\controller\Api;
use addons\miniprogram\{
    validate\LoginValidate,
    logic\LoginLogic
};

/**
 * 登录注册
 */
class Login extends Api
{

    // 无需登录的接口,*表示全部
    protected $noNeedLogin = '*';

    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = '*';


    /**
     * @notes 小程序-登录接口
     * @author Xing <464401240@qq.com>
     */
    public function mnpLogin()
    {
        $params = $this->request->post();
        $validate = new LoginValidate();
        if (!$validate->scene('mnp')->check($params)){
            $this->error($validate->getError());
        }
        $result = LoginLogic::mnpLogin($params, $this->auth);
        if (true !== $result && !is_array($result)) {
            $this->error($result);
        }
        $this->success('授权登录成功', $result);
    }
}
