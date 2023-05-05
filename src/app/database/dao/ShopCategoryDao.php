<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: app\database\dao
 * Class ShopCategoryDao
 * Created By ankio.
 * Date : 2023/5/4
 * Time : 17:42
 * Description :
 */

namespace app\database\dao;

use app\database\model\ShopCategoryModel;
use library\database\object\Dao;

class ShopCategoryDao extends Dao
{

    public function __construct()
    {
        parent::__construct(ShopCategoryModel::class);
    }

    function getAllCategory()
    {
        return $this->select()->commit(false);
    }

    /**
     * @inheritDoc
     */
    protected function getTable(): string
    {
        return 'shop_category';
    }
}