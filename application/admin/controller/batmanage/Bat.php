<?php

namespace app\admin\controller\batmanage;

use app\common\controller\Backend;

/**
 * 电池管理
 *
 * @icon fa fa-circle-o
 */
class Bat extends Backend
{

    /**
     * Bat模型对象
     * @var \app\admin\model\batmanage\Bat
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\batmanage\Bat;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("islikeList", $this->model->getIslikeList());
        $this->view->assign("battypeList", $this->model->getBattypeList());
        $this->view->assign("balanceList", $this->model->getBalanceList());
        $this->view->assign("chargedischargeswitchList", $this->model->getChargedischargeswitchList());
        $this->view->assign("mosstatusList", $this->model->getMosstatusList());
        $this->view->assign('admin_id',$this->auth->id);
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
                ->with(['admin','factory'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 下拉搜索
     */
    public function selectpage()
    {
        //$this->dataLimit = 'auth';
        //$this->dataLimitField = 'id';
        return parent::selectpage();
    }

    //电池充放电配置(指令)
    public function sendcf()
    {
        //2491000542
        $deviceid = $this->request->param('deviceid','');
        $status = $this->request->param('status',1);
        $commandt = "charge_control";
        $params = ['status'=>$status];
        $params = json_encode($params);
        $c = "php /www/wwwroot/bat/public/index.php index/mqtt/sendc/deviceid/{$deviceid}/commandt/{$commandt}/params/{$params}";
        $re = exec($c);
        $this->success('指令下发成功，会自动更新电池数据');
    }
}
