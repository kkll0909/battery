<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 门店接口
 */
class Shop extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

}