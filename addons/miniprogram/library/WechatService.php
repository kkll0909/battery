<?php

namespace addons\miniprogram\library;
use EasyWeChat\Kernel\Exceptions\Exception;
use EasyWeChat\Factory;

class WechatService
{
    private static $instance = null;

    /**
     * @notes EasyWeChat微信小程序
     * @author Xing <464401240@qq.com>
     */
    public static function application($cache = false)
    {
        if (self::$instance === null || $cache === true) {
            $config = ConfigService::getMnpConfig();
            if (empty($config['app_id']) || empty($config['secret'])) {
                throw new Exception('请先设置小程序配置');
            }
            self::$instance = Factory::miniProgram($config);
        }
        return self::$instance;
    }

    /**
     * @notes 回调事件监听
     * @author Xing <464401240@qq.com>
     */
    public static function observe()
    {
        self::application()->server->push(WechatEvent::class);//事件监听
        return self::application()->server->serve();
    }

    /**
     * @notes 小程序-根据code获取微信信息
     * @param string $code
     * @return array
     * @author Xing <464401240@qq.com>
     */
    public static function getMnpResByCode(string $code)
    {
        $utils = self::application()->auth;
        $response = $utils->session($code);
        if (!isset($response['openid']) || empty($response['openid'])) {
            throw new Exception('获取openID失败：errcode[' . $response['errcode'] . ']' . $response['errmsg']);
        }
        return $response;
    }

    /**
     * @notes 获取手机号
     * @param string $code
     * @author Xing <464401240@qq.com>
     */
    public static function getUserPhoneNumber(string $code)
    {
        return self::application()->getClient()->httpPostJson('wxa/business/getuserphonenumber', [
            'code' => $code,
        ]);
    }

    /**
     * 上传素材
     * @return object
     */
    public static function material($type, $data)
    {
        $data = self::pathFormat($data);
        $material = self::application()->media->uploadImage($data);
        if (isset($material['errcode']) && $material['errcode'] > 0) {
            throw new Exception(json_encode($material));
        }
        //媒体文件上传后，3天内有效
        return isset($material['media_id']) ? $material['media_id'] : '';
    }

    /**
     * @notes 下载文件
     * @param $url
     * @param $saveDir
     * @param $fileName
     * @return string
     * @author Xing <464401240@qq.com>
     */
    public static function download_file($url, $saveDir, $fileName): string
    {
        if (!file_exists($saveDir)) {
            mkdir($saveDir, 0775, true);
        }
        $fileSrc = $saveDir . $fileName;
        file_exists($fileSrc) && unlink($fileSrc);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $resource = fopen($fileSrc, 'a');
        fwrite($resource, $file);
        fclose($resource);
        if (filesize($fileSrc) == 0) {
            unlink($fileSrc);
            return '';
        }
        return '/' . $fileSrc;
    }

    /**
     * @notes 处理素材路径
     * @author Xing <464401240@qq.com>
     */
    public static function pathFormat($url)
    {
        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            $url = self::download_file($url, 'uploads/miniprogram/material/', md5($url) . '.' . $extension);
        }
        $url = realpath(trim($url, '/'));
        if ($url !== false) {
            return $url;
        }
        throw new \Exception('素材文件无效或不存在');
    }
}
