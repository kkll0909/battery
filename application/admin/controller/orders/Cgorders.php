<?php

namespace app\admin\controller\orders;

use app\admin\model\batmanage\Bat;
use app\admin\model\batmanage\Belong;
use app\admin\model\user\Realauth;
use app\common\controller\Backend;
use fast\Random;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 订单管理
 *
 * @icon fa fa-circle-o
 */
class Cgorders extends Backend
{

    /**
     * Cgorders模型对象
     * @var \app\admin\model\orders\Cgorders
     */
    protected $model = null;
    protected $batsum = 0;
    protected $bbatsum = 0;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\orders\Cgorders;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

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
                ->with(['fromadmin','touser'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit)->each(function ($item,$index){
                    $item['totalsum'] = \app\admin\model\orders\Orderpay::where(['oid'=>$item['id'],'paysum'=>['>',0]])->count();
                    $item['yjpaysum'] = \app\admin\model\orders\Orderpay::where(['oid'=>$item['id'],'paysum'=>['>',0],'paystatus'=>'pay'])->count();
                    $item['yjpaym'] = \app\admin\model\orders\Orderpay::where(['oid'=>$item['id'],'paysum'=>['>',0],'paystatus'=>'pay'])->sum('paymoney');
                    $item['qs'] = $item['yjpaysum'].'/'.$item['totalsum'];
                    $item['yjpaym'] = number_format($item['yjpaym'],2);
                    $item['realname'] = Realauth::where(['user_id'=>$item['touser']['id']])->value('realname');
                    //分配电池数量
                    $item['likebatsum'] = \app\admin\model\orders\Cgordersub::where(['oid'=>$item['id'],'batno'=>['<>','']])->count();
                    //用户绑定数量
                    $likebatlist = \app\admin\model\orders\Cgordersub::where(['oid'=>$item['id']])->select();
                    $i=0;
                    foreach ($likebatlist as $k=>$v){
                        $batid = Bat::where(['batno'=>$v['batno']])->value('id');
                        if($batid){
                            $beInfo = Belong::where(['batid'=>$batid,'belongtype'=>'user','isuse'=>'self','iszt'=>'ok'])->count();
                            if($beInfo){
                                $i++;
                            }
                        }

                    }
                    $item['bindbatsum'] = $i;
//                    $this->batsum += $item['likebatsum'];
//                    $this->bbatsum += $item['bindbatsum'];
                    return $item;
                });

            //分配电池数量
            $this->batsum = \app\admin\model\orders\Cgordersub::where(['batno'=>['<>','']])->count();
            //用户绑定数量
            $likebatlist = \app\admin\model\orders\Cgordersub::select();
            $i=0;
            foreach ($likebatlist as $k=>$v){
                $batid = Bat::where(['batno'=>$v['batno']])->value('id');
                if($batid){
                    $beInfo = Belong::where(['batid'=>$batid,'belongtype'=>'user','isuse'=>'self','iszt'=>'ok'])->count();
                    if($beInfo){
                        $i++;
                    }
                }

            }
            $this->bbatsum = $i;
            $result = array("total" => $list->total(), "rows" => $list->items(),'extend'=>['totallbs'=>$this->batsum,'totalbbs'=>$this->bbatsum]);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $params['orderno'] = "CG".Random::build('unique',10).rand(1000,9999);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
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
        return parent::edit($ids);
    }
}
