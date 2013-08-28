<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Upgrade_RepairTables extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function renderBrokenTables()
    {
        $horizontalTables = Mage::helper('M2ePro/Module')->getHorizontalTables();

        $brokenParentsTablesHtml = '';
        $brokenChildrenTablesHtml = '';

        $baseUrl = Mage::helper('adminhtml')->getUrl('*/*/*');

        foreach ($horizontalTables as $parentTable => $childTables) {

            $brokenItemsCount = $this->getBrokenRecordsInfo($parentTable, true);

            ($brokenItemsCount > 0) && $brokenParentsTablesHtml .= "
                <tr>

                    <td class='tableName'>
                        {$parentTable}
                    </td>

                    <td class='value'>
                        {$brokenItemsCount}
                    </td>

                    <td class='value'>
                        <input type='button' value='Repair' onclick =\"location.href='{$baseUrl}?table[]={$parentTable}'\" />
                    </td>

                    <td class='value'>
                        <input type='checkbox' name='table[]' value='{$parentTable}' />
                    </td>

                </tr>";

            foreach ($childTables as $childTable) {

                $brokenItemsCount = $this->getBrokenRecordsInfo($childTable, true);

                ($brokenItemsCount > 0) && $brokenChildrenTablesHtml .= "
                    <tr>

                        <td class='tableName'>
                            {$childTable}
                        </td>

                        <td class='value'>
                            {$brokenItemsCount}
                        </td>

                        <td class='value'>
                            <input type='button' value='Repair' onclick =\"location.href='{$baseUrl}?table[]={$childTable}'\" />
                        </td>

                         <td class='value'>
                            <input type='checkbox' name='table[]' value='{$childTable}' />
                        </td>

                    </tr>";
            }
        }

        if ($brokenParentsTablesHtml == '' && $brokenChildrenTablesHtml == '') {
            echo 'All is OK. Tables are synchronized';
            exit();
        }

        ($brokenParentsTablesHtml != '') && $brokenParentsTablesHtml = '
            <tr bgcolor="#E7E7E7">
                <td width="450" colspan="4">
                    <span class="blue"><b>Parents Tables</b></span>
                </td>
            </tr>
            <tr>
                <td><b>Name</b></td>
                <td align="center"><b>Count</b></td>
            </tr>'
            .$brokenParentsTablesHtml;

        ($brokenChildrenTablesHtml != '') && $brokenChildrenTablesHtml = '
            <tr bgcolor="#E7E7E7">
                <td width="450" colspan="4">
                    <span class="blue"><b>Children Tables</b></span>
                </td>
            </tr>
            <tr>
                <td><b>Name</b></td>
                <td align="center"><b>Count</b></td>
            </tr>'
            .$brokenChildrenTablesHtml;

        echo $html = "
        <html>
            <head>
                <style>
                    .blue {
                        color: #586A88;
                    }
                    .tableName {
                        width: 400px;
                    }
                    .value {
                        width: 50px;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <form method='GET' action='{$baseUrl}'>
                    <table>
                        {$brokenParentsTablesHtml}
                        {$brokenChildrenTablesHtml}
                        <tr>
                            <td colspan='4'><hr/></td>
                        </tr>
                        <tr>
                            <td colspan='4' align='right'>
                                <input type='submit' value='Repair Checked'>
                            <td>
                        </tr>
                    </table>
                </form>
            </body>
        </html>";
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
        $allTables = Mage::helper('M2ePro/Module')->getHorizontalTables();

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
        $allTables = Mage::helper('M2ePro/Module')->getHorizontalTables();

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