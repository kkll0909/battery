<?php
namespace app\api\controller;

use addons\epay\library\Service;
use app\common\controller\Api;
use app\common\model\orders\Cgorderaddr;
use app\common\model\orders\Cgorders;
use app\common\model\orders\Cgordersub;
use app\common\model\orders\Orderpay;
use app\common\model\Useraddr;
use fast\Random;
use think\Db;

/**
 * 订单接口
 */
class Order extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    /**
     * 下单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="shopid", type="int", required=true, description="门店ID")
     * @ApiParams (name="preid", type="int", required=true, description="产品ID")
     * @ApiParams (name="type", type="string", required=false, description="租zp,买buy")
     * @ApiParams (name="zptype", type="string", required=false, description="当为租时,租期为m(月),j(季),n(年)")
     * @ApiParams (name="zptypesum", type="int", required=false, description="租期周期单位,默认1")
     * @ApiParams (name="presum", type="int", required=false, description="产品数量,默认1")
     */
    public function porder()
    {
        $userid = $this->auth->id;
        $uuInfo = \app\common\model\User::get($userid);
        if($uuInfo['isauth']!=1){$this->error(__('Realname does not auth'));}
        $shopid = $this->request->param('shopid');
        $preid = $this->request->param('preid');
        $type = empty($this->request->param('type'))?'zp':$this->request->param('type');
        $zptype = empty($this->request->param('zptype'))?$type=='zp'?'m':'a':$this->request->param('zptype');
        $zptypesum = empty($this->request->param('zptypesum'))?1:$this->request->param('zptypesum');
        $presum = empty($this->request->param('presum'))?1:$this->request->param('presum');
        if(!$shopid || !$preid){
            $this->error(__('Invalid parameters'));
        }
        //判断门店ID
        $shop = new \app\common\model\shop\Shop();
        $shopInfo = $shop->where(['id'=>$shopid,'status'=>'show'])->find();
        if (!$shopInfo){
            $this->error(__('Shop does not exist'));
        }
        //判断产品ID
        $pre = new \app\common\model\shop\Shoplist();
        $preInfo = $pre->where(['id'=>$preid,'status'=>'1'])->find();
        if (!$preInfo){
            $this->error(__('Product does not exist'));
        }

        if($type=='buy'){
            $money = $preInfo['buymoney']*$presum;
            $m = 0;
            $preInfo['deposit'] = 0;
        }else{
            switch ($zptype){
                case 'm':
                    $zk = 0;
                    $m = 1;
                    break;
                case 'j':
                    $zk = $preInfo['jzk'];
                    $m = 3;
                    break;
                case 'n':
                    $zk = $preInfo['nzk'];
                    $m = 12;
                    break;
            }
            $preInfo['zpmoney'] = $zk==0?$preInfo['zpmoney']:$preInfo['zpmoney']*$zk/10;
            $money = $preInfo['zpmoney']*$presum*$zptypesum*$m;
            $month = $zptypesum;
        }
        //事务处理
        Db::startTrans();
        try {
            //主订单
            //$orderD['orderno'] = "OR".Random::build('unique',10).rand(1000,9999);
            $orderD['orderno'] = $shopInfo['admin_id'].'O'.date('ymdHis').rand(1000,9999);
            $orderD['shopid'] = $shopid;
            $orderD['fromid'] = $shopInfo['admin_id'];
            $orderD['admin_id'] = $shopInfo['admin_id'];
            $orderD['toid'] = $userid;
            $orderD['payway'] = 'multiple';
            $orderD['type'] = $type;
            $orderD['paytype'] = $zptype;
            $orderD['sum'] = $presum;
            $orderD['paytypesum'] = $type=='buy'?0:1;
            $orderD['monay'] = $money;
            $orderD['deposit'] = $preInfo['deposit'];
            $orderD['status'] = 'nopay';
            $orderD['stime'] = time();
            $cgorder = Cgorders::create($orderD);
            //子订单
            for ($i=0;$i<$presum;$i++){
                $orderSubD['preid'] = $preInfo['id'];
                $orderSubD['oid'] = $cgorder->id;
                $orderSubD['shopid'] = $shopid;
//                $orderSubD['sum'] = $presum;
//                $orderSubD['price'] = "";
                $orderSubD['totalprice'] = $money;
                $orderSubD['status'] = "show";
                Cgordersub::create($orderSubD);
            }

            //分期
            if($type=='zp'){
                if($preInfo['usetype']=='payuse'){
                    $paydate = date('Y-m-d');
                }else{
                    $paydate = date('Y-m-d',strtotime("+1 month"));
                }
                $paydatestr = strtotime($paydate);
                $paylist = [];
                $t=0;
                $etime = 0;
                for ($i=0;$i<=$month;$i++){
                    $paylist[] = [
                        'userid'=>$userid,
                        'oid'=>$orderSubD['oid'],
                        'isy'=>$i==0?0:1,
                        'paymoney'=>$i==0?$preInfo['deposit']:$preInfo['zpmoney']*$m*$presum,
                        'paysum'=>$i,
                        'paydate'=>$i==0?date('Y-m-d'):($i==1?$paydate:date("Y-m-d", strtotime("+{$t} month",$paydatestr))),
                        'paystatus'=>'nopay'
                    ];
                    if($i>0){$t+=$m;}
                    if($i==$month){
                        $etime = strtotime("+{$t} month",$paydatestr);
                    }
                }
                Cgorders::update(['etime'=>$etime],['id'=>$orderSubD['oid']]);
                $pay = new Orderpay();
                $pay->saveAll($paylist);
            }
            $msgtype = $type=='buy'?"购买":"租赁";
            \app\common\library\Lib\Message::sendInMessage('下单',"{$msgtype}单：{$msgtype}{$presum}设备，请尽快完成支付.",'user','sys',$userid);
            \app\common\library\Lib\Message::sendInMessage('下单',"{$msgtype}单：{$msgtype}{$presum}设备，用户在你门店已经下单.",'member','sys',$shopInfo['admin_id']);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->error($e->getMessage());
        }
        $out['orderno'] = $orderD['orderno'];
        $this->success(__('success'),$out);
    }

    /**
     * 确认订单
     *
     * @ApiMethod (POST)
     * @ApiParams (name="orderno", type="string", required=true, description="订单号")
     */
    public function confirmOrder()
    {
        $orderno = $this->request->param('orderno');
        if(!$orderno){
            $this->error(__('Invalid parameters'));
        }
        $cgorder = new Cgorders();
        $cgordersub = new Cgordersub();
        $pay = new Orderpay();
        $list = $cgorder->where(['orderno'=>$orderno])->find();
        if(empty($list)){
            $this->error(__('Order does not exist'));
        }
        $list['ordersub'] = $cgordersub->where(['oid'=>$list['id']])->find();
        $list['paylist'] = $pay->where(['oid'=>$list['id']])->select();
        $this->success(__('success'),$list);
    }

    /**
     * 押金或商品支付
     *
     * @ApiMethod (POST)
     * @ApiParams (name="orderno", type="string", required=true, description="订单号")
     * @ApiParams (name="addrid", type="string", required=true, description="地址ID")
     * @ApiParams (name="type", type="string", required=true, description="快递express,自提self")
     */
    public function payorder()
    {
        $orderno = $this->request->param('orderno');
        $addrid = $this->request->param('addrid');
        $type = $this->request->param('type');
        if(!$orderno){
            $this->error(__('Invalid parameters'));
        }
        $cgorder = new Cgorders();
        $pay = new Orderpay();
        $list = $cgorder->where(['orderno'=>$orderno])->find();
        if(empty($list)){
            $this->error(__('Order does not exist'));
        }
        $addrinfo = Useraddr::get($addrid);
        $addr = new Cgorderaddr();
        $addrD['oid']=$list['id'];
        $addrD['address']=$addrinfo['address']??'';
        $addrD['tel']=$addrinfo['tel']??'';
        $addrD['name']=$addrinfo['name']??'';
        $addrD['type']=$type;
        $addr->save($addrD);
        if($list['type']=='zp'){
            if($list['deposit']==0){
                $cgorder->save(['status'=>'pay'],['id'=>$list['id']]);
                $pay->save(['paystatus'=>'pay'],['oid'=>$list['id'],'isy'=>0]);
            }else{
                $cgorder->save(['status'=>'pay'],['id'=>$list['id']]);
                $pay->save(['paystatus'=>'pay'],['oid'=>$list['id'],'isy'=>0]);
            }
        }else{
            $miniu = new \app\admin\model\miniprogram\User();
            $openid = $miniu->where(['user_id'=>$this->auth->id])->value('openid');
            $params = [
                'amount'=>$list['monay'],
                'orderid'=>$list['orderno'],
                'type'=>"wechat",
                'title'=>"购买产品",
                'notifyurl'=>"https://admin.yuanshc.com/addons/epay/index/notifyx/paytype/wechat",
                'returnurl'=>"https://admin.yuanshc.com/",
                'method'=>"miniapp",
                'openid'=>$openid,
            ];
            $re = Service::submitOrder($params);
            $this->success(__('success'),$re);
        }

        $this->success(__('success'));
    }

    /**
     * 分期支付
     *
     * @ApiMethod (POST)
     * @ApiParams (name="orderid", type="string", required=true, description="订单ID")
     * @ApiParams (name="ordersubid", type="string", required=true, description="支付订单ID")
     */
    public function wxpay()
    {
        $orderid = $this->request->param('orderid');
        $ordersubid = $this->request->param('ordersubid');
        if(!$orderid || !$ordersubid){
            $this->error(__('Invalid parameters'));
        }
        //$cgorder = new Cgorders();
        $pay = new Orderpay();
        //$cgoinfo = $cgorder->where(['id'=>$orderid])->find();
        //$cgosub = new Cgordersub();
        $payInfo = $pay->where(['oid'=>$orderid,'id'=>$ordersubid])->find();
        if (!$payInfo){
            $this->error(__('Order does not exist'));
        }
        $suborno = $orderid.'O'.$ordersubid.'R'.Random::alnum(10);
        $pay->save(['payor'=>$suborno],['id'=>$ordersubid]);
        $miniu = new \app\admin\model\miniprogram\User();
        $openid = $miniu->where(['user_id'=>$this->auth->id])->value('openid');
        $params = [
            'amount'=>$payInfo['paymoney'],
            'orderid'=>$suborno,
            'type'=>"wechat",
            'title'=>"租金第{$payInfo['paysum']}期",
            'notifyurl'=>"https://admin.yuanshc.com/addons/epay/index/notifyx/paytype/wechat",
            'returnurl'=>"https://admin.yuanshc.com/",
            'method'=>"miniapp",
            'openid'=>$openid,
        ];
        $re = Service::submitOrder($params);
        $this->success(__('success'),$re);
    }

    /**
     * 订单删除
     *
     * @ApiMethod (POST)
     * @ApiParams (name="orderid", type="string", required=true, description="订单ID")
     */
    public function delorder()
    {
        $orderid = $this->request->param('orderid');
        if(!$orderid){
            $this->error(__('Invalid parameters'));
        }
        $user_id = $this->auth->id;
        $cgorder = new Cgorders();
        $w = ['id'=>$orderid,'toid'=>$user_id];
        $info = $cgorder->where($w)->find();
        if(!$info){
            $this->error(__('Order does not exist'));
        }else{
            if($info['status']=='nopay'){
                $cgorder->where($w)->delete();
            }else{
                $this->error(__('Order pay'));
            }
        }
        $this->success(__('success'));
    }

    /**
     * 租赁续租--数据确认
     *
     * @ApiMethod (POST)
     * @ApiParams (name="orderid", type="string", required=true, description="订单ID")
     */
    public function renewalConfirm()
    {
        $orderid = $this->request->param('orderid');
        if(!$orderid){
            $this->error(__('Invalid parameters'));
        }
        $user_id = $this->auth->id;
//        $user_id = 5;
        $cgorder = new Cgorders();
        $payorder = new Orderpay();
        $w = ['id'=>$orderid,'toid'=>$user_id];
        $info = $cgorder->where($w)->find();
        $payinfoe = $payorder->where(['oid'=>$orderid,'userid'=>$user_id,'isy'=>1])->order('id desc')->find();
        switch ($info['paytype']){
            case 'm':
                $m = 1;
                break;
            case 'j':
                $m = 3;
                break;
            case 'n':
                $m = 12;
                break;
        }
        $smm = $m * $info['paytypesum'];
        $emm = $m * $info['paytypesum']*$payinfoe['paysum'];
        $out['paytype'] = $info['paytype'];
        $out['paytypesum'] = $info['paytypesum'];
        $out['money'] = $info['monay'];
        $out['deposit'] = $info['deposit'];
        $out['oldstime'] = date('Y-m-d',$info['stime']);
        $out['oldetime'] = date('Y-m-d',$info['etime']);
        $paydate = strtotime("+{$smm} month",strtotime($payinfoe['paydate']));
        if($paydate<=time()){
            $paydate = date('Y-m-d');
        }else{
            $paydate = date('Y-m-d',$paydate);
        }
        $out['newstime'] = $paydate;
        $out['newetime'] = date('Y-m-d',strtotime("+{$emm} month",strtotime($paydate)));
        $out['newlist'] = [];
        for ($i=1;$i<=$payinfoe['paysum'];$i++){
            $imm = $m*$i;
            $out['newlist'][] = [
                'userid'=>$user_id,
                'oid'=>$orderid,
                'isy'=>1,
                'paymoney'=>$payinfoe['paymoney'],
                'paysum'=>$payinfoe['paysum']+$i,
                'paydate'=>date('Y-m-d',strtotime("+{$imm} month",strtotime($payinfoe['paydate']))),
                'paystatus'=>'nopay',
            ];
        }
        $this->success(__('success'),$out);
    }

    /**
     * 租赁续租--提交
     *
     * @ApiMethod (POST)
     * @ApiParams (name="orderid", type="string", required=true, description="订单ID")
     */
    public function renewalSubmit()
    {
        $orderid = $this->request->param('orderid');
        if(!$orderid){
            $this->error(__('Invalid parameters'));
        }
        $user_id = $this->auth->id;
//        $user_id = 5;
        $cgorder = new Cgorders();
        $payorder = new Orderpay();
        $w = ['id'=>$orderid,'toid'=>$user_id];
        $info = $cgorder->where($w)->find();
        $payinfoe = $payorder->where(['oid'=>$orderid,'userid'=>$user_id,'isy'=>1])->order('id desc')->find();
        switch ($info['paytype']){
            case 'm':
                $m = 1;
                break;
            case 'j':
                $m = 3;
                break;
            case 'n':
                $m = 12;
                break;
        }
        $smm = $m * $info['paytypesum'];
        $emm = $m * $info['paytypesum']*$payinfoe['paysum'];
        $out['paytype'] = $info['paytype'];
        $out['paytypesum'] = $info['paytypesum'];
        $out['money'] = $info['monay'];
        $out['deposit'] = $info['deposit'];
        $out['oldstime'] = date('Y-m-d',$info['stime']);
        $out['oldetime'] = date('Y-m-d',$info['etime']);
        $paydate = strtotime("+{$smm} month",strtotime($payinfoe['paydate']));
        if($paydate<=time()){
            $paydate = date('Y-m-d');
        }else{
            $paydate = date('Y-m-d',$paydate);
        }
        $out['newstime'] = $paydate;
        $out['newetime'] = date('Y-m-d',strtotime("+{$emm} month",strtotime($paydate)));
        Db::startTrans();
        try {
            $cgorder->save(['stime'=>strtotime($out['newstime']),'etime'=>strtotime($out['newetime'])],['id'=>$orderid]);
            $newlist = [];
            for ($i=1;$i<=$payinfoe['paysum'];$i++){
                $imm = $m*$i;
                $newlist[] = [
                    'userid'=>$user_id,
                    'oid'=>$orderid,
                    'isy'=>1,
                    'paymoney'=>$payinfoe['paymoney'],
                    'paysum'=>$payinfoe['paysum']+$i,
                    'paydate'=>date('Y-m-d',strtotime("+{$imm} month",strtotime($payinfoe['paydate']))),
                    'paystatus'=>'nopay',
                ];
            }
            $payorder->insertAll($newlist);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            $this->error(__('续租失败'));
        }
        $this->success(__('success'),$newlist);
    }

}