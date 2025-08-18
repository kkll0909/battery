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
 * 订单子管理
 *
 * @icon fa fa-circle-o
 */
class Cgordersub extends Backend
{

    /**
     * Cgordersub模型对象
     * @var \app\admin\model\orders\Cgordersub
     */
    protected $model = null;
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\orders\Cgordersub;
        $this->assign('admin_id',$this->auth->id);
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
        $this->dataLimit = false;
        $cgid = $this->request->get('cgid',0);
        $where2=[];
        if ($cgid){$where2=['oid'=>$cgid];}
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->where($where)
                ->where($where2)
                ->order($sort, $order)
                ->paginate($limit)->each(function ($item, $key) {
                    $item['toid'] = \app\admin\model\orders\Cgorders::where(['id'=>$item['oid']])->value('toid');
                    $item['realname'] = Realauth::where(['user_id'=>$item['toid']])->value('realname');
                    return $item;
                });
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
        $this->dataLimit = false;
        $cgid = $this->request->get('cgid',0);
        if (false === $this->request->isPost()) {
            $cgo = new \app\admin\model\orders\Cgorders();
            $orderno = $cgo->where(['id'=>$cgid])->value('orderno');
            $this->assign('orderno',$orderno);
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
        //$params['orderno'] = "CG".Random::build('unique',10).rand(1000,9999);
        $params['oid'] = $cgid;
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
        $this->dataLimit = false;
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $cgid = $this->request->get('cgid',0);
        $cgo = new \app\admin\model\orders\Cgorders();
        $orderno = $cgo->where(['id'=>$cgid])->value('orderno');
        $this->assign('orderno',$orderno);
        //return $this->view->fetch();
        return parent::edit($ids);
    }

    //解除商家与用户之间的关系
    public function munbind($ids = ""){
        $this->dataLimit = false;
        if ($this->request->isAjax()) {
            $row = $this->model->get($ids);
            $batid = Bat::where(['batno'=>$row['batno']])->value('id');
            $belong = new Belong();
            $belong->save(['iszt'=>'unbind'],['batid'=>$batid,'belongtype'=>'user']);
            $this->success('解除所有绑定关系');
        }else{
            $this->error('操作失败');
        }
    }
}
