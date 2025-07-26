<?php

declare(strict_types=1);
namespace addons\miniprogram\library;
use app\admin\model\miniprogram\Config;

class ConfigService
{

    /**
     * @notes 设置配置值
     * @param $type
     * @param $name
     * @param $value
     * @return mixed
     * @author Xing <464401240@qq.com>
     */
    public static function set(string $type, string $name, $value)
    {
        $original = $value;
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $data = Config::where(['group' => $type, 'name' => $name])->find();
        if (empty($data)) {
            Config::create(['group' => $type, 'name' => $name, 'value' => $value]);
        } else {
            $data->value = $value;
            $data->save();
        }
        // 返回原始值
        return $original;
    }

    /**
     * @notes 获取配置值
     * @param $type
     * @param string $name
     * @param null $default_value
     * @return array|int|mixed|string
     * @author Xing <464401240@qq.com>
     */
    public static function get(string $type, string $name = '', $default_value = null)
    {
        if (!empty($name)) {
            $value = Config::where(['group' => $type, 'name' => $name])->value('value');
            if (!is_null($value)) {
                $json = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $json : $value;
            }
            if ($value) {
                return $value;
            }
            // 返回特殊值 0 '0'
            if ($value === 0 || $value === '0') {
                return $value;
            }
            // 返回本地配置文件中的值
            return config($type . '.' . $name) ?? $default_value;
        }

        // 取某个类型下的所有name的值
        $data = Config::where(['group' => $type])->column('value', 'name');
        foreach ($data as $k => $v) {
            $json = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data[$k] = $json;
            }
        }
        if ($data) {
            return $data;
        }
        // 返回本地配置文件中的值
        return config($type) ?? $default_value;
    }

    /**
     * @notes 获取微信小程序配置
     * @return array
     * @author Xing <464401240@qq.com>
     */
    public static function getMnpConfig()
    {
        $config = self::get('miniprogram');
        if ($config) {
            return [
                'app_id' => $config['app_id'],
                'secret' => $config['app_secret'],
                'token'  => $config['token'],
                'aes_key'=> $config['encoding_aes_key'],
                'response_type' => 'array',
                'log' => [
                    'level' => \think\Config::get('app_debug') ? 'debug' : 'info',
                    'file' => ROOT_PATH . 'runtime/miniprogram/' . date('Ym') . '/' . date('d') . '.log'
                ],
            ];
        }
        return [];
    }
}