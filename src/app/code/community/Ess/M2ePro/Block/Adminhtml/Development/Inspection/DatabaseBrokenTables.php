<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_DatabaseBrokenTables
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    public $emptyTables = array();
    public $notInstalledTables = array();

    private $existsTables = array();

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionDatabaseBrokenTables');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/databaseBrokenTables.phtml');

        $this->prepareTablesInfo();
    }

    // ########################################

    protected function isShown()
    {
        return !empty($this->emptyTables) || !empty($this->notInstalledTables);
    }

    // ########################################

    private function prepareTablesInfo()
    {
        $this->emptyTables = $this->getEmptyTables();
        $this->notInstalledTables = $this->getNotInstalledTables();
    }

    // ########################################

    private function getEmptyTables()
    {
        $emptyTables = array();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $resource = Mage::getSingleton('core/resource');

        foreach ($this->getGeneralTables() as $table) {

            $moduleTable = $resource->getTableName($table);

            if(!$this->isTableExists($moduleTable)) {
                continue;
            }

            $dbSelect = $connRead->select()->from($moduleTable, new Zend_Db_Expr('COUNT(*)'));

            if ((int)$connRead->fetchOne($dbSelect) == 0) {
                $emptyTables[] = $table;
            }
        }

        return $emptyTables;
    }

    private function getNotInstalledTables()
    {
        $notInstalledTables = array();

        $resource = Mage::getSingleton('core/resource');

        foreach (Mage::helper('M2ePro/Module_Database')->getMySqlTables() as $tableName) {
            if (!$this->isTableExists($resource->getTableName($tableName))) {
                $notInstalledTables[] = $tableName;
            }
        }

        return $notInstalledTables;
    }

    // ########################################

    private function getGeneralTables()
    {
        return array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_synchronization_config',
            'm2epro_wizard',
            'm2epro_marketplace',
            'm2epro_amazon_marketplace',
            'm2epro_ebay_marketplace',
            'm2epro_buy_marketplace',
            'm2epro_play_marketplace'
        );
    }

    // ########################################

    private function isTableExists($tableName)
    {
        if (isset($this->existsTables[$tableName])) {
            return $this->existsTables[$tableName];
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableExistsSql = $connRead->quoteInto("SHOW TABLE STATUS LIKE ?", $tableName);

        $this->existsTables[$tableName] = $connRead->fetchRow($tableExistsSql) !== false;

        return $this->existsTables[$tableName];
    }

    // ########################################
}