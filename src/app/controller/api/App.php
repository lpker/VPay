<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: app\controller\api
 * Class App
 * Created By ankio.
 * Date : 2023/3/13
 * Time : 17:25
 * Description :
 */

namespace app\controller\api;

use app\database\dao\OrderDao;
use app\database\model\OrderModel;
use app\exception\OrderNotFoundException;
use app\objects\app\HeartObject;
use app\objects\app\PushObject;

use cleanphp\base\Config;
use cleanphp\base\Json;
use cleanphp\cache\Cache;
use cleanphp\file\Log;
use library\login\SignUtils;
use library\verity\VerityException;

class App extends BaseController
{
    private string $key = "";

    public function __init()
    {
        $this->key = Config::getConfig('app')['key'];
    }

    /**
     * App心跳
     * @return string
     */
    function heart(): string
    {
        try {
            $heart = new HeartObject(get(), $this->key);
            Cache::init()->set("last_heart", time());
            return $this->json(200, "心跳成功");
        } catch (VerityException $exception) {
            Log::record("app_channel", "心跳异常：" . $exception->getMessage());
            return $this->json(400, $exception->getMessage());
        }
    }

    /**
     * App消息推送
     * @return string
     */
    function push(): string
    {
        Log::record("app_channel", "收到App推送：" . Json::encode(arg()));
        try {
            $push = new PushObject(get(), $this->key);
        } catch (VerityException $exception) {
            return $this->json(400, $exception->getMessage());
        }
        $result = OrderDao::getInstance()->getWaitOrderByPayType($push->type, $push->price);
        if (empty($result)) {
            return $this->json(500, '无订单待确认！');
        }
        try {
            OrderDao::getInstance()->notify($result->order_id, $this->key);
        } catch (OrderNotFoundException $e) {
            return $this->json(500, '无订单待确认！');
        }
        return $this->json(200);
    }


}