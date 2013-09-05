<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Helper_Module_Database_RepairTables extends Mage_Core_Helper_Abstract
{
    //####################################

    public function getBrokenTablesInfo()
    {
        $horizontalTables = Mage::helper('M2ePro/Module_Database')->getHorizontalTables();

        $brokenParentTables = array();
        $brokenChildrenTables = array();
        $totalBrokenTables = 0;

        foreach ($horizontalTables as $parentTable => $childrenTables) {

            if ($brokenItemsCount = $this->getBrokenRecordsInfo($parentTable, true)) {
                $brokenParentTables[$parentTable] = $brokenItemsCount;
                $totalBrokenTables++;
            }

            foreach ($childrenTables as $childrenTable) {

                if ($brokenItemsCount = $this->getBrokenRecordsInfo($childrenTable, true)) {
                    $brokenChildrenTables[$childrenTable] = $brokenItemsCount;
                    $totalBrokenTables++;
                }
            }
        }

        return $brokenTables = array(
            'parent'      => $brokenParentTables,
            'children'    => $brokenChildrenTables,
            'total_count' => $totalBrokenTables
        );
    }

    public function repairBrokenTables(array $tables)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($tables as $table) {

            $brokenIds = $this->getBrokenRecordsInfo($table);

            if (count($brokenIds) <= 0) {
                continue;
            }

            $brokenIds = array_slice($brokenIds,0,50000);

            $tablePrefix = Mage::getSingleton('core/resource')->getTableName($table);
            $primaryColumnName = $this->getPrimaryColumnName($table);

            $brokenIdsParts = array_chunk($brokenIds,1000);

            foreach ($brokenIdsParts as $brokenIdsPart) {
                if (count($brokenIdsPart) <= 0) {
                    continue;
                }
                $connWrite->delete($tablePrefix,'`'.$primaryColumnName.'` IN ('.implode (',',$brokenIdsPart).')');
            }

            $logTemp = "Table: {$table} ## Amount: ".count($brokenIds);
            Mage::log($logTemp, null, 'm2epro_repair_tables.log',true);
        }
    }

    //####################################

    private function getPrimaryColumnName($table)
    {
        $allTables = Mage::helper('M2ePro/Module_Database')->getHorizontalTables();

        foreach ($allTables as $parentTable => $childTables) {

            if ($table == $parentTable) {
                return 'id';
            }

            foreach ($childTables as $component => $childTable) {

                if ($table == $childTable) {
                    return str_replace('m2epro_'.$component.'_','',$childTable) . '_id';
                }
            }
        }

        return NULL;
    }

    private function getBrokenRecordsInfo($table, $returnOnlyCount = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $allTables = Mage::helper('M2ePro/Module_Database')->getHorizontalTables();

        $result = $returnOnlyCount ? 0 : array();

        foreach ($allTables as $parentTable => $childTables) {
            foreach ($childTables as $component => $childTable) {

                if (!in_array($table,array($parentTable,$childTable))) {
                    continue;
                }

                $parentTablePrefix = Mage::getSingleton('core/resource')->getTableName($parentTable);
                $childTablePrefix = Mage::getSingleton('core/resource')->getTableName($childTable);

                $parentPrimaryColumnName = $this->getPrimaryColumnName($parentTable);
                $childPrimaryColumnName = $this->getPrimaryColumnName($childTable);

                if ($table == $parentTable) {

                    $stmtQuery = $connRead->select()
                        ->from(array('parent' => $parentTablePrefix),
                        $returnOnlyCount ? new Zend_Db_Expr('count(*) as `count_total`') :
                                           array('id'=>$parentPrimaryColumnName))
                        ->joinLeft(array('child' => $childTablePrefix),
                        '`parent`.`'.$parentPrimaryColumnName.'` = `child`.`'.$childPrimaryColumnName.'`',array())
                        ->where('`parent`.`component_mode` = ?',$component)
                        ->where('`child`.`'.$childPrimaryColumnName.'` IS NULL')
                        ->query();

                } else if ($table == $childTable) {

                    $stmtQuery = $connRead->select()
                        ->from(array('child' => $childTablePrefix),
                        $returnOnlyCount ? new Zend_Db_Expr('count(*) as `count_total`') :
                                           array('id'=>$childPrimaryColumnName))
                        ->joinLeft(array('parent' => $parentTablePrefix),
                                   '`child`.`'.$childPrimaryColumnName.'` = `parent`.`'.$parentPrimaryColumnName.'`',
                                   array())
                        ->where('`parent`.`'.$parentPrimaryColumnName.'` IS NULL')
                        ->query();
                }

                if ($returnOnlyCount) {
                    $row = $stmtQuery->fetch();
                    $result += (int)$row['count_total'];
                } else {
                    while ($row = $stmtQuery->fetch()) {
                        $result[] = (int)$row['id'];
                    }
                }
            }
        }

        return $result;
    }

    //####################################
}