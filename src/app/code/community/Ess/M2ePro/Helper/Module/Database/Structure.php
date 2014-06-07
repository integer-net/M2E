<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Helper_Module_Database_Structure extends Mage_Core_Helper_Abstract
{
    //#############################################

    public function getTablesInfo()
    {
        $tablesInfo = array();
        foreach (Mage::helper('M2ePro/Module_Database')->getMySqlTables() as $currentTable) {
            $tablesInfo[$currentTable] = $this->getTableInfo($currentTable);
        }

        return $tablesInfo;
    }

    public function getTableInfo($tableName)
    {
        $readConnection = Mage::getResourceModel('core/config')->getReadConnection();
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $stmtQuery = $readConnection->query('SHOW COLUMNS FROM '.$tableName);

        $result = array();
        while ($row = $stmtQuery->fetch()) {

            $result[strtolower($row['Field'])] = array(
                'type'     => strtolower($row['Type']),
                'null'     => strtolower($row['Null']),
                'key'      => strtolower($row['Key']),
                'default'  => strtolower($row['Default']),
                'extra'    => strtolower($row['Extra']),
            );
        }

        return $result;
    }

    // --------------------------------------------

    public function getStoreRelatedColumns()
    {
        $result = array();

        $simpleColumns = array('store_id', 'related_store_id');
        $jsonColumns = array('magento_orders_settings', 'marketplaces_data');

        foreach ($this->getTablesInfo() as $tableName => $tableInfo) {
            foreach ($tableInfo as $columnName => $columnInfo) {

                if (in_array($columnName, $simpleColumns)) {
                    $result[$tableName][] = array(
                        'name' => $columnName, 'type' => 'int'
                    );
                }

                if (in_array($columnName, $jsonColumns)) {
                    $result[$tableName][] = array(
                        'name' => $columnName, 'type' => 'json'
                    );
                }
            }
        }

        return $result;
    }

    //#############################################
}