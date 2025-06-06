<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 首页接口
 * @ApiInternal
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     * @ApiInternal
     */
    public function index()
    {
        $this->success('请求成功');
    }
}
