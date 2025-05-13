<?php

namespace app\index\controller;

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

}