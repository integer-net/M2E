<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    /** @var Varien_Db_Adapter_Interface */
    protected $connection = null;
    /** @var Mage_Core_Model_Resource_Setup */
    protected $installer = null;
    protected $tableName = '';
    protected $queryLog = array();

    //####################################
    public function getConnection()
    {
        return $this->connection;
    }

    public function setConnection(Varien_Db_Adapter_Interface $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Mage_Core_Model_Resource_Setup $installer)
    {
        $this->installer = $installer;
        return $this;
    }

    //####################################

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        if (!$this->getConnection()->isTableExists($tableName)) {
            throw new Zend_Db_Exception("Table \"{$tableName}\" is not exists");
        }
        $this->tableName = $this->getInstaller()->getTable($tableName);

        return $this;
    }

    //####################################

    protected function runQuery($query)
    {
        $this->setQueryLog($query);
        $this->getInstaller()->run($query);
        return $this;
    }

    //####################################

    public function getQueryLog()
    {
        return $this->queryLog;
    }

    public function setQueryLog($query)
    {
        $this->queryLog[] = $query;
        return $this;
    }

    //####################################
}