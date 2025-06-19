<?php

namespace app\api\controller;

use app\common\model\shop\Shoplike;
use app\common\controller\Api;
use think\Lang;

/**
 * 门店接口
 */
class Shop extends Api
{
    protected $noNeedLogin = ['shoplist','shopdetail','prelist','servicelist'];
    protected $noNeedRight = '*';

    /**
     * 门店列表
     *
     * @ApiMethod (POST)
     * @ApiParams (name="lng", type="string", required=true, description="经度")
     * @ApiParams (name="lat", type="string", required=true, description="纬度")
     * @ApiParams (name="shopname", type="string", required=false, description="门店名称")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
     */
    public function shoplist()
    {
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/shop/shop.php');
        $lng = $this->request->param('lng');
        $lat = $this->request->param('lat');
        $shopname = $this->request->param('shopname');
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?20:$this->request->post('pagesize');
        if (!$lng || !$lat) {
            $this->error(__('Invalid parameters'));
        }
        $where = [];
        if ($shopname) {
            $where[] = ['shopname', 'like', "%{$shopname}%"];
        }
        $shop = new \app\common\model\shop\Shop();
        $list = $shop
            ->where($where)
            ->where(['status'=>'show'])
            ->field('*, ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((' . $lat . ' * PI() / 180 - splat * PI() / 180) / 2),2) + COS(' . $lat . ' * PI() / 180) * COS(splat * PI() / 180) * POW(SIN((' . $lng . ' * PI() / 180 - splng * PI() / 180) / 2),2))) * 1000) AS distance')
            ->order('distance ASC')
            ->paginate($pagesize,false,['page'=>$page])->each(function ($item,$index){
                $spimgs = explode(',',$item['spimgs']);
                foreach ($spimgs as $v){
                    $spimglist[] = cdnurl($v,true);
                }
                $item['spimgs'] = $spimglist;
                //计算评分
                $likeObj = new Shoplike();
                $likesum = $likeObj->where(['shopid'=>$item['id'],'type'=>'like'])->sum('score');
                $item['likecount'] = $likeObj->where(['shopid'=>$item['id'],'type'=>'like'])->count();
                $item['like'] = empty($item['likecount']) ? 0 :  number_format($likesum / $item['likecount'],1);
                return $item;
            });
        
        $this->success(__('Success'), $list);
    }

    /**
     * 门店详情
     *
     * @ApiMethod (POST)
     * @ApiParams (name="lng", type="string", required=true, description="经度")
     * @ApiParams (name="lat", type="string", required=true, description="纬度")
     * @ApiParams (name="shopid", type="string", required=true, description="门店ID")
     */
    public function shopdetail()
    {
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/shop/shop.php');
        $lng = $this->request->post('lng','');
        $lat = $this->request->post('lat','');
        $shopid = $this->request->post('shopid','');
        if(!$lng || !$lat || !$shopid){
            $this->error(__("Invalid parameters"));
        }
        $where = ['id'=> $shopid,
            'status'=>'show'];
        $shop = new \app\common\model\shop\Shop();
        $list = $shop
            ->where($where)
            ->field('*, ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((' . $lat . ' * PI() / 180 - splat * PI() / 180) / 2),2) + COS(' . $lat . ' * PI() / 180) * COS(splat * PI() / 180) * POW(SIN((' . $lng . ' * PI() / 180 - splng * PI() / 180) / 2),2))) * 1000) AS distance')
            ->find();
        $spimgs = explode(',',$list['spimgs']);
        foreach ($spimgs as $v){
            $spimglist[] = cdnurl($v,true);
        }
        $list['spimgs'] = $spimglist;
        $likeObj = new Shoplike();
        $likesum = $likeObj->where(['shopid'=>$list['id'],'type'=>'like'])->sum('score');
        $list['likecount'] = $likeObj->where(['shopid'=>$list['id'],'type'=>'like'])->count();
        $list['like'] = empty($list['likecount']) ? 0 : number_format($likesum / $list['likecount'],1);
        if(empty($list)){
            $this->error(__("Does not exist"));
        }

        $this->success(__('Success'), $list);
    }

    /**
     * 产品列表
     *
     * @ApiMethod (POST)
     * @ApiParams (name="shopid", type="string", required=true, description="门店ID")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认20")
    */
    public function prelist()
    {
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/shop/shoplist.php');
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?5:$this->request->post('pagesize');
        $shopid = $this->request->post('shopid','');
        if(!$shopid){
            $this->error(__("Invalid parameters"));
        }
        $where = ['shopid'=> $shopid,
            'status'=>'1'];
        $shop = new \app\common\model\shop\Shoplist();
        $list = $shop
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page])->each(function ($item,$index){
                $item['sbimg'] = cdnurl($item['sbimg'],true);
                return $item;
            });

        $this->success(__('Success'), $list);
    }

    /**
     * 服务列表
     *
     * @ApiMethod (POST)
     * @ApiParams (name="shopid", type="string", required=true, description="门店ID")
     * @ApiParams (name="page", type="string", required=false, description="当前页码默认1")
     * @ApiParams (name="pagesize", type="int", required=false, description="显示记录数默认5")
     */
    public function servicelist()
    {
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/shop/shopservice.php');
        $page = empty($this->request->post('page'))?1:$this->request->post('page');
        $pagesize = empty($this->request->post('pagesize'))?5:$this->request->post('pagesize');
        $shopid = $this->request->post('shopid','');
        if(!$shopid){
            $this->error(__("Invalid parameters"));
        }
        $where = ['shopid'=> $shopid,
            'status'=>'show'];
        $shop = new \app\common\model\shop\Shopservice();
        $list = $shop
            ->where($where)
            ->order('id desc')
            ->paginate($pagesize,false,['page'=>$page]);

        $this->success(__('Success'), $list);
    }

    /**
     * 用户订单评分
     *
     * @ApiMethod (POST)
     * @ApiParams (name="shopid", type="int", required=true, description="门店ID")
     * @ApiParams (name="orderid", type="int", required=true, description="订单ID")
     * @ApiParams (name="score", type="int", required=true, description="评分(整数1-5)")
     */
    public function userlike()
    {
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/shop/shoplike.php');
        $shopid = $this->request->post('shopid','');
        $score = $this->request->post('score','');
        $orderid = $this->request->post('orderid','');
        if(!$shopid || !$score || !$orderid){
            $this->error(__("Invalid parameters"));
        }
        $data = [
            'shopid'=> $shopid,
            'orderid'=> $orderid,
            'userid'=> $this->auth->id,
            'score'=>$score,
            'ctime'=>time(),
        ];
        $shop = new \app\common\model\shop\Shoplike();
        $list = $shop
            ->save($data);

        $this->success(__('Success'));
    }

    /**
     * 用户收藏
     *
     * @ApiMethod (POST)
     * @ApiParams (name="shopid", type="int", required=true, description="门店ID")
     */
    public function usercollect()
    {
        Lang::load(ROOT_PATH . 'application/api/lang/zh-cn/shop/shoplike.php');
        $shopid = $this->request->post('shopid','');
        if(!$shopid){
            $this->error(__("Invalid parameters"));
        }
        $data = [
            'shopid'=> $shopid,
            'userid'=> $this->auth->id,
            'ctime'=>time(),
        ];
        $shop = new \app\common\model\shop\Shoplike();
        $list = $shop
            ->save($data);

        $this->success(__('Success'));
    }

}