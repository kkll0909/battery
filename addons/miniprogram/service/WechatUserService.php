<?php

namespace addons\miniprogram\service;

use app\common\model\User;
use app\admin\model\miniprogram\User as WxUserAuth;

/**
 * 用户功能类（主要微信登录后创建和更新用户）
 * Class WechatUserService
 */
class WechatUserService
{
    protected $user_type  = 'miniprogram';
    protected $response   = [];
    protected $code       = null;
    protected $openid     = null;
    protected $unionid    = null;
    protected $nickname   = null;
    protected $headimgurl = null;
    protected $user;

    public function __construct(array $response, $user_type = 'miniprogram', string $nickname = '', string $headimgurl = '')
    {
        $this->user_type  = $user_type;
        $this->nickname   = $nickname;
        $this->headimgurl = $headimgurl;
        $this->setParams($response);
    }

    /**
     * @notes 设置微信返回的用户信息
     * @author Xing <464401240@qq.com>
     */
    private function setParams($response): void
    {
        $this->response     = $response;
        $this->openid       = $response['openid'];
        $this->unionid      = $response['unionid'] ?? '';
    }

    /**
     * @notes 根据opendid或unionid获取系统用户信息
     * @return $this
     * @author Xing <464401240@qq.com>
     */
    public function getResopnseByUserInfo(): self
    {
        $openid  = $this->openid;
        $unionid = $this->unionid;

        $user = User::alias('u')
            ->field('u.id, u.id as user_id, u.nickname, u.avatar, wu.openid, wu.unionid')
            ->join('miniprogram_user wu', 'wu.user_id = u.id')
            ->where(function ($query) use ($openid, $unionid) {
                $query->whereOr(['wu.openid' => $openid]);
                if (isset($unionid) && $unionid) {
                    $query->whereOr(['wu.unionid' => $unionid]);
                }
            })->find();
        $this->user = $user;
        return $this;
    }

    /**
     * @notes 用户授权登录，
     * 如果用户不存在，创建用户；用户存在，更新用户信息，并检查该端信息是否需要写入
     * @return WechatUserService
     * @author Xing <464401240@qq.com>
     */
    public function authUserLogin(\app\common\library\Auth $auth): self
    {
        if (!$this->user) {
            $this->createUser($auth);
        } else {
            $this->updateUser($auth);
        }
        return $this;
    }

    /**
     * @notes 生成授权记录(绑定)
     * @author Xing <464401240@qq.com>
     */
    public function createAuth($user_id)
    {
        //先检查openid是否有记录
        $isAuth = WxUserAuth::where('openid', '=', $this->openid)->find();
        if ($isAuth) {
            throw new \Exception('该微信已被绑定');
        }
        if (!empty($this->unionid)) {
            //在用unionid找记录，防止生成两个账号，同个unionid的问题
            $userAuth = WxUserAuth::where(['unionid' => $this->unionid])->find();
            if ($userAuth && $userAuth->user_id != $user_id) {
                throw new \Exception('该微信已被绑定');
            }
        }
        //如果没有授权，直接生成一条微信授权记录
        WxUserAuth::create([
            'user_id'  => $user_id,
            'openid'   => $this->openid,
            'unionid'  => $this->unionid ?? '',
            'user_type'=> $this->user_type,
        ]);
        return true;
    }

    /**
     * @notes 获取用户信息
     * @return array
     * @author Xing <464401240@qq.com>
     */
    public function getUserInfo()
    {
        return $this->user;
    }

    /**
     * @notes 创建用户（注册）
     * @author Xing <464401240@qq.com>
     */
    private function createUser(\app\common\library\Auth $auth): void
    {
        $username  = $this->createUserSn('u');
        $nickname  = "用户" . $username;
        if (!empty($this->nickname)) {
            $nickname = $this->nickname;
        }
        $ret = $auth->register($username, $this->createUserSn('pwd'), '', '', [
            'avatar'   => $this->headimgurl,
            'nickname' => $nickname,
        ]);
        if ($ret) {
            $this->user = $auth->getUserinfo();
            //创建授权信息
            WxUserAuth::create([
                'user_id'  => $this->user['id'],
                'openid'   => $this->openid,
                'unionid'  => $this->unionid,
                'user_type'=> $this->user_type
            ]);
        } else {
            $this->user = null;
        }
    }

    /**
     * @notes 更新用户（登录）
     * @author Xing <464401240@qq.com>
     * @remark 该端没授权信息,重新写入一条该端的授权信息
     */
    private function updateUser(\app\common\library\Auth $auth): void
    {
        // 更新会员信息
        if ($this->headimgurl) {
            $this->user->avatar = $this->headimgurl;
        }
        if (!empty($this->nickname)) {
            $this->user->nickname = $this->nickname;
        }
        $this->user->save();

        // 查询授权信息
        $userAuth = WxUserAuth::where(['user_id' => $this->user->user_id, 'openid' => $this->openid])->find();
        // 无该端授权信息，新增一条
        if (!$userAuth) {
            $userAuth = new WxUserAuth();
            $userAuth->user_id  = $this->user->user_id;
            $userAuth->openid   = $this->openid;
            $userAuth->unionid  = $this->unionid;
            $userAuth->user_type= $this->user_type;
            $userAuth->save();
        } else {
            if (empty($userAuth['unionid']) && !empty($this->unionid)) {
                $userAuth->unionid = $this->unionid;
                $userAuth->save();
            }
        }
        //已经有账号直接登录
        $ret = $auth->direct($this->user->user_id);
        if ($ret) {
            $this->user = $auth->getUserinfo();
        } else {
            $this->user = null;
        }
    }

    /**
     * @notes 生成用户编码
     * @param string $prefix
     * @param int $length
     * @return string
     * @author Xing <464401240@qq.com>
     */
    private function createUserSn($prefix = '', $length = 8): string
    {
        $rand_str = '';
        for ($i = 0; $i < $length; $i++) {
            $rand_str .= mt_rand(0, 9);
        }
        $sn = $prefix . $rand_str;
        if (User::where(['username' => $sn])->find()) {
            return $this->createUserSn($prefix, $length);
        }
        return $sn;
    }
}