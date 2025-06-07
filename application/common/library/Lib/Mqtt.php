<?php

namespace app\common\library\Lib;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class Mqtt
{
    private $client;
    private $server = 'api.666x.cc';
    private $port = 1882;
    private $username = 'public';
    private $password = '123456';
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function connect()
    {
        try {
            $this->client = new MqttClient($this->server, $this->port, uniqid());

            $connectionSettings = (new ConnectionSettings())
                ->setUsername($this->username)
                ->setPassword($this->password)
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(10);

            $this->client->connect($connectionSettings, true);

            // 订阅消息主题
            $this->client->subscribe("message/{$this->token}", function ($topic, $message) {
                $this->handleIncomingMessage($message);
            }, 0);

            return true;
        } catch (\Exception $e) {
            error_log("MQTT连接失败: " . $e->getMessage());
            return false;
        }
    }

    public function disconnect()
    {
        $this->client->disconnect();
    }

    public function sendCommand($deviceId, $commandType, $parameters)
    {
        $command = [
            'message_type' => 'command',
            'content' => [
                'device_id' => $deviceId,
                'device_type' => 'G2',
                'command' => [
                    'type' => $commandType,
                    'parameters' => $parameters
                ]
            ]
        ];
        echo json_encode($command);
        $this->client->publish("command/{$this->token}", json_encode($command), 0);
    }

    private function handleIncomingMessage($message)
    {
        //echo $message;
        $data = json_decode($message, true);
        \app\index\controller\Mqtt::addlog($data);
        if ($data['message_type'] === 'status_report') {
            // 处理设备上报的状态信息
            $deviceId = $data['content']['device_id'];
            $status = $data['content']['status'];

            // 在这里添加您的业务逻辑
            echo "收到设备 {$deviceId} 状态更新:\n";
            //print_r($status);
            echo $status;
        }
    }

    public function loop()
    {
        $this->client->loop(true);
    }
}