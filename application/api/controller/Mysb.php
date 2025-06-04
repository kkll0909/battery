<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 设备接口
 */
class Mysb extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

}