<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Tools_M2ePro_InstallController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
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
        <input type='button' value="Repair" onclick ="setLocation('{$baseUrl}?table[]={$parentTable}')" />
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
        <input type='button' value="Repair" onclick ="setLocation('{$baseUrl}?table[]={$childrenTable}')" />
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
}