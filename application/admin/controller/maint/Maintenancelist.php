<?php

namespace app\admin\controller\maint;

use app\common\controller\Backend;

/**
 * 维修记录
 *
 * @icon fa fa-circle-o
 */
class Maintenancelist extends Backend
{

    /**
     * Maintenancelist模型对象
     * @var \app\admin\model\maint\Maintenancelist
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\maint\Maintenancelist;

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
        $bxid = $this->request->get('bxid',0);
        $where2=[];
        if (!$bxid){
            $this->error(__('Parameter %s can not be empty', ''));
        }else{
            $where2=['maintid'=>$bxid];
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
}
