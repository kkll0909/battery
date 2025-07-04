<?php

namespace app\admin\controller\shop;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Shopservice extends Backend
{

    /**
     * Shopservice模型对象
     * @var \app\admin\model\shop\Shopservice
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\shop\Shopservice;
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
        $shopid = $this->request->get('shopid',0);
        $where2=[];
        if (!$shopid){
            $this->error(__('Parameter %s can not be empty', ''));
        }else{
            $where2=['shopid'=>$shopid];
        }
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                //->with(['admin'])
                ->where($where)
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());

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
        $shopid = $this->request->get('shopid',0);
        if (!$shopid){
            $this->error(__('Parameter %s can not be empty', ''));
        }else{
            $shop = new \app\admin\model\shop\Shop();
            $spname=$shop->where(['id'=>$shopid])->value('spname');
            $this->assign('spname',$spname);
        }
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
//        $this->token();
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
//        if(isset($params['sbtype']) && is_array($params['sbtype'])){
//            $params['sbtype'] = implode(',',$params['sbtype']);
//        }else{
//            $params['sbtype'] = '';
//            $this->error(__('Parameter %s can not be empty', ''));
//        }
        $params['shopid'] = $shopid;
//        dump($params);
//        exit;
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


}
