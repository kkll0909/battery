<?php

namespace app\index\controller;

use app\admin\model\Otalog;

class Mqtt
{
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

    public function a()
    {
        $a = '{"message_type":"POLLING_REPORT","content":{"device_id":"2491000542","device_type":"G2","timestamp":1748827019214,"raw":1003,"status":{"total_voltage":66.5,"cell_count":20,"soc":100,"remaining_capacity":50.0,"soh":91,"charging_current":0.04,"ambient_temperature":29,"cell_temperature":22,"board_temperature":23,"cell_voltage_1":3.312,"cell_voltage_2":3.315,"cell_voltage_3":3.313,"cell_voltage_4":3.314,"cell_voltage_5":3.312,"cell_voltage_6":3.313,"cell_voltage_7":3.314,"cell_voltage_8":3.315,"cell_voltage_9":3.313,"cell_voltage_10":3.314,"cell_voltage_11":3.312,"cell_voltage_12":3.313,"cell_voltage_13":3.312,"cell_voltage_14":3.313,"cell_voltage_15":3.311,"cell_voltage_16":3.314,"cell_voltage_17":3.312,"cell_voltage_18":3.313,"cell_voltage_19":3.311,"cell_voltage_20":3.315,"cell_voltage_21":0.0,"cell_voltage_22":0.0,"cell_voltage_23":0.0,"cell_voltage_24":0.0,"cell_voltage_25":0.0,"cell_voltage_26":0.0,"cell_voltage_27":0.0,"cell_voltage_28":0.0,"cell_voltage_29":0.0,"cell_voltage_30":0.0,"cell_voltage_31":0.0,"cell_voltage_32":0.0,"battery_type":2,"cycle_count":80,"nominal_capacity":50,"charge_discharge_switch":1,"request_charge_current":0.54,"request_charge_voltage":73.08,"balance":0,"mos_status":0,"heating_status":0,"light_trigger":0,"over_voltage_protection":0,"undervoltage_protection":0,"short_circuit_protection":0,"discharge_overcurrent_protection":0,"charge_overcurrent_protection":0,"high_temperature_protection":0,"low_temperature_protection":0,"large_pressure_difference":0,"raw":"01036619FA001400641388005B0004001D001600170CF00CF30CF10CF20CF00CF10CF20CF30CF10CF20CF00CF10CF00CF10CEF0CF20CF00CF10CEF0CF3000000000000000000000000000000000000000000000000000200500032000100361C8C00000000000000004397"}},"version":"1.1.8-b3"}';
        $a = json_decode($a,true);
        dump($a);
    }

    public static function addlog($p)
    {
        $d = is_array($p)?$p:json_decode($p,true);
        $data['message_type'] = $d['message_type'];
        $data['type'] = 'bat';
        $data['sbno'] = $d['content']['device_id']??'';
        $data['stime'] = time();
        $data['lognote'] = json_encode($d,JSON_UNESCAPED_UNICODE);
        $otalog = new Otalog();
        $otalog->insert($data);
    }
    //1

}