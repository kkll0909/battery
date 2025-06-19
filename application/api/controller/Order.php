<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\orders\Cgorders;
use fast\Random;

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
     * @ApiParams (name="paytype", type="string", required=false, description="当为租时,付期为m(月),j(季),n(年),当为买时周期为a")
     * @ApiParams (name="paytypesum", type="int", required=false, description="付款周期单位,默认1")
     * @ApiParams (name="presum", type="int", required=false, description="产品数量,默认1")
     */
    public function porder()
    {
        $userid = $this->auth->id;
        $shopid = $this->request->param('shopid');
        $preid = $this->request->param('preid');
        $type = empty($this->request->param('type'))?'zp':$this->request->param('type');
        $paytype = empty($this->request->param('paytype'))?$type=='zp'?'m':'a':$this->request->param('paytype');
        $paytypesum = empty($this->request->param('paytypesum'))?1:$this->request->param('paytypesum');
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
        }else{
            switch ($paytype){
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
            $money = $preInfo['buymoney']*$presum*$paytypesum*$m;
        }

        $orderD['orderno'] = "OR".Random::build('unique',10).rand(1000,9999);
        $orderD['shopid'] = $shopid;
        $orderD['fromid'] = $shopInfo['admin_id'];
        $orderD['toid'] = $userid;
        $orderD['payway'] = 'multiple';
        $orderD['type'] = $type;
        $orderD['paytype'] = $paytype;
        $orderD['sum'] = $presum;
        $orderD['paytypesum'] = $type=='buy'?0:$paytypesum;
        $orderD['monay'] = $money;
        $orderD['status'] = 'nopay';
    }
}