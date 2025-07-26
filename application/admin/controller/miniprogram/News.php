<?php

namespace app\admin\controller\miniprogram;
use app\common\controller\Backend;

/**
 * 图文消息管理管理
 *
 * @icon fa fa-circle-o
 */
class News extends Backend
{

    /**
     * News模型对象
     * @var \app\admin\model\miniprogram\News
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\miniprogram\News;
        $this->view->assign("statusList", $this->model->getStatusList());
        //开启模型验证
        $this->modelValidate = true;
        //内容过滤
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
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
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $row) {
                $row->visible(['id', 'title', 'pic', 'url', 'status']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());
            return json($result);
        }
        return $this->view->fetch();
    }

}