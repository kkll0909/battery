<?php

namespace addons\miniprogram;
use app\common\library\Menu;
use think\Addons;

/**
 * 微信管理插件
 */
class Miniprogram extends Addons
{

    /**
     * 插件标识名称
     */
    private $name = 'miniprogram';

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [];
        $config_file = ADDON_PATH . $this->name . DS . 'data' . DS . "menus.php";
        if (file_exists($config_file)) {
            $menu = include $config_file;
        }
        if ($menu) {
            Menu::create($menu);
        }
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete($this->name);
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::delete($this->name);
        $menu = [];
        $config_file = ADDON_PATH . $this->name . DS . 'data' . DS . "menus.php";
        if (file_exists($config_file)) {
            $menu = include $config_file;
        }
        if ($menu) {
            Menu::create($menu);
        }
        Menu::enable($this->name);
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable($this->name);
    }

    /**
     * 插件升级方法
     */
    public function upgrade()
    {
        $menu = [];
        $config_file = ADDON_PATH . $this->name . DS . 'data' . DS . "menus.php";
        if (file_exists($config_file)) {
            $menu = include $config_file;
        }
        if ($menu) {
            Menu::upgrade($this->name, $menu);
        }
        return true;
    }

}
