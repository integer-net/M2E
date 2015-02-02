<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Tools_M2ePro_InstallController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Show Installation History"
     * @description "Show History of Install/Upgrade Module"
     * @new_line
     */
    public function showInstallationVersionHistoryAction()
    {
        /** @var $cacheConfigCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $cacheConfigCollection = Mage::helper('M2ePro/Module')->getCacheConfig()->getCollection();
        $cacheConfigCollection->addFieldToFilter('`group`', '/installation/version/history/');
        $cacheConfigCollection->getSelect()->order(
            array('create_date DESC', 'key DESC')
        );

        $history = $cacheConfigCollection->toArray();
        $history = $history['items'];

        if (count($history) <= 0) {
            echo $this->getEmptyResultsHtml('Installation History is not available.');
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<style>
    .grid td.color-first  { background-color: rgba(136, 227, 53, 0); }
    .grid td.color-second { background-color: rgba(255, 217, 97, 0.27); }
    .grid td  { text-align: center; }
</style>

<h2 style="margin: 20px 0 0 10px">Installation History
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 100px">Version From</th>
        <th style="width: 100px">Version To</th>
        <th style="width: 200px">Date</th>
    </tr>
HTML;
        $tdClass = 'color-first';
        $previousItemDate = $history[0]['create_date'];

        foreach ($history as $item) {

            !$item['value'] && $item['value'] = '--';

            if ((strtotime($previousItemDate) - strtotime($item['value'])) > 360) {
                $tdClass = $tdClass != 'color-second' ? 'color-second' : 'color-first';
            }
            $previousItemDate = $item['create_date'];

            $html .= <<<HTML
<tr>
    <td class="{$tdClass}">{$item['value']}</td>
    <td class="{$tdClass}">{$item['key']}</td>
    <td class="{$tdClass}">{$item['create_date']}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        print str_replace('%count%', count($history), $html);
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

            $connWrite->update(
                Mage::getSingleton('core/resource')->getTableName('core_resource'),
                array(
                    'version'      => $version,
                    'data_version' => $version
                ),
                array('code = ?' => 'M2ePro_setup')
            );

            Mage::helper('M2ePro/Magento')->clearCache();

            $this->_getSession()->addSuccess('Extension upgrade was successfully completed.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());

            return;
        }

        $urlPhpInfo = Mage::helper('adminhtml')->getUrl('*/*/*', array('upgrade' => 'yes'));

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
            echo $this->getEmptyResultsHtml('No files info for this M2E version on server.');
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
        <th style="width: 600px">Path</th>
        <th>Reason</th>
        <th>Action</th>
    </tr>
HTML;
        foreach ($problems as $item) {
            $url = Mage::helper('adminhtml')->getUrl('*/*/filesDiff',
                                                     array('filePath' => base64_encode($item['path'])));

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
     * @title "Check Tables Structure Validity"
     * @description "Check Tables Structure Validity"
     */
    public function checkTablesStructureValidityAction()
    {
        $tablesInfo = Mage::helper('M2ePro/Module_Database_Structure')->getTablesInfo();

        $responseData = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                            ->processVirtual('tables','get','diff',
                                             array('tables_info' => json_encode($tablesInfo)));

        if (!isset($responseData['diff'])) {
            echo $this->getEmptyResultsHtml('No tables info for this M2E version on server.');
            return;
        }

        if (count($responseData['diff']) <= 0) {
            echo $this->getEmptyResultsHtml('All tables are valid.');
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Tables Structure Validity
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 400px">Table</th>
        <th>Problem</th>
        <th style="width: 300px">Info</th>
        <th style="width: 100px">Actions</th>
    </tr>
HTML;

        foreach ($responseData['diff'] as $tableName => $checkResult) {
            foreach ($checkResult as $resultRow) {

                $additionalInfo = '';
                if (isset($resultRow['info']['diff_data'])) {
                    foreach ($resultRow['info']['diff_data'] as $diffCode => $diffValue) {
                        $additionalInfo .= "<b>{$diffCode}</b>: '{$diffValue}'. ";
                        $additionalInfo .= "<b>original:</b> '{$resultRow['info']['original_data'][$diffCode]}'.";
                        $additionalInfo .= "</br>";
                    }
                }

                $actionsHtml = '';
                if (isset($resultRow['info'])) {

                    $urlParams = array(
                        'table_name'  => $tableName,
                        'column_info' => json_encode($resultRow['info']['original_data'])
                    );

                    $diffData = isset($resultRow['info']['diff_data']) ? $resultRow['info']['diff_data'] : array();

                    if (empty($resultRow['info']['current_data']) ||
                        (isset($diffData['type']) || isset($diffData['default']) || isset($diffData['null']))) {

                        $urlParams['mode'] = 'properties';
                        $url = $this->getUrl('*/*/fixColumn', $urlParams);
                        $actionsHtml .= "<a href=\"{$url}\">Fix Properties</a>";
                    }

                    if (isset($resultRow['info']['diff_data']) && isset($diffData['key'])) {

                        $urlParams['mode'] = 'index';
                        $url = $this->getUrl('*/*/fixColumn', $urlParams);
                        $actionsHtml .= "<a href=\"{$url}\">Fix Index</a>";
                    }
                }

                $html .= <<<HTML
<tr>
    <td>{$tableName}</td>
    <td>{$resultRow['message']}</td>
    <td>&nbsp;{$additionalInfo}&nbsp;</td>
    <td>&nbsp;{$actionsHtml}&nbsp;</td>
</tr>
HTML;
            }
        }

        $html .= '</table>';
        print str_replace('%count%',count($responseData['diff']),$html);
    }

    /**
     * @title "Check Configs Validity"
     * @description "Check Configs Validity"
     */
    public function checkConfigsValidityAction()
    {
        $responseData = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                                ->processVirtual('configs','get','info');

        if (!isset($responseData['configs_info'])) {
            echo $this->getEmptyResultsHtml('No configs info for this M2E version on server.');
            return;
        }

        $helper = Mage::helper('M2ePro/Module_Database_Structure');
        $differenses = array();

        foreach ($responseData['configs_info'] as $tableName => $configInfo) {

            $currentInfo = $helper->getConfigSnapshot($tableName);
            foreach ($configInfo as $codeHash => $item) {
                !array_key_exists($codeHash, $currentInfo) && $differenses[] = array(
                    'table' => $tableName,
                    'item'  => $item
                );
            }
        }

        if (count($differenses) <= 0) {
            echo $this->getEmptyResultsHtml('All Configs are valid.');
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Configs Validity
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 400px">Table</th>
        <th>Group</th>
        <th>Key</th>
        <th style="width: 100px">Actions</th>
    </tr>
HTML;

        foreach ($differenses as $index => $row) {

            $url = $this->getUrl('*/adminhtml_development_database/addTableRow', array(
                'table'  => $row['table'],
                'model'  => Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($row['table']),
            ));

            $onclickAction = <<<JS
var elem = $(this.id);
new Ajax.Request( '{$url}' , {
    method: 'get',
    asynchronous : false,
    parameters : Form.serialize(elem.up('form')),
    onSuccess: function(transport) { elem.up('tr').remove(); }
});
JS;
        $html .= <<<HTML
<tr>
    <td>{$row['table']}</td>
    <td>{$row['item']['group']}</td>
    <td>{$row['item']['key']}</td>
    <td>
        <form style="margin-bottom: 0;">
            <input type="checkbox" name="cells[]" value="group" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="key" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="value" style="display: none;" checked="checked">

            <input type="hidden" name="value_group" value="{$row['item']['group']}">
            <input type="hidden" name="value_key" value="{$row['item']['key']}">
            <input type="text" name="value_value" value="{$row['item']['value']}">

            <a id="insert_id_{$index}" onclick="{$onclickAction}" href="javascript:void(0);">Insert</a>
        </form>
    </td>
</tr>
HTML;
        }

        $html .= '</table>';
        print str_replace('%count%',count($differenses),$html);
    }

    // ----------------------------------------

    /**
     * @hidden
     */
    public function fixColumnAction()
    {
        $tableName  = $this->getRequest()->getParam('table_name');
        $columnInfo = $this->getRequest()->getParam('column_info');
        $columnInfo = (array)json_decode($columnInfo, true);

        $repairMode = $this->getRequest()->getParam('mode');

        if (!$tableName || !$repairMode || empty($columnInfo)) {
            $this->_redirect('*/*/checkTablesStructureValidity');
            return;
        }

        $helper = Mage::helper('M2ePro/Module_Database_Repair');
        $repairMode == 'index' && $helper->fixColumnIndex($tableName, $columnInfo);
        $repairMode == 'properties' && $helper->fixColumnProperties($tableName, $columnInfo);

        $this->_redirect('*/*/checkTablesStructureValidity');
    }

    /**
     * @title "Files Diff"
     * @description "Files Diff"
     * @hidden
     */
    public function filesDiffAction()
    {
        $filePath     = base64_decode($this->getRequest()->getParam('filePath'));
        $originalPath = base64_decode($this->getRequest()->getParam('originalPath'));

        $params = array(
            'content' => file_get_contents(Mage::getBaseDir() . '/' . $filePath),
            'path'    => $originalPath ? $originalPath : $filePath
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
            echo $this->getEmptyResultsHtml('No UnWritable Directories');
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
        <th style="width: 800px">Path</th>
    </tr>
HTML;
        foreach ($unWritableDirectories as $item) {

            $html .= <<<HTML
<tr>
    <td>{$item}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        print str_replace('%count%',count($unWritableDirectories),$html);
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

        $moduleTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();

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

    private function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }

    //#############################################
}