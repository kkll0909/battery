<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\model\Feedback;
use app\common\model\Messge;
use app\common\model\Messgeread;
use app\common\model\orders\Cgorders;
use app\common\model\orders\Cgordersub;
use app\common\model\orders\Orderpay;
use app\common\model\shop\Shoplike;
use app\common\model\Useraddr;
use fast\Random;
use think\Config;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
     * 会员中心
     * @ApiInternal
     */
    public function index()
    {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }

    /**
     * 会员登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="account", type="string", required=true, description="账号")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     * @ApiInternal
     */
    public function login()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 手机验证码登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function mobilelogin()
    {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 注册会员
     *
     * @ApiMethod (POST)
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="password", type="string", required=true, description="密码")
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="code", type="string", required=true, description="验证码")
     * @ApiInternal
     */
    public function register()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $email = $this->request->post('email');
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (!$username || !$password) {
            $this->error(__('Invalid parameters'));
        }
        if ($email && !Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $ret = Sms::check($mobile, $code, 'register');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        $ret = $this->auth->register($username, $password, $email, $mobile, []);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Sign up successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 退出登录
     * @ApiMethod (POST)
     */
    public function logout()
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     *
     * @ApiMethod (POST)
     * @ApiParams (name="avatar", type="string", required=true, description="头像地址")
     * @ApiParams (name="username", type="string", required=true, description="用户名")
     * @ApiParams (name="nickname", type="string", required=true, description="昵称")
     * @ApiParams (name="bio", type="string", required=true, description="个人简介")
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
     * 修改邮箱
     *
     * @ApiMethod (POST)
     * @ApiParams (name="email", type="string", required=true, description="邮箱")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     * @ApiInternal
     */
    public function changeemail()
    {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
     * 修改手机号
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function changemobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
     * 第三方登录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="platform", type="string", required=true, description="平台名称")
     * @ApiParams (name="code", type="string", required=true, description="Code码")
     */
    public function third()
    {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app->{$platform}->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo'  => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
     * 重置密码
     *
     * @ApiMethod (POST)
     * @ApiParams (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams (name="newpassword", type="string", required=true, description="新密码")
     * @ApiParams (name="captcha", type="string", required=true, description="验证码")
     */
    public function resetpwd()
    {
        $type = $this->request->post("type", "mobile");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 我的押金
     *
     * @ApiMethod (POST)
     */
    public function mydeposi()
    {
        $user = $this->auth->getUser();
        $orderpay = new Orderpay();
        $where['userid'] = $user->id;
        $where['paystatus'] = 'pay';
        $list = $orderpay->where($where)->select();
        $this->success(__('Success'),$list);
    }

    /**
     * 我的消息
     *
     * @ApiMethod (POST)
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function myMsg()
    {
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        $userid = $this->auth->id;
        $message = new Messge();
        $where['status'] = 'show';
        $list = $message
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page])->each(function ($item,$index) use ($userid){
                $msgread = new Messgeread();
                $w['msgid']=$item['id'];
                $w['userid']=$userid;
                $c = $msgread->where($w)->count();
                if($c>0){$item['isread']=1;}else{$item['isread']=0;}
                return $item;
            });
        $this->success(__('Success'),$list);
    }

    /**
     * 我的收藏
     *
     * @ApiMethod (POST)
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function mycollect()
    {
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        $userid = $this->auth->id;
        $shoplike = new Shoplike();
        $where['type'] = 'collect';
        $where['userid'] = $userid;
        $list = $shoplike
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page])->each(function ($item,$index){
                $shop = new \app\common\model\shop\Shop();
                $w['id']=$item['shopid'];
                $shopinfo = $shop->where($w)->find();
                $item['shopinfo'] = $shopinfo;
                return $item;
            });
        $this->success(__('Success'),$list);
    }



    /**
     * 我的订单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="type", type="string", required=false, description="订单类型(buy,zp,默认zp)")
     * @ApiParams (name="status", type="string", required=false, description="状态,默认空")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function myOrder()
    {
        $type = $this->request->post("type", "zp");
        $where['type'] = $type;
        $status = $this->request->post("status", "");
        if ($status){
            $where['status'] = $status;
        }
        $page = $this->request->post("page", "1");
        $pageSize = $this->request->post("pagesize", "20");
        $user = $this->auth->getUser();
        $where['toid'] = $user->id;
        $Cgo = new Cgorders();
        $list = $Cgo->where($where)
            ->order('id desc')
            ->paginate($pageSize,false,['page'=>$page]);
        $this->success(__('Success'),$list);
    }

    /**
     * 订单设备明细
     *
     * @ApiMethod (POST)
     * @ApiParams (name="oid", type="int", required=true, description="订单ID")
     */
    public function myOrderSub()
    {
        $oid = $this->request->post("oid");
        if (empty($oid)){
            $this->error(__('Invalid parameters'));
        }
        $where['oid'] = $oid;
        $user = $this->auth->getUser();
        //$where['toid'] = $user->id;
        $Cgo = new Cgordersub();
        $list = $Cgo->where($where)
            ->order('id desc')
            ->select();
        $this->success(__('Success'),$list);
    }

    /**
     * 订单支付详情
     *
     * @ApiMethod (POST)
     * @ApiParams (name="oid", type="int", required=true, description="订单ID")
     */
    public function myOrderPay()
    {
        $oid = $this->request->post("oid");
        if (empty($oid)){
            $this->error(__('Invalid parameters'));
        }
        $where['cgoid'] = $oid;
        $user = $this->auth->getUser();
        //$where['toid'] = $user->id;
        $Cgo = new Cgorders();
        $list = $Cgo->where($where)
            ->order('id desc')
            ->select();
        $this->success(__('Success'),$list);
    }

    /**
     * 添加修改地址(addrid为空时表示新增)
     *
     * @ApiMethod (POST)
     * @ApiParams (name="address", type="string", required=true, description="地址")
     * @ApiParams (name="tel", type="string", required=true, description="电话")
     * @ApiParams (name="name", type="string", required=true, description="姓名")
     * @ApiParams (name="addrid", type="int", required=false, description="地址ID")
     */
    public function addmodiaddr()
    {
        $address = $this->request->post("address");
        $tel = $this->request->post("tel");
        $name = $this->request->post("name");
        $addrid = empty($this->request->post("addrid"))?'':$this->request->post("addrid");
        if (!$address || !$tel || !$name){
            $this->error(__('Invalid parameters'));
        }
        $addr = new Useraddr();
        $data['address'] = $address;
        $data['tel'] = $tel;
        $data['name'] = $name;
        if($addrid){
            $addr->save($data,['id'=>$addrid]);
        }else{
            $data['userid'] = $this->auth->id;
            $addr->save($data);
        }
        $this->success(__('Success'));
    }

    /**
     * 删除地址
     *
     * @ApiMethod (POST)
     * @ApiParams (name="addrid", type="int", required=false, description="地址ID")
     */
    public function deladdr()
    {
        $addrid = $this->request->post("addrid");
        if (!$addrid){
            $this->error(__('Invalid parameters'));
        }
        $userid = $this->auth->id;
        $addr = new Useraddr();
        $addr->where(['userid'=>$userid,'id'=>$addrid])->delete();
        $this->success(__('Success'));
    }

    /**
     * 我的地址
     *
     * @ApiMethod (POST)
     */
    public function addrlist()
    {
        $userid = $this->auth->id;
        $addr = new Useraddr();
        $list = $addr->where(['userid'=>$userid])->order('id desc')->select();
        $this->success(__('Success'),$list);
    }

    /**
     * 反馈
     *
     * @ApiMethod (POST)
     * @ApiParams (name="pid", type="int", required=false, description="上级ID，默认为0")
     * @ApiParams (name="desc", type="string", required=true, description="内容100字")
     * @ApiParams (name="tel", type="string", required=true, description="联系方式")
     */
    public function feedback()
    {
        $pid = empty($this->request->post('pid'))?0:$this->request->post('pid');
        $desc = $this->request->post('desc');
        $tel = $this->request->post('tel');
        if(!$desc || !$tel){
            $this->error(__('Invalid parameters'));
        }
        $data['userid'] = $this->auth->id;
        $data['pid'] = $pid;
        $data['desc'] = $desc;
        $data['tel'] = $tel;
        $data['ctime'] = time();
        $fback = new Feedback();
        $fback->save($data);
        $this->success(__('Success'));
    }

    /**
     * 反馈列表
     *
     * @ApiMethod (POST)
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认5")
     */
    public function feedbacklist()
    {
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?5:$this->request->post('pagesize');
        $userid = $this->auth->id;
        $fback = new Feedback();
        $list = $fback
            ->where(['userid'=>$userid,'pid'=>0])
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page])->each(function ($item,$index) use ($fback){
                $item['sublist']=$fback->where(['pid'=>$item['id']])->select();
                return $item;
            });
        $this->success(__('Success'),$list);
    }
}
