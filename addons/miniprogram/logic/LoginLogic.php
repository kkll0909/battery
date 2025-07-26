<?php

namespace addons\miniprogram\logic;
use addons\miniprogram\library\WechatService;
use addons\miniprogram\service\WechatUserService;
use think\Db;

/**
 * 登录逻辑
 */
class LoginLogic
{

    /**
     * @notes 小程序-授权登录
     * @author Xing <464401240@qq.com>
     */
    public static function mnpLogin(array $params, \app\common\library\Auth $auth)
    {
        Db::startTrans();
        try {
            //通过code获取微信 openid
            $response   = (new WechatService())->getMnpResByCode($params['code']);
            $userServer = new WechatUserService(
                $response,
                'miniprogram',
                $params['nickname'] ?? '',
                $params['avatar'] ?? ''
            );
            $userInfo   = $userServer->getResopnseByUserInfo()->authUserLogin($auth)->getUserInfo();
            if (empty($userInfo)) {
                return $auth->getError() ?? '发生未知错误';
            }
            Db::commit();
            return $userInfo;
        } catch (\Exception  $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
}