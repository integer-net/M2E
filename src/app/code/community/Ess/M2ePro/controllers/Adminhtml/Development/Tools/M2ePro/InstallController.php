<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Tools_M2ePro_InstallController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    private function getStyleHtml()
    {
        return <<<HTML
<style type="text/css">

    table.grid {
        border-color: black;
        border-style: solid;
        border-width: 1px 0 0 1px;
    }
    table.grid th {
        padding: 5px 20px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
        background-color: silver;
        color: white;
        font-weight: bold;
    }
    table.grid td {
        padding: 3px 10px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
    }

</style>
HTML;
    }

    //#############################################

    /**
     * @title "Check Upgrade to 3.2.0"
     * @description "Check extension installation"
     * @confirm "Are you sure?"
     */
    public function checkInstallationCacheAction()
    {
        /** @var $installerInstance Ess_M2ePro_Model_Upgrade_MySqlSetup */
        $installerInstance = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');

        /** @var $migrationInstance Ess_M2ePro_Model_Upgrade_Migration_ToVersion4 */
        $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion4');
        $migrationInstance->setInstaller($installerInstance);

        $migrationInstance->startSetup();
        $migrationInstance->migrate();
        $migrationInstance->endSetup();

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess('Check installation was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Repeat Upgrade > 3.2.0"
     * @description "Repeat Upgrade From Certain Version"
     * @new_line
     */
    public function recurringUpdateAction()
    {
        if ($this->getRequest()->getParam('upgrade')) {

            $version = $this->getRequest()->getParam('version');
            $version = str_replace(array(','),'.',$version);

            if (!version_compare('3.2.0',$version,'<=')) {
                $this->_getSession()->addError('Extension upgrade can work only from 3.2.0 version.');
                $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
                return;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

            $coreResourceTable = Mage::getSingleton('core/resource')->getTableName('core_resource');
            $bind = array('version'=>$version,'data_version'=>$version);
            $connWrite->update($coreResourceTable,$bind,array('code = ?'=>'M2ePro_setup'));

            Mage::helper('M2ePro/Magento')->clearCache();

            $this->_getSession()->addSuccess('Extension upgrade was successfully completed.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());

            return;
        }

        $urlPhpInfo = $this->getUrl('*/*/*', array('upgrade' => 'yes'));

        echo '<form method="GET" action="'.$urlPhpInfo.'">
                From version: <input type="text" name="version" value="3.2.0" />
                <input type="submit" title="Upgrade Now!" onclick="return confirm(\'Are you sure?\');" />
              </form>';
    }

    //#############################################

    /**
     * @title "Check Files Validity"
     * @description "Check Files Validity"
     */
    public function checkFilesValidityAction()
    {
        $responseData = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                                    ->processVirtual('files','get','info');

        if (count($responseData) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">No files info for this M2E version on server.</span></h2>';
            return;
        }

        $problems = array();

        $baseDir = Mage::getBaseDir() . '/';
        foreach ($responseData['files_info'] as $info) {

            if (!is_file($baseDir . $info['path'])) {
                $problems[] = array(
                    'path' => $info['path'],
                    'reason' => 'File is missing'
                );
                continue;
            }

            $fileContent = trim(file_get_contents($baseDir . $info['path']));
            $fileContent = str_replace(array("\r\n","\n\r",PHP_EOL), chr(10), $fileContent);

            if (md5($fileContent) != $info['hash']) {
                $problems[] = array(
                    'path' => $info['path'],
                    'reason' => 'Hash mismatch'
                );
                continue;
            }

        }

        if (count($problems) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">All files are valid.</span></h2>';
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Files Validity
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 600px">
            Path
        </th>
        <th>
            Reason
        </th>
        <th>
            Action
        </th>
    </tr>
HTML;
        foreach ($problems as $item) {
            $url = $this->getUrl('*/*/filesDiff', array('filePath' => base64_encode($item['path'])));

            $html .= <<<HTML
<tr>
    <td>
        {$item['path']}
    </td>
    <td>
        {$item['reason']}
    </td>
    <td style="text-align: center;">
        <a href="{$url}" target="_blank">Diff</a>
    </td>
</tr>

HTML;
        }

        $html .= '</table>';

        print str_replace('%count%',count($problems),$html);
    }

    /**
     * @title "Files Diff"
     * @description "Files Diff"
     * @hidden
     */
    public function filesDiffAction()
    {
        $filePath = base64_decode($this->getRequest()->getParam('filePath'));

        $params = array(
            'content' => file_get_contents(Mage::getBaseDir() . '/' . $filePath),
            'path' => $filePath
        );

        $responseData = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                                ->processVirtual('files','get','diff', $params);

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Files Difference
    <span style="color: #808080; font-size: 15px;">({$filePath})</span>
</h2>
<br>
HTML;

        if (isset($responseData['html'])) {
            $html .= $responseData['html'];
        } else {
            $html .= '<h1>&nbsp;&nbsp;No file on server</h1>';
        }

        echo $html;
    }

    /**
     * @title "Show UnWritable Directories"
     * @description "Show UnWritable Directories"
     * @new_line
     */
    public function showUnWritableDirectoriesAction()
    {
        $unWritableDirectories = Mage::helper('M2ePro/Module')->getUnWritableDirectories();

        if (count ($unWritableDirectories) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">No UnWritable Directories</span></h2>';
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">UnWritable Directories
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 800px">
            Path
        </th>
    </tr>
HTML;
        foreach ($unWritableDirectories as $item) {

            $html .= <<<HTML
<tr>
    <td>
        $item
    </td>
</tr>

HTML;
        }

        $html .= '</table>';

        print str_replace('%count%',count($unWritableDirectories),$html);
    }

    //#############################################

    /**
     * @title "Repair Broken Tables"
     * @description "Command for show and repair broken horizontal tables"
     */
    public function checkTablesAction()
    {
        $tableNames = $this->getRequest()->getParam('table');

        if ($tableNames != NULL) {
            Mage::helper('M2ePro/Module_Database_RepairTables')->repairBrokenTables($tableNames);
            $this->_redirectUrl($this->getUrl('*/*/checkTables/'));
        }

        $brokenTables = Mage::helper('M2ePro/Module_Database_RepairTables')->getBrokenTablesInfo();

        if ($brokenTables['total_count'] <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">No Broken Tables</span></h2>';
            return;
        }

        $baseUrl = Mage::helper('adminhtml')->getUrl('*/*/*');

        $html = <<<HTML
<html>
    <body>
        <h2 style="margin: 20px 0 0 10px">Broken Tables
            <span style="color: #808080; font-size: 15px;">({$brokenTables['total_count']} entries)</span>
        </h2>
        <br>
        <form method="GET" action="{$baseUrl}">
            <table class="grid" cellpadding="0" cellspacing="0">
HTML;
        if (count($brokenTables['parent'])) {

            $html .= <<<HTML
<tr bgcolor="#E7E7E7">
    <td colspan="4">
        <h4 style="margin: 0 0 0 10px">Parent Tables</h4>
    </td>
</tr>
<tr>
    <th style="width: 400">
        Table
    </th>
    <th style="width: 50">
        Count
    </th>
    <th style="width: 50">
    </th>
    <th style="width: 50">
    </th>
</tr>
HTML;
            foreach ($brokenTables['parent'] as $parentTable => $brokenItemsCount) {

                $html .= <<<HTML
<tr>
    <td>
        {$parentTable}
    </td>
    <td>
        {$brokenItemsCount}
    </td>
    <td>
        <input type='button' value="Repair" onclick ="location.href = '{$baseUrl}?table[]={$parentTable}'" />
    </td>
    <td>
        <input type="checkbox" name="table[]" value="{$parentTable}" />
    </td>
HTML;
            }
        }

        if (count($brokenTables['children'])) {

            $html .= <<<HTML
<tr height="100%">
    <td><div style="height: 10px;"></div></td>
</tr>
<tr bgcolor="#E7E7E7">
    <td colspan="4">
        <h4 style="margin: 0 0 0 10px">Children Tables</h4>
    </td>
</tr>
<tr>
    <th style="width: 400">
        Table
    </th>
    <th style="width: 50">
        Count
    </th>
    <th style="width: 50">
    </th>
    <th style="width: 50">
    </th>
</tr>
HTML;
            foreach ($brokenTables['children'] as $childrenTable => $brokenItemsCount) {

                $html .= <<<HTML
<tr>
    <td>
        {$childrenTable}
    </td>
    <td>
        {$brokenItemsCount}
    </td>
    <td>
        <input type='button' value="Repair" onclick ="location.href = '{$baseUrl}?table[]={$childrenTable}'" />
    </td>
    <td>
        <input type="checkbox" name="table[]" value="{$childrenTable}" />
    </td>
HTML;
            }
        }

        $html .= <<<HTML
                <tr>
                    <td colspan="4"><hr/></td>
                </tr>
                <tr>
                    <td colspan="4" align="right">
                        <input type="submit" value="Repair Checked">
                    <td>
                </tr>
            </table>
        </form>
    </body>
</html>
HTML;

        echo $html;
    }

    /**
     * @title "Remove Config Duplicates"
     * @description "Remove Configuration Duplicates"
     * @confirm "Are you sure?"
     * @new_line
     */
    public function removeConfigDuplicatesAction()
    {
        /** @var $installerInstance Ess_M2ePro_Model_Upgrade_MySqlSetup */
        $installerInstance = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');
        $installerInstance->removeConfigDuplicates();

        Mage::helper('M2ePro/Module')->clearCache();

        $this->_getSession()->addSuccess('Remove duplicates was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //#############################################

    /**
     * @title "Reset Module (Clear Installation)"
     * @description "Clear all M2ePro data tables, reset wizards"
     * @confirm "Are you sure?"
     */
    public function fullResetModuleStateAction()
    {
        $this->truncateModuleTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_primary_config'),
            array('value' => null),
            '`group` LIKE \'%license%\''
        );
        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_config'),
            array('value' => 1),
            '`key` = \'mode\' AND `group` LIKE \'/component/%\''
        );
        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_wizard'),
            array('status' => 0, 'step' => null),
            '`nick` <> \'migrationToV6\''
        );

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess('Full Reset Module State was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Reset Module (Without Wizards)"
     * @description "Clear all M2ePro data tables, set wizards as skipped"
     * @confirm "Are you sure?"
     */
    public function ResetModuleStateAndSkippingWizardsAction()
    {
        $this->truncateModuleTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_config'),
            array('value' => 1),
            '`key` = \'mode\' AND `group` LIKE \'/component/%\''
        );
        $connWrite->update(
            Mage::getSingleton('core/resource')->getTableName('m2epro_wizard'),
            array('status' => 3, 'step' => null)
        );

        Mage::helper('M2ePro/Magento')->clearCache();

        $this->_getSession()->addSuccess('Reset Module State was successfully completed.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //------------------------------------

    private function truncateModuleTables()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $moduleTables = Mage::helper('M2ePro/Module_Database')->getMySqlTables();

        $excludeTables = array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_synchronization_config',

            'm2epro_marketplace',
            'm2epro_amazon_marketplace',
            'm2epro_buy_marketplace',
            'm2epro_ebay_marketplace',
            'm2epro_play_marketplace',

            'm2epro_wizard'
        );

        $tablesForTruncate = array_diff($moduleTables, $excludeTables);
        foreach ($tablesForTruncate as $table) {
            $connWrite->delete(Mage::getSingleton('core/resource')->getTableName($table));
        }
    }

    //#############################################
}