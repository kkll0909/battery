<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 培训接口
 *
 */
class Training extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 内容列表
     *
     * @ApiMethod (POST)
     * @ApiParams (name="ntype", type="string", required=false, description="分类:word,video")
     * @ApiParams (name="title", type="string", required=false, description="标题搜索")
     * @ApiParams (name="recommend", type="string", required=false, description="是否推荐1,0")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function index()
    {
        $ntype = $this->request->post('ntype','');
        $title = $this->request->post('title','');
        $recommend = $this->request->post('recommend','');
        $page = $this->request->post('page', 1);
        $pagesize = $this->request->post('pagesize', 20);

        $where[] = ['status', '=', 'show'];
        if ($recommend) {
            $where[] = ['recommend', '=', $recommend];
        }
        if ($ntype) {
            $where[] = ['ntype', '=', $ntype];
        }
        if ($title) {
            $where[] = ['title', 'like', "%{$title}%"];
        }
        $train = new \app\common\model\Training();
        $list = $train
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page]);

        $this->success(__('Success'), $list);
    }

    /**
     * 内容详情
     *
     * @ApiMethod (POST)
     * @ApiParams (name="nid", type="string", required=true, description="文章ID")
     */
    public function notedetail()
    {
        $nid = $this->request->post('nid','');
        if (!$nid) {
            $this->error(__('Invalid parameters'));
        }
        $where[] = ['status', '=', 'show'];
        $where[] = ['id', '=', $nid];
        $train = new \app\common\model\Training();
        $list = $train
            ->where($where)
            ->find();

        $this->success(__('Success'), $list);
    }
}
