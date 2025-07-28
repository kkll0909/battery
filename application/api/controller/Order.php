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
        }else{
            switch ($zptype){
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
            $money = $preInfo['zpmoney']*$presum*$zptypesum*$m;
            switch ($preInfo['paytype']){
                case 'm':
                    $mm = 1;
                    break;
                case 'j':
                    $mm = 3;
                    break;
                case 'n':
                    $mm = 12;
                    break;
            }
            $month = $m * $zptypesum;
        }
        //事务处理
        Db::startTrans();
        try {
            //主订单
            $orderD['orderno'] = "OR".Random::build('unique',10).rand(1000,9999);
            $orderD['shopid'] = $shopid;
            $orderD['fromid'] = $shopInfo['admin_id'];
            $orderD['toid'] = $userid;
            $orderD['payway'] = 'multiple';
            $orderD['type'] = $type;
            $orderD['paytype'] = $preInfo['paytype'];
            $orderD['sum'] = $presum;
            $orderD['paytypesum'] = $type=='buy'?0:1;
            $orderD['monay'] = $money;
            $orderD['deposit'] = $preInfo['deposit'];
            $orderD['status'] = 'nopay';
            $cgorder = Cgorders::create($orderD);
            //子订单
            $orderSubD['preid'] = $preInfo['id'];
            $orderSubD['oid'] = $cgorder->id;
            $orderSubD['sum'] = $presum;
            //$orderSubD['price'] = "";
            $orderSubD['totalprice'] = $money;
            $orderSubD['status'] = "show";
            Cgordersub::create($orderSubD);
            //分期
            if($type=='zp'){
                if($preInfo['usetype']=='payuse'){
                    $paydate = date('Y-m-d');
                }else{
                    $paydate = date('Y-m-d',strtotime("+1 month"));
                }
                $paylist = [];
                for ($i=0;$i<=$month;$i++){
                    $paylist[] = [
                        'userid'=>$userid,
                        'oid'=>$orderSubD['oid'],
                        'isy'=>$i==0?0:1,
                        'paymoney'=>$i==0?$preInfo['deposit']:$preInfo['zpmoney'],
                        'paysum'=>$month,
                        'paydate'=>$i==0?date('Y-m-d'):date("Y-m-d", strtotime("+{$i}} month", strtotime($paydate))),
                        'paystatus'=>'nopay'
                    ];
                }
                $pay = new Orderpay();
                $pay->saveAll($paylist);
            }
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
        $addrD['address']=$addrinfo['address'];
        $addrD['tel']=$addrinfo['tel'];
        $addrD['name']=$addrinfo['name'];
        $addrD['type']=$type;
        $addr->save($addrD);
        if($list['deposit']==0){
            $cgorder->save(['status'=>'pay'],['id'=>$list['id']]);
            $pay->save(['paystatus'=>'pay'],['oid'=>$list['id'],'isy'=>0]);
        }else{
            $cgorder->save(['status'=>'pay'],['id'=>$list['id']]);
            $pay->save(['paystatus'=>'pay'],['oid'=>$list['id'],'isy'=>0]);
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

}