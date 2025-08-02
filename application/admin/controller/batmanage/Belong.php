<?php

namespace app\admin\controller\batmanage;

use app\admin\model\Admin;
use app\admin\model\User;
use app\common\controller\Backend;

/**
 * 归属关系
 *
 * @icon fa fa-circle-o
 */
class Belong extends Backend
{

    /**
     * Belong模型对象
     * @var \app\admin\model\batmanage\Belong
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\batmanage\Belong;
        $this->view->assign("statusList", $this->model->getIsztList());
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
        $batid = $this->request->get('batid',0);
        $where2=[];
        if (!$batid){
            $this->error(__('Parameter %s can not be empty', ''));
        }else{
            $where2=['batid'=>$batid];
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
                ->paginate($limit)->each(function ($item,$index){
                    if($item['belongtype']=='manage'){
                        $admin = new Admin();
                        $item['nickname'] = $admin->where(['id'=>$item['belongid']])->value('nickname');
                    }elseif($item['belongtype']=='user'){
                        $admin = new User();
                        $item['nickname'] = $admin->where(['id'=>$item['belongid']])->value('nickname');
                    }
                    return $item;
                });
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
