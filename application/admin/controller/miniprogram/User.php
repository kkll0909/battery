<?php

namespace app\admin\controller\miniprogram;
use app\common\controller\Backend;
use addons\miniprogram\library\{WechatService, MessageReply};

/**
 * 微信用户管理
 * @icon fa fa-circle-o
 */
class User extends Backend
{
    /**
     * User模型对象
     * @var \app\admin\model\miniprogram\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\miniprogram\User;
        //内容过滤
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['fauser'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['fauser'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
            $list = collection($list)->toArray();

            foreach ($list as &$row) {
                $row['fauser']['avatar'] = $row['fauser']['avatar'] ? cdnurl($row['fauser']['avatar'], true) : letter_avatar($row['fauser']['nickname']);
            }
            unset($row);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
