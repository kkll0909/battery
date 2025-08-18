<?php

namespace app\common\library\Lib;

use think\Log;

class Message
{
    //消息
    //$sendto = site,mobile,wx
    public static function sendInMessage($title,$desc,$totype='user',$type='sys',$userid='0',$sendto='site'){
        $sendto = explode(',',$sendto);
        foreach ($sendto as $k=>$v){
            if($v=='site'){
                $in['title'] = $title;
                $in['desc'] = $desc;
                $in['totype'] = $totype;
                $in['type'] = $type;
                $in['userid'] = $userid;
                $in['ctime'] = time();
                $in['status'] = 'show';
                \app\admin\model\message\Message::create($in);
            }elseif($v=='mobile'){
                Log::write('手机消息暂不支持');
            }elseif($v=='wx'){
                Log::write('微信消息暂不支持');
            }
        }
    }
}