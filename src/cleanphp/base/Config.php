<?php
/*
 *  Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace cleanphp\base;


use cleanphp\App;
use cleanphp\exception\ExitApp;

/**
 * Class Config
 * @package cleanphp\config
 * Date: 2020/11/19 12:22 上午
 * Author: ankio
 * Description:配置管理器
 */
class Config
{
    private static array $file_data = [];//配置文件
    private static string $path = "";


    /**
     * 获取路由表
     * @return array|null
     */
    static public function getRouteTable(): ?array
    {
        $result = self::$file_data["route"] ?? null;
        EventManager::trigger("__config_get_route__", $result);

        return $result;
    }


    static private function loadConfig()
    {
        if (!empty(self::$file_data)) return;
        self::$path = Variables::getAppPath("config.php");
        self::$file_data = require self::$path;
    }

    /**
     * 注册配置信息
     * @throws ExitApp
     */
    static public function register()
    {

        self::loadConfig();
        date_default_timezone_set(Config::getConfig('frame')['time_zone'] ?? "Asia/Shanghai");
        $frame = self::getConfig("frame");
        if (!in_array("0.0.0.0", $frame['host']) && !App::$cli && !in_array($_SERVER["SERVER_NAME"], $frame['host'])) {
            App::exit(sprintf("您的域名绑定错误，当前域名为：%s , 请在 %s 中Host选项里添加该域名。", $_SERVER["SERVER_NAME"], "config.php"), true);
        }


    }


    /**
     * 设置单个配置
     * @param string $key 参数名称
     * @param  $val
     */
    public static function setConfig(string $key, $val)
    {
        self::loadConfig();
        self::$file_data[$key] = $val;
        self::setAll(self::$file_data);
    }

    /**
     * 获取配置
     * @return mixed|null
     */
    static public function getConfig($sub = "")
    {
        self::loadConfig();
        return self::$file_data[$sub] ?? null;
    }


    /**
     * 设置整个配置文件数组
     * @param array $data
     */
    public static function setAll(array $data)
    {
        self::$file_data = $data;
        file_put_contents(self::$path, '<?php return ' . var_export(self::$file_data, true) . '; ');
    }
}
