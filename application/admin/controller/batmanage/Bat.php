<?php

namespace app\admin\controller\batmanage;

use app\admin\model\batmanage\Belong;
use app\admin\model\maint\Maintenance;
use app\admin\model\orders\Cgordersub;
use app\common\controller\Backend;
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Log;
use think\Model;

/**
 * 电池管理
 *
 * @icon fa fa-circle-o
 */
class Bat extends Backend
{

    /**
     * Bat模型对象
     * @var \app\admin\model\batmanage\Bat
     */
    protected $model = null;
    protected $relationSearch = true;
    protected $noNeedRight = ['selectpage'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\batmanage\Bat;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("islikeList", $this->model->getIslikeList());
        $this->view->assign("battypeList", $this->model->getBattypeList());
        $this->view->assign("balanceList", $this->model->getBalanceList());
        $this->view->assign("chargedischargeswitchList", $this->model->getChargedischargeswitchList());
        $this->view->assign("mosstatusList", $this->model->getMosstatusList());
        $this->view->assign('admin_id',$this->auth->id);
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->with(['admin','factory'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit)->each(function ($item,$index){
                    $ismt =  Maintenance::where(['batno'=>$item['batno'],'bxstatus'=>['<>','wxwc']])->count();
                    $item['ismt'] = $ismt>1?1:0;
                });
            $ex['totalbs'] = $this->model->where($where)->count();

            //分配电池数量
            $ex['totallbs'] = \app\admin\model\orders\Cgordersub::where(['batno'=>['<>','']])->count();
            //用户绑定数量
            $likebatlist = \app\admin\model\orders\Cgordersub::select();
            $i=0;
            foreach ($likebatlist as $k=>$v){
                $batid = $this->model->where(['batno'=>$v['batno']])->value('id');
                if($batid){
                    $beInfo = Belong::where(['batid'=>$batid,'belongtype'=>'user','isuse'=>'self','iszt'=>'ok'])->count();
                    if($beInfo){
                        $i++;
                    }
                }

            }
            $ex['totalbbs'] = $i;

            $result = array("total" => $list->total(), "rows" => $list->items(),'extend'=>$ex);
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            $newId = $this->model->id;
            $bein['admin_id'] = $params['admin_id'];
            $bein['batid'] = $newId;
            $bein['belongid'] = $params['admin_id'];
            $bein['isuse'] = 'self';
            $bein['belongtype'] = 'manage';
            $bein['status'] = 'show';
            $bein['stime'] = time();
            $bein['iszt'] = 'yes';
            \app\admin\model\batmanage\Belong::create($bein);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


    /**
     * 下拉搜索
     */
    public function selectpage()
    {
        //设置过滤方法
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        //搜索条件
        $andor = $this->request->request("andOr", "and", "strtoupper");
        //排序方式
        $orderby = (array)$this->request->request("orderBy/a");
        //显示的字段
        $field = $this->request->request("showField");
        //主键
        $primarykey = $this->request->request("keyField");
        //主键值
        $primaryvalue = $this->request->request("keyValue");
        //搜索字段
        $searchfield = (array)$this->request->request("searchField/a");
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        //是否返回树形结构
        $istree = $this->request->request("isTree", 0);
        $ishtml = $this->request->request("isHtml", 0);
        if ($istree) {
            $word = [];
            $pagesize = 999999;
        }
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
            $pagesize = 999999;
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                $searchfield = str_replace(',', $logic, $searchfield);
                $word = array_filter(array_unique($word));
                if (count($word) == 1) {
                    $query->where($searchfield, "like", "%" . reset($word) . "%");
                } else {
                    $query->where(function ($query) use ($word, $searchfield) {
                        foreach ($word as $index => $item) {
                            $query->whereOr(function ($query) use ($item, $searchfield) {
                                $query->where($searchfield, "like", "%{$item}%");
                            });
                        }
                    });
                }
                if ($custom && is_array($custom)) {
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), $v[1]);
                        } else {
                            $query->where($k, '=', $v);
                        }
                    }
                }
            };
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        $list = [];
        $total = $this->model->where($where)->count();
        if ($total > 0) {
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }

            $fields = is_array($this->selectpageFields) ? $this->selectpageFields : ($this->selectpageFields && $this->selectpageFields != '*' ? explode(',', $this->selectpageFields) : []);

            //如果有primaryvalue,说明当前是初始化传值,按照选择顺序排序
            if ($primaryvalue !== null && preg_match("/^[a-z0-9_\-]+$/i", $primarykey)) {
                $primaryvalue = array_unique(is_array($primaryvalue) ? $primaryvalue : explode(',', $primaryvalue));
                //修复自定义data-primary-key为字符串内容时，给排序字段添加上引号
                $primaryvalue = array_map(function ($value) {
                    return '\'' . $value . '\'';
                }, $primaryvalue);

                $primaryvalue = implode(',', $primaryvalue);

                $this->model->orderRaw("FIELD(`{$primarykey}`, {$primaryvalue})");
            } else {
                $this->model->order($order);
            }
            //dump($where);
            $datalist = $this->model->where($where)
                ->page($page, $pagesize)
                ->select();

            foreach ($datalist as $index => $item) {
                unset($item['password'], $item['salt']);
                if ($this->selectpageFields == '*') {
                    $result = [
                        $primarykey => $item[$primarykey] ?? '',
                        $field      => $item[$field] ?? '',
                    ];
                } else {
                    $result = array_intersect_key(($item instanceof Model ? $item->toArray() : (array)$item), array_flip($fields));
                }
                $result['pid'] = isset($item['pid']) ? $item['pid'] : (isset($item['parent_id']) ? $item['parent_id'] : 0);
                $result = array_map("htmlentities", $result);
                //去除已经分配的
                $count = Cgordersub::where(['batno'=>$result['batno']])->count();
                if($count){
                    continue;
                }
                $list[] = $result;
            }
            if ($istree && !$primaryvalue) {
                $tree = Tree::instance();
                $tree->init(collection($list)->toArray(), 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), $field);
                if (!$ishtml) {
                    foreach ($list as &$item) {
                        $item = str_replace('&nbsp;', ' ', $item);
                    }
                    unset($item);
                }
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }

    //电池充放电配置(指令)
    public function sendcf()
    {
        //2491000542
        $deviceid = $this->request->param('deviceid','');
        $status = $this->request->param('status',1);
        $commandt = "charge_control";
        $params = ['status'=>$status];
        $params = json_encode($params);
        $c = "php /www/wwwroot/battery/public/index.php index/mqtt/sendc/deviceid/{$deviceid}/commandt/{$commandt}/params/{$params}";
        Log::write("发送指令:".$c);
        $re = shell_exec($c);
        $this->success("指令下发成功{$re}，会自动更新电池数据");
    }

    public function map($batid='')
    {
        $ids = $batid;
//        $batinfo = $this->model->where(['id'=>$ids])->find();
//        $this->assign('lng',$batinfo['lng']);
//        $this->assign('lat',$batinfo['lat']);
        $batloc = new \app\admin\model\batmanage\Batlocstate();
        $pot = [];
        $batlocinfo = $batloc->where(['batid'=>$ids])->limit(10)->order('id desc')->select();
        if(!$batlocinfo){
            $this->error('暂无定位数据!','');
        }
        foreach ($batlocinfo as $v){
            $pot[] = [$v['longitude'],$v['latitude']];
        }
        $this->assign('pot',json_encode($pot));
        return $this->view->fetch('map');
    }

    //二维码
    public function qrcode($batid=''){
        $batinfo = $this->model->where(['id'=>$batid])->find();

        return $this->view->fetch('qrcode');
    }
}
