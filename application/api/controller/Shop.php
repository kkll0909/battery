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
        $lng = $this->request->post('lng','');
        $lat = $this->request->post('lat','');
        $shopname = $this->request->post('shopname','');
        $page = $this->request->post('page', 1);
        $pagesize = $this->request->post('pagesize', 20);
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
            ->field('*, ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((' . $lat . ' * PI() / 180 - splat * PI() / 180) / 2),2) + COS(' . $lat . ' * PI() / 180) * COS(splat * PI() / 180) * POW(SIN((' . $lng . ' * PI() / 180 - splng * PI() / 180) / 2),2))) * 1000) AS distance')
            ->order('distance ASC')
            ->paginate($pagesize,false,['page'=>$page]);
        
        $this->success(__('Success'), $list);
    }
}