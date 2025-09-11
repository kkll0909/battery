<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\batmanage\Bat;
use app\admin\model\orders\Cgorders;
use app\admin\model\orders\Orderpay;
use app\admin\model\shop\Shop;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $column = [];
        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        $joinlist = Db("user")->where('jointime', 'between time', [$starttime, $endtime])
            ->field('jointime, status, COUNT(*) AS nums, DATE_FORMAT(FROM_UNIXTIME(jointime), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->select();
        for ($time = $starttime; $time <= $endtime;) {
            $column[] = date("Y-m-d", $time);
            $time += 86400;
        }
        $userlist = array_fill_keys($column, 0);
        foreach ($joinlist as $k => $v) {
            $userlist[$v['join_date']] = $v['nums'];
        }

        $dbTableList = Db::query("SHOW TABLE STATUS");
        $addonList = get_addon_list();
        $totalworkingaddon = 0;
        $totaladdon = count($addonList);
        foreach ($addonList as $index => $item) {
            if ($item['state']) {
                $totalworkingaddon += 1;
            }
        }
        $this->view->assign([
            'totaluser'         => User::count(),
            'totalrevenue'        => Orderpay::where(['isy'=>1,'paystatus'=>'pay'])->sum('paymoney'),
            'totalshops'        => Shop::count(),
            'totalcategory'     => \app\common\model\Category::count(),
            'todayusersignup'   => User::whereTime('jointime', 'today')->count(),
            'todayuserlogin'    => User::whereTime('logintime', 'today')->count(),
            'sevendau'          => User::whereTime('jointime|logintime|prevtime', '-7 days')->count(),
            'thirtydau'         => User::whereTime('jointime|logintime|prevtime', '-30 days')->count(),
            'threednu'          => User::whereTime('jointime', '-3 days')->count(),
            'sevendnu'          => User::whereTime('jointime', '-7 days')->count(),
            'investornums'       => 0,
            'investorreturns'    => 0,
            'todayorders' => Cgorders::whereTime('stime','d')->where(['status'=>'pay'])->count(),
            'nextmonthmoney'    => Orderpay::whereBetween('paydate',[time(),strtotime('+1 month')])->where(['isy'=>1,'paystatus'=>'nopay'])->sum('paymoney'),
            'revenueday'    => Orderpay::whereTime('paydate','d')->where(['isy'=>1,'paystatus'=>'pay'])->sum('paymoney'),
            'revenuemonth'    => Orderpay::whereTime('paydate','m')->where(['isy'=>1,'paystatus'=>'pay'])->sum('paymoney'),
            'devicesidle'       => Bat::count(),
            'devicesuse'       => Db::table('fa_viewbatx')->count(),
        ]);

        $this->assignconfig('column', array_keys($userlist));
        $this->assignconfig('userdata', array_values($userlist));

        return $this->view->fetch();
    }

    /**
     * 查看
     */
    public function mdindex()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $column = [];
        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        $joinlist = Db("user")->where('jointime', 'between time', [$starttime, $endtime])
            ->field('jointime, status, COUNT(*) AS nums, DATE_FORMAT(FROM_UNIXTIME(jointime), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->select();
        for ($time = $starttime; $time <= $endtime;) {
            $column[] = date("Y-m-d", $time);
            $time += 86400;
        }
        $userlist = array_fill_keys($column, 0);
        foreach ($joinlist as $k => $v) {
            $userlist[$v['join_date']] = $v['nums'];
        }

        $dbTableList = Db::query("SHOW TABLE STATUS");
        $addonList = get_addon_list();
        $totalworkingaddon = 0;
        $totaladdon = count($addonList);
        foreach ($addonList as $index => $item) {
            if ($item['state']) {
                $totalworkingaddon += 1;
            }
        }
        $this->view->assign([
            'totaluser'         => User::count(),
            'totalrevenue'        => Orderpay::where(['isy'=>1,'paystatus'=>'pay'])->sum('paymoney'),
            'totalshops'        => Shop::count(),
            'totalcategory'     => \app\common\model\Category::count(),
            'todayusersignup'   => User::whereTime('jointime', 'today')->count(),
            'todayuserlogin'    => User::whereTime('logintime', 'today')->count(),
            'sevendau'          => User::whereTime('jointime|logintime|prevtime', '-7 days')->count(),
            'thirtydau'         => User::whereTime('jointime|logintime|prevtime', '-30 days')->count(),
            'threednu'          => User::whereTime('jointime', '-3 days')->count(),
            'sevendnu'          => User::whereTime('jointime', '-7 days')->count(),
            'investornums'       => 0,
            'investorreturns'    => 0,
            'todayorders' => Cgorders::where(['admin_id'=>$this->auth->id])->whereTime('stime','d')->where(['status'=>'pay'])->count(),
            'nextmonthmoney'    => Orderpay::whereBetween('paydate',[time(),strtotime('+1 month')])->where(['isy'=>1,'paystatus'=>'nopay'])->sum('paymoney'),
            'revenueday'    => Orderpay::whereTime('paydate','d')->where(['isy'=>1,'paystatus'=>'pay'])->sum('paymoney'),
            'revenuemonth'    => Orderpay::whereTime('paydate','m')->where(['isy'=>1,'paystatus'=>'pay'])->sum('paymoney'),
            'devicesidle'       => Bat::where(['admin_id'=>$this->auth->id])->count(),
            'devicesuse'       => Db::table('fa_viewbatx')->where(['admin_id'=>$this->auth->id])->count(),
        ]);

        $this->assignconfig('column', array_keys($userlist));
        $this->assignconfig('userdata', array_values($userlist));

        return $this->view->fetch();
    }
}
