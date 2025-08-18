<?php

namespace app\api\controller;

use app\admin\model\message\Messageread;
use app\common\controller\Api;
use think\Lang;

/**
 * 消息接口
 */
class Message extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/message.php');
    }

    /**
     * 消息
     * @ApiTitle 根据相关内容显示
     * @ApiSummary 根据相关内容显示
     * @ApiMethod (POST)
     * @ApiParams (name="totype", type="string", required=false, description="归属方:默认user")
     * @ApiParams (name="type", type="string", required=false, description="消息类型:默认msg,msg,sys")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function msglist()
    {
        $totype = $this->request->param('totype','user');
        $type = $this->request->param('type','msg');
        $page = $this->request->param('page','1');
        $pagesize = $this->request->param('pagesize','15');
        $user_id = $this->auth->id;
        $list = \app\admin\model\message\Message::where('type',$type)
            ->where('totype',$totype)
            ->where(function($query) use ($user_id) {
                $query->where('userid', 0) // userid=0
                ->whereOr('FIND_IN_SET(:userid, userid)', ['userid' => $user_id]); // 或当前用户在userid字段内
            })
            ->paginate($pagesize,false,['page'=>$page])->each(function($item,$key) use ($user_id){
                $item['isread'] = Messageread::where(['totype'=>$item['totype'],'msgid'=>$item['id'],'userid'=>$user_id])->count();
                return $item;
            });
        $this->success(__('success'),$list);
    }

    /**
     * 消息已读提交
     * @ApiMethod (POST)
     * @ApiParams (name="msgid", type="int", required=true, description="消息ID")
     */
    public function readmsg()
    {
        $msgid = $this->request->param('msgid');
        if(empty($msgid)){
            $this->error(__('Invalid parameters'));
        }
        $userid = $this->auth->id;
        //查询是否存在
        $info = Messageread::where('msgid',$msgid)->where('userid',$userid)->find();
        if($info){
            $this->success(__('已看过'));
        }else{
            $in['msgid'] = $msgid;
            $in['userid'] = $userid;
            $in['totype'] = 'user';
            $in['createtime'] = time();
            Messageread::create($in);
            $this->success(__('success'));
        }
    }

    /**
     * 消息未读统计
     * @ApiMethod (POST)
     * @ApiParams (name="totype", type="string", required=false, description="归属方:默认user")
     */
    public function msgnoreadsum(){
        $totype = $this->request->param('totype','user');
        $user_id = $this->auth->id;
        $msgtotal = \app\admin\model\message\Message::where('totype',$totype)
            ->where('type','msg')
            ->where(function($query) use ($user_id) {
                $query->where('userid', 0) // userid=0
                ->whereOr('FIND_IN_SET(:userid, userid)', ['userid' => $user_id]); // 或当前用户在userid字段内
            })
            ->count();
        $systotal = \app\admin\model\message\Message::where('totype',$totype)
            ->where('type','sys')
            ->where(function($query) use ($user_id) {
                $query->where('userid', 0) // userid=0
                ->whereOr('FIND_IN_SET(:userid, userid)', ['userid' => $user_id]); // 或当前用户在userid字段内
            })
            ->count();
        $msgreadsum = \app\admin\model\message\Messageread::where('totype',$totype)->where('type','msg')->where('userid',$user_id)->count();
        $sysreadsum = \app\admin\model\message\Messageread::where('totype',$totype)->where('type','sys')->where('userid',$user_id)->count();
        $this->success(__('success'),['msgnoreadsum'=>$msgtotal-$msgreadsum,'sysnoreadsum'=>$systotal-$sysreadsum]);
    }
}