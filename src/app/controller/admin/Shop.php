<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: app\controller\admin
 * Class Shop
 * Created By ankio.
 * Date : 2023/5/4
 * Time : 17:11
 * Description :
 */

namespace app\controller\admin;

use app\database\dao\ShopCategoryDao;
use cleanphp\base\Config;
use cleanphp\engine\EngineManager;

class Shop extends BaseController
{
    function setting()
    {
        EngineManager::getEngine()->setArray(Config::getConfig("shop"));
    }

    function manager()
    {
        EngineManager::getEngine()->setData("category", ShopCategoryDao::getInstance()->getAllCategory());
    }
}