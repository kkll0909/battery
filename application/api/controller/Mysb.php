<?php

namespace app\api\controller;

use app\admin\model\user\Realauth;
use app\common\controller\Api;
use app\common\model\batmanage\Bat;
use app\common\model\batmanage\Belong;
use app\common\model\orders\Cgorders;
use app\common\model\orders\Cgordersub;
use app\common\model\orders\Orderpay;
use think\Lang;
use think\Log;

/**
 * 设备接口
 */
class Mysb extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/batmanage/belong.php');
    }
    /**
     * 添加设备
     * @ApiTitle 当返回的isauth=1时表示可以走申请接口
     * @ApiSummary 当返回的isauth=1时表示可以走申请接口
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     */
    public function addSb()
    {
        $uuInfo = \app\common\model\User::get($this->auth->id);
        if($uuInfo['isauth']!=1){$this->error(__('Realname does not auth'));}
        $batno = $this->request->post('batno');
        if(!$batno){
            if(!$batno){
                $this->error(__('Invalid parameters'));
            }
        }
        $bat = new Bat();
        $batinfo = $bat->where(['batno'=>$batno])->find();
        if (empty($batinfo)){
            $this->error(__('Sb does not exist'));
        }
        //判断是否绑定到订单
        $cgsub = new Cgordersub();
        $cgsubinfo = $cgsub->where(['batno'=>$batno])->find();
        if (empty($cgsubinfo)){
            $this->error(__('Sb not bind order'));
        }
        //判断是否已经存在
        $belong = new Belong();
        $beAuthInfo = $belong->where(['batid'=>$batinfo['id'],'isuse'=>'authorize','iszt'=>'ok'])->find();
        if($beAuthInfo && $beAuthInfo['belongid']==$this->auth->id){
            $this->error(__('Sb already authorize give you'),['isauth'=>0]);
        }
        if ($beAuthInfo){
            $this->error(__('Sb already authorize'),['isauth'=>0]);
        }
        $beSelfInfo = $belong->where(['batid'=>$batinfo['id'],'belongtype'=>'user','iszt'=>'ok'])->find();
        if($beSelfInfo && $beSelfInfo['belongid']==$this->auth->id){
            $this->error(__('Sb already bind give you'),['isauth'=>0]);
        }
        if($beSelfInfo){
            $this->error(__('Sb already bind'),['isauth'=>1]);
        }
        //判断绑定人与下单人
        $order = new Cgorders();
        $toid = $order->where(['id'=>$cgsubinfo['oid']])->value('toid');
        if(empty($toid) || $toid !== $this->auth->id){
            $this->error(__('Sb not on your order'));
        }
        $data['admin_id'] = $batinfo['admin_id'];
        $data['batid'] = $batinfo['id'];
        $data['belongid'] = $this->auth->id;
        $data['belongtype'] = 'user';
        $data['isuse'] = 'self';
        $data['status'] = 'show';
        $data['stime'] = time();
        $data['iszt'] = 'ok';

        $belong->save($data);
        $this->success(__('success'));
    }

    /**
     * 提交授权审请
     *
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     */
    public function submitAuth()
    {
        $batno = $this->request->post('batno');
        if(!$batno){
            if(!$batno){
                $this->error(__('Invalid parameters'));
            }
        }
        $bat = new Bat();
        $batinfo = $bat->where(['batno'=>$batno])->find();
        if (empty($batinfo)){
            $this->error(__('Sb does not exist'));
        }
        //判断是否已经存在
        $belong = new Belong();
        $beSelfInfo = $belong->where(['batid'=>$batinfo['id'],'isuse'=>'authorize'])->find();
        if ($beSelfInfo && $beSelfInfo['iszt']=='ok'){
            $this->error(__('Sb already authorize'),['isauth'=>0]);
        }elseif ($beSelfInfo && $beSelfInfo['iszt']=='apply'){
            $this->error(__('Sb already have apply'),['isauth'=>0]);
        }
        $data['admin_id'] = $batinfo['admin_id'];
        $data['batid'] = $batinfo['id'];
        $data['belongid'] = $this->auth->id;
        $data['belongtype'] = 'user';
        $data['isuse'] = 'authorize';
        $data['status'] = 'show';
        $data['stime'] = time();
        $data['iszt'] = 'apply';
        $belong->save($data);
        $this->success(__('success'));
    }

    /**
     * 审请授权列表
     *
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     */
    public function authlist()
    {
        $batno = $this->request->post('batno');
        if(!$batno){
            if(!$batno){
                $this->error(__('Invalid parameters'));
            }
        }
        $bat = new Bat();
        $batinfo = $bat->where(['batno'=>$batno])->find();
        if (empty($batinfo)){
            $this->error(__('Sb does not exist'));
        }
        $belong = new Belong();
        $list = $belong->with('uinfo')->where(['batid'=>$batinfo['id'],'isuse'=>'authorize','iszt'=>'apply'])->find();
        $list['uinfo']['bind_realname'] = Realauth::where(['user_id'=>$list['belongid']])->value('realname');
        $this->success(__('success'),$list);
    }

    /**
     * 确认授权审请
     *
     * @ApiMethod (POST)
     * @ApiParams (name="id", type="string", required=true, description="申请列表的ID")
     * @ApiParams (name="confirm", type="string", required=false, description="确认yes,no")
     */
    public function confirmAuth()
    {
        $id = $this->request->post('id');
        $confirm = $this->request->post('confirm','yes');
        if(!$id){
            $this->error(__('Invalid parameters'));
        }
        $belong = new Belong();
        $beInfo = $belong->where(['id'=>$id,'isuse'=>'authorize','iszt'=>'apply'])->find();
        if(!$beInfo){
            $this->error(__('Apply does not exist'));
        }
        $beSelfInfo = $belong->where(['batid'=>$beInfo['batid'],'isuse'=>'self','iszt'=>'ok','belongid'=>$this->auth->id])->find();
        if (!$beSelfInfo){
            $this->error(__('Not your sb'));
        }
        $cof = $confirm=='yes'?'ok':'no';
        $belong->save(['iszt'=>$cof],['id'=>$id]);
        $this->success(__('success'));
    }

    /**
     * 解除设备
     *
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     */
    public function delSb()
    {
        $batno = $this->request->post('batno');
        if(!$batno){
            if(!$batno){
                $this->error(__('Invalid parameters'));
            }
        }
        $bat = new Bat();
        $batinfo = $bat->where(['batno'=>$batno])->order('id desc')->find();
        if (empty($batinfo)){
            $this->error(__('Sb does not exist'));
        }
//        $data['batid'] = $batinfo['id'];
//        $data['belongid'] = $this->auth->id;
//        $data['belongtype'] = 'user';
//        $data['isuse'] = 'self';
//        $data['status'] = 'show';
//        $data['stime'] = time();
        $data['iszt'] = 'unbind';
        $belong = new Belong();
        $belong->save($data,['batid'=>$batinfo['id'],'isuse'=>'authorize','iszt'=>'ok']);
        $this->success(__('success'));
    }

    /**
     * 我的设备
     *
     * @ApiMethod (POST)
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
    */
    public function mySb()
    {
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        $userid = $this->auth->id;
//        $userid = 2;
        $belong = new Belong();
        $list = $belong
            ->with('bat')
            ->where(['belongid'=>$userid,'iszt'=>'ok','belongtype'=>'user','isuse'=>['in','self,authorize']])
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page])->each(function ($item,$index) use ($userid){
                $info = Belong::where(['batid'=>$item['batid'],'isuse'=>'authorize','iszt'=>['in','apply,ok']])->find();
                if($info && $info['iszt']=='apply'){
                    $item['is_auth_applay'] = 1;
                }elseif($info && $info['iszt']=='ok'){
                    $item['is_auth_applay'] = 2;
                }elseif(empty($info)){
                    $item['is_auth_applay'] = 0;
                }
                //打到对应的订单信息
                $oid = Cgordersub::where(['batno'=>$item['bat']['batno']])->value('oid');
                $shopid = Cgorders::where(['id'=>$oid])->value('shopid');
                if($item['isuse']=='self'){
                    $belongid = Belong::where(['batid'=>$item['batid'],'belongtype'=>'user','isuse'=>'authorize','iszt'=>['in','apply,ok']])->value('belongid');
                    //实名信息
                    $item['bind_realname'] = Realauth::where(['user_id'=>$belongid])->value('realname');
                }else{
                    $belongid = Belong::where(['batid'=>$item['batid'],'belongtype'=>'user','isuse'=>'self','iszt'=>'ok'])->value('belongid');
                    $item['bind_realname'] = Realauth::where(['user_id'=>$belongid])->value('realname');
                }
                $item['shopmobile'] = \app\common\model\shop\Shop::where(['id'=>$shopid])->value('shopmobile');
                $item['shopname'] = \app\common\model\shop\Shop::where(['id'=>$shopid])->value('spname');
                $item['etime'] = Cgorders::where(['id'=>$oid])->value('etime');
                $item['orderid'] = $oid;
                $item['orderpay'] = Orderpay::where(['oid'=>$oid,'userid'=>$userid,'paystatus'=>'nopay'])->order('id asc')->find();

                return $item;
            });
//        Log::write($belong->getLastSql());
        $this->success(__('success'),$list);
    }

    /**
     * 设备轨迹
     *
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     */
    public function trajectory()
    {
        $batno = $this->request->post('batno');
        if(!$batno){
            $this->error(__('Invalid parameters'));
        }
        $bat = new Bat();
        $batinfo = $bat->where(['batno'=>$batno])->order('id desc')->find();
        $batloc = new \app\admin\model\batmanage\Batlocstate();
        $pot = [];
//        $st = date('Y-d-m').' 00:00:00';
//        $et = date('Y-d-m').' 23:59:59';
//        ->whereTime('datet', 'between', [$st, $et]);
        $batlocinfo = $batloc->where(['batid'=>$batinfo['id']])->limit(100)->order('id desc')->select();
        if(!$batlocinfo){
            $this->error('暂无定位数据!','');
        }
        foreach ($batlocinfo as $v){
            $pot[] = [$v['longitude'],$v['latitude']];
        }
//        $this->success('pot',json_encode());
        $this->success(__('success'),$pot);
    }
}