<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Lib\Realauth;

class Index extends Frontend
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    public function aa()
    {
        $name = '罗青钦';
        $idcard = '332501198212180217';
        $re = Realauth::realauth($name,$idcard);
        dump($re);
        if($re['result']==0){
            dump(1);
        }
    }

}
