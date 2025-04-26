<?php

namespace app\index\controller;

class Mqtt
{
    public function sendC()
    {
        $mqtt = new \app\common\library\Lib\Mqtt('your_token');
        $mqtt->sendCommand('device_id', 'G2', 'set_parameter', [
            'parameter_name' => 'parameter_value'
        ]);
    }

    public function subscribe()
    {
        $mqtt = new \app\common\library\Lib\Mqtt('your_token');
        $mqtt->subscribe(function($data) {
            // 处理接收到的消息
            print_r($data);
        });
    }
}