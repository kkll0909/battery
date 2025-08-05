<?php

namespace app\index\controller;

use app\admin\model\batmanage\Bat;
use app\admin\model\batmanage\Batlocstate;
use app\admin\model\Otalog;
use app\common\controller\Frontend;
use app\common\library\Log;

class Mqtt extends Frontend
{
    //nohup php /www/wwwroot/battery/public/index.php /index/mqtt/sc > /dev/null 2>&1 &
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    const token = "DFXFyMg7VmQcFYfL7Q3RYxDL8Qs0EceL";

    public function sc()
    {
        // 使用示例
        $token = self::token; // 替换为您的实际token
        $mqttClient = new \app\common\library\Lib\Mqtt($token);

        if ($mqttClient->connect()) {
            echo "成功连接到MQTT服务器\n";

            // 示例：发送设置参数命令
//            $mqttClient->sendCommand('2491000542', 'get_bms_status', [
//                //'charge_voltage' => '14.6V'
//            ]);

            // 保持连接并处理消息
            while (true) {
                $mqttClient->loop();
                sleep(1); // 避免CPU占用过高
            }
        } else {
            echo "无法连接到MQTT服务器\n";
        }
    }

    //指令下发
    public function sendc()
    {
        $deviceid = $this->request->param('deviceid','');
        $commandt = $this->request->param('commandt','');
        $params = $this->request->param('params','');

        // 使用示例
        $token = self::token; // 替换为您的实际token
        $mqttClient = new \app\common\library\Lib\Mqtt($token);

        if ($mqttClient->connect()) {
            echo "成功连接到MQTT服务器\n";

            // 示例：发送设置参数命令
            $mqttClient->sendCommand($deviceid, $commandt,$params);

            // 保持连接并处理消息
//            while (true) {
//                $mqttClient->loop();
//                sleep(1); // 避免CPU占用过高
//            }
        } else {
            echo "无法连接到MQTT服务器\n";
        }
    }

    public static function addlog($p)
    {
        $d = is_array($p)?$p:json_decode($p,true);
        //添加设备日志
        $data['message_type'] = $d['message_type'];
        $data['type'] = 'bat';
        $data['sbno'] = $d['content']['device_id']??'';
        $data['stime'] = time();
        $data['lognote'] = json_encode($d,JSON_UNESCAPED_UNICODE);
        $otalog = new Otalog();
        $otalog->insert($data);
        //更新设备
        if($d['content']['raw']=='1003'){
            //电池
            self::updateBat($d['content']);
        }elseif($d['content']['raw']=='1000'){
            //基站
            self::updateJz($d['content']);
        }elseif($d['content']['raw']=='1001'){
            //坐标
            self::updateMove($d['content']);
        }

    }
    //更新设备信息
    public static function updateBat($data)
    {
        \think\Log::write('设备信息:'.json_encode($data));
        $device_id = $data['device_id'];
        $data = $data['status'];
        $data_params['voltage'] = $data['total_voltage']??0;
        $data_params['capacity'] = $data['nominal_capacity']??0;
        $data_params['cellcount'] = $data['cell_count']??0;
        $data_params['ambienttemperature'] = $data['ambient_temperature']??0;
        $data_params['celltemperature'] = $data['cell_temperature']??0;
        $data_params['boardtemperature'] = $data['board_temperature']??0;
        $data_params['soh'] = $data['soh']??0;
        $data_params['soc'] = $data['soc']??0;
        $data_params['cyclelife'] = $data['cycle_count']??0;
        $data_params['battype'] = $data['battery_type']??0;
        $data_params['balance'] = $data['balance']??0;
        $data_params['chargedischargeswitch'] = $data['charge_discharge_switch']??0;
        $data_params['mosstatus'] = $data['mos_status']??0;
        $data_params['remainingcapacity'] = $data['remaining_capacity']??0;
        $bat = new Bat();
        $bat->save($data_params,['batno'=>$device_id]);
    }

    //基站
    public static function updateJz($data)
    {
        \think\Log::write('基站信息:'.json_encode($data));

    }

    //坐标
    public static function updateMove($data)
    {
        \think\Log::write('运动坐标信息:'.json_encode($data));
        $device_id = $data['device_id'];
        $bat = new Bat();
        $id = $bat->where(['batno'=>$device_id])->value('id');
        if(empty($id)){
            \think\Log::write("设备{$device_id}不存在");
            return false;
        }
        $data = $data['status'];
        $data_params['batid'] = $id;
        $data_params['speed'] = $data['speed'];
        $data_params['direction'] = $data['direction'];
        $data_params['latitude'] = $data['latitude'];
        $data_params['longitude'] = $data['longitude'];
        $data_params['location_state'] = $data['location_state'];
        $data_params['datet'] = $data['date'];
        $batloc = new Batlocstate();
        $batloc->insert($data_params);
        $batin['lng'] = $data['longitude'];
        $batin['lat'] = $data['latitude'];
        $bat->save($batin,['batno'=>$device_id]);
    }

}