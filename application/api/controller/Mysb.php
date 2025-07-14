<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\batmanage\Bat;
use app\common\model\batmanage\Belong;
use think\Log;

/**
 * 设备接口
 */
class Mysb extends Api
{
    protected $noNeedLogin = ['mySb'];
    protected $noNeedRight = '*';

    /**
     * 添加设备
     * @ApiTitle 当返回的isauth=1时表示可以走申请接口
     * @ApiSummary 当返回的isauth=1时表示可以走申请接口
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     */
    public function addSb()
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
        $beSelfInfo = $belong->where(['batid'=>$batinfo['id'],'isuse'=>'authorize','iszt'=>'ok'])->find();
        if ($beSelfInfo){
            $this->error(__('Sb already authorize'),['isauth'=>0]);
        }
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
        $list = $belong->where(['batid'=>$batinfo['id'],'isuse'=>'authorize','iszt'=>'apply'])->find();
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
        $batinfo = $bat->where(['batno'=>$batno])->find();
        if (empty($batinfo)){
            $this->error(__('Sb does not exist'));
        }
//        $data['batid'] = $batinfo['id'];
//        $data['belongid'] = $this->auth->id;
//        $data['belongtype'] = 'user';
//        $data['isuse'] = 'self';
//        $data['status'] = 'show';
//        $data['stime'] = time();
        $data['iszt'] = 'no';
        $belong = new Belong();
        $belong->save($data,['batid'=>$batinfo['id'],'belongid'=>$this->auth->id]);
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
            ->paginate($pagesize,false,['page'=>$page]);
//        Log::write($belong->getLastSql());
        $this->success(__('success'),$list);
    }
}