<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\database\driver
 * Class Mysql
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 23:14
 * Description :
 */

namespace library\database\driver;

use library\database\exception\DbConnectError;
use library\database\object\DbFile;
use library\database\object\Model;
use library\database\object\SqlKey;
use PDO;
use PDOException;

class Mysql extends Driver
{

    private DbFile $dbFile;

    /**
     * @throws DbConnectError
     */
    public function __construct(DbFile $dbFile)
    {
        if ($dbFile->charset === "utf8")
            $dbFile->charset = "utf8mb4";
        $this->dbFile = $dbFile;
        //pdo初始化
        try {
            $this->pdo = new PDO("mysql:host={$this->dbFile->host};port={$this->dbFile->port};dbname={$this->dbFile->db}",
                $this->dbFile->username,
                $this->dbFile->password,
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $this->dbFile->charset . '\'',
                ]);
        } catch (PDOException $e) {
            throw new DbConnectError($e->getMessage(), $e->errorInfo, "Mysql");
        }

    }

    /**
     * 渲染创建字段
     * @param Model $model
     * @param string $table
     * @return string
     */
    function renderCreateTable(Model $model, string $table): string
    {
        $primary_keys = $model->getPrimaryKey() instanceof SqlKey ? [$model->getPrimaryKey()] : $model->getPrimaryKey();
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '`(';
        $primary = [];
        /**
         * @var $value SqlKey
         */
        foreach ($primary_keys as $value) {
            if ($value instanceof SqlKey) {
                $name = $value->name;
                $primary[] = $name;
                $sql .= $this->renderKey($value) . ",";
            } else {
                $primary[] = $value;
            }
        }
        foreach (get_object_vars($model) as $key => $value) {
            if (in_array($key, $primary)) continue;
            $sql .= $this->renderKey(new SqlKey($key, $value)) . ",";
        }
        $sql .= "PRIMARY KEY (";

        foreach ($primary as $key => $item) {
            $sql .= "`$item`";
            if ($key < sizeof($primary) - 1) {
                $sql .= ",";
            }
        }
        $sql .= ")";
        $sql .= ')ENGINE=InnoDB DEFAULT CHARSET=' . $this->dbFile->charset . ';';

        return $sql;

    }

    public function renderKey(SqlKey $sqlKey): string
    {
        if ($sqlKey->type === SqlKey::TYPE_TEXT && $sqlKey->value !== null)
            $sqlKey->value = str_replace("'", "\'", $sqlKey->value);

        if ($sqlKey->type === SqlKey::TYPE_INT && $sqlKey->auto) return "`$sqlKey->name` INT AUTO_INCREMENT";

        elseif ($sqlKey->type === SqlKey::TYPE_INT && !$sqlKey->auto) return "`$sqlKey->name` INT";

        elseif ($sqlKey->type === SqlKey::TYPE_BOOLEAN) return "`$sqlKey->name` INT";

        elseif ($sqlKey->type === SqlKey::TYPE_TEXT && $sqlKey->length !== 0) return "`$sqlKey->name` VARCHAR(" . $sqlKey->length . ") DEFAULT '$sqlKey->value'";

        elseif ($sqlKey->type === SqlKey::TYPE_TEXT && $sqlKey->length === 0 && $sqlKey->value !== null) return "`$sqlKey->name` LONGTEXT NOT NULL";

        elseif ($sqlKey->type === SqlKey::TYPE_TEXT && $sqlKey->length === 0 && $sqlKey->value === null) return "`$sqlKey->name` TEXT";

        elseif ($sqlKey->type === SqlKey::TYPE_FLOAT) return "`$sqlKey->name` FLOAT DEFAULT '$sqlKey->value'";

        else  return "`$sqlKey->name` TEXT DEFAULT '$sqlKey->value'";
    }

    function getDbConnect(): PDO
    {

        return $this->pdo;
    }

    public function __destruct()
    {
        unset($this->pdo);
    }


    function renderEmpty(string $table): string
    {
        return "TRUNCATE TABLE '$table';";
    }

    function onInsertModel(int $model): string
    {
        return $model;
    }
}