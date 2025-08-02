<?php

namespace app\admin\controller\user;

use app\admin\model\maint\Maintenance;
use app\common\controller\Backend;
use app\common\library\Auth;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';
    protected $selectpageFields = 'id,username,nickname,avatar';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User;
        $this->assign('ismaintList',$this->model->getIsmaintList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

    /**
     * 下拉搜索
     */
    public function selectpage()
    {
        //$this->dataLimit = 'auth';
        $this->dataLimitField = 'id';
        return parent::selectpage();
    }

    //获取维修人员并派单
    public function maintuser()
    {
        $bxid=$this->request->param('bxid',0);
        if (!$bxid){
            $this->error(__("Invalid parameters"));
        }
        $this->assign('bxid',$bxid);
        //$maint = new Maintenance();
        $maintInfo = Maintenance::get($bxid);
        $this->assign('mInfo',$maintInfo);
        $maintuList = \app\admin\model\User::where(['ismaint'=>1,'status'=>'normal'])->field('id,username,nickname,ismaint')->select();
        //wxjd维修派单 wxing正在维修 wxzd维修派单 wxwc维修完成
        foreach ($maintuList as $key => $v){
            $maintuList[$key]['wxjd'] = Maintenance::where(['wxuser_id'=>$v['id'],'bxstatus'=>'wxjd'])->count();
            $maintuList[$key]['wxing'] = Maintenance::where(['wxuser_id'=>$v['id'],'bxstatus'=>'wxing'])->count();
            $maintuList[$key]['wxwc'] = Maintenance::where(['wxuser_id'=>$v['id'],'bxstatus'=>'wxwc'])->count();
            $maintuList[$key]['wxzd'] = Maintenance::where(['wxuser_id'=>$v['id'],'bxstatus'=>'wxzd'])->count();
        }
        //var_dump($maintuList);exit;
        $this->assign('wxuser_id',$maintInfo->wxuser_id);
        $this->assign('maintuList',$maintuList);
        if ($this->request->isPost()){
            $this->token();
            if($maintInfo->bxstatus!='wxup'){
                $this->error('不可派单','',[
                    'callback' => 'parent.location.reload();parent.layer.closeAll();'
                ]);
            }
            $uid = $this->request->param('uid',0);
            if (!$uid){
                $this->error(__("Invalid parameters"));
            }
            $maintInfo->wxuser_id = $uid;
            $maintInfo->bxstatus = 'wxzd';
            $maintInfo->save();
            $this->success('派单成功','',[
                'callback' => 'parent.location.reload();parent.layer.closeAll();'
            ]);
        }
        return $this->view->fetch();
    }

}
