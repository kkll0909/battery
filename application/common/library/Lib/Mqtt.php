<?php

namespace app\common\library\Lib;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class Mqtt
{
    private $host = 'api.666x.cc';
    private $port = 1882;
    private $username = 'public';
    private $password = '123456';
    private $token;
    private $client;

    public function __construct($token)
    {
        if (empty($token)) {
            throw new \Exception("设备令牌不能为空");
        }
        $this->token = $token;
    }

    public function sendCommand($deviceId, $deviceType, $commandType, $parameters = [])
    {
        try {
            // 创建连接设置
            $settings = new ConnectionSettings();
            $settings->setUsername($this->username);
            $settings->setPassword($this->password);
            $settings->setKeepAliveInterval(60);
            $settings->setConnectTimeout(3);

            // 创建客户端
            $clientId = 'php-mqtt-' . uniqid();
            $this->client = new MqttClient($this->host, $this->port, $clientId);

            // 连接
            $this->client->connect($settings);

            // 准备命令
            $command = [
                'message_type' => 'command',
                'content' => [
                    'device_id' => (string)$deviceId,
                    'device_type' => (string)$deviceType,
                    'command' => [
                        'type' => (string)$commandType,
                        'parameters' => $parameters
                    ]
                ]
            ];

            // 发布命令
            $topic = "command/{$this->token}";
            $message = json_encode($command);
            
            $this->client->publish($topic, $message, 0);
            
            // 断开连接
            $this->client->disconnect();
            
            return true;
        } catch (\Exception $e) {
            if (isset($this->client)) {
                try {
                    $this->client->disconnect();
                } catch (\Exception $disconnectError) {
                    // 忽略断开连接时的错误
                }
            }
            throw new \Exception("命令发送失败: " . $e->getMessage());
        }
    }

    public function subscribe($callback)
    {
        try {
            // 创建连接设置
            $settings = new ConnectionSettings();
            $settings->setUsername($this->username);
            $settings->setPassword($this->password);
            $settings->setKeepAliveInterval(60);
            $settings->setConnectTimeout(3);

            // 创建客户端
            $clientId = 'php-mqtt-' . uniqid();
            $this->client = new MqttClient($this->host, $this->port, $clientId);

            // 连接
            $this->client->connect($settings);

            // 订阅主题
            $topic = "message/{$this->token}";
            $this->client->subscribe($topic, function ($topic, $message) use ($callback) {
                $data = json_decode($message, true);
                if ($data && isset($data['message_type']) && $data['message_type'] === 'status_report') {
                    $callback($data);
                }
            }, 0);

            // 保持连接
            $this->client->loop(true);

        } catch (\Exception $e) {
            if (isset($this->client)) {
                try {
                    $this->client->disconnect();
                } catch (\Exception $disconnectError) {
                    // 忽略断开连接时的错误
                }
            }
            throw new \Exception("订阅失败: " . $e->getMessage());
        }
    }

    public function disconnect()
    {
        if (isset($this->client)) {
            try {
                $this->client->disconnect();
            } catch (\Exception $e) {
                // 忽略断开连接时的错误
            }
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
} 