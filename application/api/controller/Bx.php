<?php

namespace app\api\controller;

use app\admin\model\maint\Maintenance;
use app\admin\model\maint\Maintenancelist;
use app\common\controller\Api;
use app\common\model\batmanage\Bat;

/**
 * 维修接口
 */
class Bx extends Api
{

    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    /**
     * 上报故障
     *
     * @ApiMethod (POST)
     * @ApiParams (name="batno", type="string", required=true, description="设备号")
     * @ApiParams (name="wxtype", type="string", required=true, description="上报类型:从init初始化接口拿")
     * @ApiParams (name="bximg", type="string", required=true, description="上传图片多图用逗号分开:从init初始化接口拿")
     * @ApiParams (name="bxdesc", type="string", required=true, description="上报谫明")
     */
    public function reporterr()
    {
        $user_id = $this->auth->id;
        $batno = $this->request->param('batno');
        $wxtype = $this->request->param('wxtype');
        $bximg = $this->request->param('bximg');
        $bxdesc = $this->request->param('bxdesc');
        if(!$batno || !$wxtype|| !$bximg|| !$bxdesc){
            $this->error(__('Invalid parameters'));
        }
        $bat = new Bat();
        $batinfo = $bat->where(['batno'=>$batno])->find();
        if (empty($batinfo)){
            $this->error(__('Sb does not exist'));
        }
        $in['admin_id'] = $batinfo['admin_id'];
        $in['user_id'] = $user_id;
        $in['batno'] = $batno;
        $in['wxtype'] = $wxtype;
        $in['bximg'] = $bximg;
        $in['bxdesc'] = $bxdesc;
        $in['bxstatus'] = 'wxup';
        $in['bxtime'] = time();
        $in['orderno'] = date('YmdHis').\fast\Random::alnum(12);

        $maint = new Maintenance();
        $maint->save($in);
        $this->success(__('Success'));
    }

    /**
     * 上报记录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="bxstatus", type="string", required=false, description="默认所有,wxup维修上报,wxjd维修接单,wxing正在维修,wxzd维修派单,wxwc维修完成")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
    */
    public function reportlist()
    {
        $bxstatus = $this->request->post('bxstatus');
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        $userid = $this->auth->id;
        $where['user_id'] = $userid;
        if($bxstatus){
            $where['bxstatus'] = $bxstatus;
        }
        $maint = new Maintenance();
        $list = $maint
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page]);
        $this->success(__('Success'),$list);
    }

    /**
     * 维修记录
     *
     * @ApiMethod (POST)
     * @ApiParams (name="maintid", type="string", required=true, description="上传列表中的ID")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function wxlist()
    {
        $maintid = $this->request->post('maintid');
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        //$userid = $this->auth->id;
        //$where['user_id'] = $userid;
        $where['maintid'] = $maintid;
        $maintL = new Maintenancelist();
        $list = $maintL
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page]);
        $this->success(__('Success'),$list);
    }

    /**
     * 维修人的订单
     * @ApiMethod (POST)
     * @ApiParams (name="bxstatus", type="string", required=false, description="默认所有,wxup维修上报,wxjd维修接单,wxing正在维修,wxzd维修派单,wxwc维修完成")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function wxuserlist()
    {
        $bxstatus = $this->request->post('bxstatus');
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        $userid = $this->auth->id;
        $where['wxuser_id'] = $userid;
        if($bxstatus){
            $where['bxstatus'] = $bxstatus;
        }
        $maint = new Maintenance();
        $list = $maint
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page]);
        $this->success(__('Success'),$list);
    }

    /**
     * 维修人员接单
     * @ApiMethod (POST)
     * @ApiParams (name="bxid", type="int", required=true, description="报修ID")
     */
    public function wxuserjd()
    {
        $bxid = $this->request->post('bxid');
        $wxuserid = $this->auth->id;
        $maint = new Maintenance();
        $maintInfo = $maint->where(['id'=>$bxid,'wxuser_id'=>$wxuserid])->find();
        if(!$maintInfo){
            $this->error(__('Bxorder does not exist'));
        }
        $maint->save(['bxstatus'=>'wxjd'],['id'=>$bxid]);
        $maintList = new Maintenancelist();
        $in['maintid'] = $bxid;
        $in['wxdesc'] = '已经接单';
        $in['wxuser_id'] = $wxuserid;
        $in['wxstatus'] = 'wxjd';
        $in['wxtime'] = time();
        $maintList->save($in);
        $this->success(__('Success'));
    }

    /**
     * 维修人员开始
     * @ApiMethod (POST)
     * @ApiParams (name="bxid", type="int", required=true, description="报修ID")
     */
    public function wxusering()
    {
        $bxid = $this->request->post('bxid');
        $wxuserid = $this->auth->id;
        $maint = new Maintenance();
        $maintInfo = $maint->where(['id'=>$bxid,'wxuser_id'=>$wxuserid])->find();
        if(!$maintInfo){
            $this->error(__('Bxorder does not exist'));
        }
        $maint->save(['bxstatus'=>'wxing'],['id'=>$bxid]);
        $maintList = new Maintenancelist();
        $in['maintid'] = $bxid;
        $in['wxdesc'] = '正在维修中';
        $in['wxuser_id'] = $wxuserid;
        $in['wxstatus'] = 'wxing';
        $in['wxtime'] = time();
        $maintList->save($in);
        $this->success(__('Success'));
    }

    /**
     * 维修人员维修上报
     * @ApiMethod (POST)
     * @ApiParams (name="bxid", type="int", required=true, description="报修ID")
     * @ApiParams (name="wximg", type="string", required=false, description="报修中的图片,多图用逗号隔开")
     * @ApiParams (name="wxvideo", type="string", required=false, description="报修中的视频,多视频用逗号隔开")
     * @ApiParams (name="wxdesc", type="string", required=true, description="报修说明")
     */
    public function wxuserup()
    {
        $bxid = $this->request->post('bxid');
        $wximg = $this->request->post('wximg');
        $wxvideo = $this->request->post('wxvideo');
        $wxdesc = $this->request->post('wxdesc');
        if(!$bxid || !$wxdesc){
            $this->error(__('Invalid parameters'));
        }
        $wxuserid = $this->auth->id;
        $maintList = new Maintenancelist();
        $in['maintid'] = $bxid;
        $in['wximg'] = $wximg;
        $in['wxvideo'] = $wxvideo;
        $in['wxdesc'] = $wxdesc;
        $in['wxuser_id'] = $wxuserid;
        $in['wxtime'] = time();
        $maintList->save($in);
        $this->success(__('Success'));
    }
}