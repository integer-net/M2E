<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Tools_M2ePro_GeneralController
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
     * @title "Clear Cache"
     * @description "Clear extension cache"
     * @confirm "Are you sure?"
     */
    public function clearExtensionCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearCache();
        $this->_getSession()->addSuccess('Extension cache was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear Config Cache"
     * @description "Clear config cache"
     * @confirm "Are you sure?"
     * @new_line
     */
    public function clearConfigCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearConfigCache();
        $this->_getSession()->addSuccess('Config cache was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //#############################################

    /**
     * @title "Check Server Connection"
     * @description "Send test request to server and check connection"
     * @new_line
     */
    public function serverCheckConnectionAction()
    {
        $curlObject = curl_init();

        //set the server we are using
        curl_setopt($curlObject, CURLOPT_URL, Mage::helper('M2ePro/Server')->getEndpoint());

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query(array(),'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);

        echo '<h1>Response</h1><pre>';
        print_r($response);
        echo '</pre><h1>Report</h1><pre>';
        print_r(curl_getinfo($curlObject));
        echo '</pre>';

        echo '<h2 style="color:red;">Errors</h2>';
        echo curl_errno($curlObject) . ' ' . curl_error($curlObject) . '<br><br>';

        curl_close($curlObject);
    }

    //#############################################

    /**
     * @title "Check Files Validity"
     * @description "Check Files Validity"
     */
    public function checkFilesValidityAction()
    {
        $responseData = Mage::getModel('M2ePro/Connector_Server_M2ePro_Dispatcher')
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

            if (md5_file($baseDir . $info['path']) != $info['hash']) {
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
    </tr>
HTML;
        foreach ($problems as $item) {

            $html .= <<<HTML
<tr>
    <td>
        {$item['path']}
    </td>
    <td>
        {$item['reason']}
    </td>
</tr>

HTML;
        }

        $html .= '</table>';

        print str_replace('%count%',count($problems),$html);
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