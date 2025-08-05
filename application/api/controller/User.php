<?php

namespace app\api\controller;

use app\admin\model\shop\Shoplist;
use app\admin\model\Shopapply;
use app\admin\model\user\Usermailt;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Lib\Realauth;
use app\common\library\Sms;
use app\common\model\Feedback;
use app\common\model\Messge;
use app\common\model\Messgeread;
use app\common\model\orders\Cgorderaddr;
use app\common\model\orders\Cgorders;
use app\common\model\orders\Cgordersub;
use app\common\model\orders\Orderpay;
use app\common\model\shop\Shoplike;
use app\common\model\Useraddr;
use fast\Http;
use fast\Random;
use think\Config;
use think\Validate;

/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['idcardocr','login', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third','shopapply'];
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
     * 获取用户信息
     *
     * @ApiMethod (POST)
     */
    public function getuinfo()
    {
        $user_id = $this->auth->id;
        $miniuser = new \app\admin\model\miniprogram\User();
        $data = [
            'userinfo' => $this->auth->getUserinfo(),
            'wxinfo' => $miniuser->where(['user_id'=>$user_id])->find(),
        ];
        $this->success(__('Logged in successful'), $data);
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
        $list = $Cgo
            ->with('fromadmin,shop')
            ->where($where)
            ->order('id desc')
            ->paginate($pageSize,false,['page'=>$page])->each(function ($item,$index){
                $cgsub = new Cgordersub();
                $preid = $cgsub->where(['oid'=>$item['id']])->value('preid');
                $item['preinfo'] = Shoplist::get($preid);
                $item['orderaddr'] = Cgorderaddr::where(['oid'=>$item['id']])->find();
                $Cgo = new Orderpay();
                $item['paytypesum'] = $Cgo->where(['oid'=>$item['id'],'isy'=>1])->count();
                return $item;
            });
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
        $where['oid'] = $oid;
        $user = $this->auth->getUser();
        //$where['toid'] = $user->id;
        $Cgo = new Orderpay();
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

    /**
     * 获取小程序openid
     *
     * @ApiMethod (POST)
     * @ApiParams (name="code", type="string", required=true, description="微信CODE")
     */
    public function getopenid()
    {
        $code = $this->request->post('code');
        $appid = "";
        $secret = "";
        //获取小程序配置
        $miniconfig = new \app\admin\model\miniprogram\Config();
        $minicfg = $miniconfig->select();
        foreach ($minicfg as $value){
            if($value['name']=='app_id'){
                $appid = $value['value'];
            }
            if($value['name']=='app_secret'){
                $secret = $value['value'];
            }
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $re = Http::get($url);
        $userid = $this->auth->id;
        $re = is_array($re)?$re:json_decode($re,true);
        $miniuser = new \app\admin\model\miniprogram\User();
        $uinfo = $miniuser->where(['user_id'=>$userid])->find();
        if ($uinfo){
            $in['openid'] = $re['openid'];
            $in['createtime'] = time();
            $miniuser->save($in,['user_id'=>$userid]);
        }else{
            $in['user_id'] = $userid;
            $in['openid'] = $re['openid'];
            $in['createtime'] = time();
            $miniuser->save($in);
        }

        $this->success(__('Success'));
    }

    /**
     * 实名订证
     *
     * @ApiMethod (POST)
     * @ApiParams (name="idcardz", type="string", required=true, description="身份证正面")
     * @ApiParams (name="idcardf", type="string", required=true, description="身份证反面")
     * @ApiParams (name="idcard", type="string", required=true, description="身份证号")
     * @ApiParams (name="realname", type="string", required=true, description="姓名")
     */
    public function realauth()
    {
        $idcardz = $this->request->post('idcardz');
        $idcardf = $this->request->post('idcardf');
        $idcard = $this->request->post('idcard');
        $realname = $this->request->post('realname');
        if(!$idcardz || !$idcardf || !$idcard || !$realname){
            $this->error(__('Invalid parameters'));
        }
        //实名
        $re = Realauth::realauth($realname,$idcard);
        if($re && $re['result']==0){
            $user_id = $this->auth->id;
            $ind['user_id'] = $user_id;
            $ind['idcardz'] = $idcardz;
            $ind['idcardf'] = $idcardf;
            $ind['idcard'] = $idcard;
            $ind['realname'] = $realname;
            $ind['status'] = 1;
            $ind['reaon'] = $re['desc'];
            \app\admin\model\user\Realauth::create($ind);
            \app\common\model\User::update(['isauth'=>1],['id'=>$user_id]);
            $this->success(__('Success'));
        }else{
            if(isset($re['desc'])){
                $this->error(__($re['desc']));
            }else{
                $this->error(__('请检查身份证号的正确性'));
            }
        }

    }

    /**
     * 维修人员申请
     *
     * @ApiMethod (POST)
     * @ApiParams (name="njimg", type="string", required=true, description="场地照片,多图用逗号分开")
     * @ApiParams (name="workimg", type="string", required=true, description="工作证明,多图用逗号分开")
     * @ApiParams (name="idcardz", type="string", required=true, description="身份证正面")
     * @ApiParams (name="idcardf", type="string", required=true, description="身份证反面")
     * @ApiParams (name="idcard", type="string", required=true, description="身份证号")
     * @ApiParams (name="realname", type="string", required=true, description="姓名")
     */
    public function mailtapply()
    {
        $njimg = $this->request->post('njimg');
        $workimg = $this->request->post('workimg');
        $idcardz = $this->request->post('idcardz');
        $idcardf = $this->request->post('idcardf');
        $idcard = $this->request->post('idcard');
        $realname = $this->request->post('realname');
        if(!$idcardz || !$idcardf || !$idcard || !$realname ||!$njimg ||!$workimg){
            $this->error(__('Invalid parameters'));
        }
        $user_id = $this->auth->id;
        $ind['user_id'] = $user_id;
        $ind['njimg'] = $njimg;
        $ind['workimg'] = $workimg;
        $ind['idcardz'] = $idcardz;
        $ind['idcardf'] = $idcardf;
        $ind['idcard'] = $idcard;
        $ind['realname'] = $realname;
        $ind['status'] = 'apply';
        $ind['reaon'] = '';
        Usermailt::create($ind);
//        \app\common\model\User::update(['ismaint'=>1],['id'=>$user_id]);
        $this->success(__('Success'));
    }
    /**
     * 维修人员申请查询
     *
     * @ApiMethod (POST)
     */
    public function mailtapplysearch()
    {
        $user_id = $this->auth->id;
        $info = Usermailt::where(['user_id'=>$user_id])->find();
        $this->success(__('Success'),$info);
    }


    /**
     * 商户入驻
     *
     * @ApiMethod (POST)
     * @ApiParams (name="qytype", type="int", required=true, description="企业类型1企业2个体")
     * @ApiParams (name="usetype", type="int", required=true, description="场地类型1自有2租用")
     * @ApiParams (name="yyzzimg", type="string", required=true, description="营业执照")
     * @ApiParams (name="cdimg", type="string", required=true, description="场地照片,多图用逗号分开")
     * @ApiParams (name="cqzimg", type="string", required=true, description="产权证")
     * @ApiParams (name="zlhtimg", type="string", required=false, description="租用合同usetype为2时必传,多图用逗号分开")
     * @ApiParams (name="jyimg", type="string", required=true, description="经营数据照片,多图用逗号分开")
     * @ApiParams (name="idcardz", type="string", required=true, description="身份证正面")
     * @ApiParams (name="idcardf", type="string", required=true, description="身份证反面")
     * @ApiParams (name="idcard", type="string", required=true, description="身份证号")
     * @ApiParams (name="realname", type="string", required=true, description="姓名")
     * @ApiParams (name="shopname", type="string", required=true, description="商户名称")
     * @ApiParams (name="address", type="string", required=true, description="经营地址")
     * @ApiParams (name="mobile", type="string", required=true, description="联系电话")
     */
    public function shopapply()
    {
        $yyzzimg = $this->request->post('yyzzimg');
        $cdimg = $this->request->post('cdimg');
        $cqzimg = $this->request->post('cqzimg');
        $zlhtimg = $this->request->post('zlhtimg');
        $jyimg = $this->request->post('jyimg');
        $idcardz = $this->request->post('idcardz');
        $idcardf = $this->request->post('idcardf');
        $idcard = $this->request->post('idcard');
        $realname = $this->request->post('realname');
        $qytype = $this->request->post('qytype');
        $usetype = $this->request->post('usetype');
        $address = $this->request->post('address');
        $shopname = $this->request->post('shopname');
        $mobile = $this->request->post('mobile');
        if(!$mobile || !$idcardz || !$idcardf || !$idcard || !$realname ||!$yyzzimg ||!$cdimg ||!$cqzimg ||!$jyimg){
            $this->error(__('Invalid parameters'));
        }
        if(!$qytype || !$usetype || !$address || !$shopname){
            $this->error(__('Invalid parameters'));
        }
        if($usetype==2){
            if(!$zlhtimg){
                $this->error(__('Invalid parameters'));
            }
        }
        $ind['njimg'] = $yyzzimg;
        $ind['cdimg'] = $cdimg;
        $ind['cqzimg'] = $cqzimg;
        $ind['zlhtimg'] = $zlhtimg;
        $ind['jyimg'] = $jyimg;
        $ind['workimg'] = $cdimg;
        $ind['idcardz'] = $idcardz;
        $ind['idcardf'] = $idcardf;
        $ind['idcard'] = $idcard;
        $ind['qytype'] = $qytype;
        $ind['usetype'] = $usetype;
        $ind['address'] = $address;
        $ind['realname'] = $realname;
        $ind['mobile'] = $mobile;
        $ind['status'] = 'apply';
        $ind['reaon'] = '';
        Shopapply::create($ind);
        $this->success(__('Success'));
    }

    /**
     * 商户入驻申请查询
     *
     * @ApiMethod (POST)
     * @ApiParams (name="idcard", type="string", required=true, description="申请时的身份证号")
     */
    public function shopapplysearch()
    {
        $idcard = $this->request->post('idcard');
        if(!$idcard){
            $this->error(__('Invalid parameters'));
        }
        $info = Shopapply::where(['idcard'=>$idcard])->find();
        $this->success(__('Success'),$info);
    }

    /**
     * 身份证识别
     *
     * @ApiMethod (POST)
     * @ApiParams (name="img", type="string", required=true, description="身份证正面URL")
     */
    public function idcardocr()
    {
        $img = $this->request->post('img');
        if(!$img){
            $this->error(__('Invalid parameters'));
        }
        $re = Realauth::idcardocr($img);
        if($re && $re['success']==true){
            $this->success(__('Success'),$re);
        }else{
            $this->error('识别出错');
        }
    }
}
