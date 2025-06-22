<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\batmanage\Bat;
use app\common\model\batmanage\Belong;

/**
 * 设备接口
 */
class Mysb extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * 添加设备
     *
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
        $data['batid'] = $batinfo['id'];
        $data['belongid'] = $this->auth->id;
        $data['belongtype'] = 'user';
        $data['isuse'] = 'self';
        $data['status'] = 'show';
        $data['stime'] = time();
        $data['iszt'] = 'ok';
        $belong = new Belong();
        $belong->save($data);
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
        $belong = new Belong();
        $list = $belong
            ->with('bat')
            ->where(['belongid'=>$userid,'iszt'=>['!=','no']])
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page]);
        $this->success(__('success'),$list);
    }
}