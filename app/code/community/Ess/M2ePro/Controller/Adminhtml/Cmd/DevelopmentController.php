<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_Cmd_DevelopmentController
    extends Ess_M2ePro_Controller_Adminhtml_Cmd_SystemController
{
    //#############################################

    /**
     * @title "Run Processing Cron"
     * @description "Run Processing Cron"
     * @group "Development"
     * @new_line
     */
    public function cronProcessingTemporaryAction()
    {
        $this->printBack();
        Mage::getModel('M2ePro/Processing_Cron')->process();
    }

    //#############################################

    /**
     * @title "Check Upgrade to 3.2.0"
     * @description "Check extension installation"
     * @group "Development"
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

        Mage::helper('M2ePro')->setSessionValue('success_message', 'Check installation was successfully completed.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Repeat Upgrade > 3.2.0"
     * @description "Repeat Upgrade From Certain Version"
     * @group "Development"
     * @new_line
     */
    public function recurringUpdateAction()
    {
        if ($this->getRequest()->getParam('upgrade')) {

            $version = $this->getRequest()->getParam('version');
            $version = str_replace(array(','),'.',$version);

            if (!version_compare('3.2.0',$version,'<=')) {
                Mage::helper('M2ePro')->setSessionValue(
                    'error_message', 'Extension upgrade can work only from 3.2.0 version.'
                );
                $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
                return;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

            $coreResourceTable = Mage::getSingleton('core/resource')->getTableName('core_resource');
            $bind = array('version'=>$version,'data_version'=>$version);
            $connWrite->update($coreResourceTable,$bind,array('code = ?'=>'M2ePro_setup'));

            Mage::helper('M2ePro/Magento')->clearCache();

            Mage::helper('M2ePro')->setSessionValue('success_message', 'Extension upgrade was successfully completed.');
            $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));

            return;
        }

        $this->printBack();
        $urlPhpInfo = $this->getUrl('*/*/*', array('upgrade' => 'yes'));

        echo '<form method="GET" action="'.$urlPhpInfo.'">
                From version: <input type="text" name="version" value="3.2.0" />
                <input type="submit" title="Upgrade Now!" onclick="return confirm(\'Are you sure?\');" />
              </form>';
    }

    //#############################################

    /**
     * @title "Check Server Connection"
     * @description "Send test request to server and check connection"
     * @group "Development"
     */
    public function serverCheckConnectionAction()
    {
        $this->printBack();

        $curlObject = curl_init();

        //set the server we are using
        $serverUrl = Mage::helper('M2ePro/Connector_Server')->getScriptPath().'index.php';
        curl_setopt($curlObject, CURLOPT_URL, $serverUrl);

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

    /**
     * @title "Remove Config Duplicates"
     * @description "Remove Configuration Duplicates"
     * @group "Development"
     * @confirm "Are you sure?"
     */
    public function removeConfigDuplicatesAction()
    {
        /** @var $installerInstance Ess_M2ePro_Model_Upgrade_MySqlSetup */
        $installerInstance = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');
        $installerInstance->removeConfigDuplicates();

        Mage::helper('M2ePro/Module')->clearCache();

        Mage::helper('M2ePro')->setSessionValue('success_message', 'Remove duplicates was successfully completed.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Repair Broken Tables"
     * @description "Command for show and repair broken horizontal tables"
     * @group "Development"
     */
    public function checkTablesAction()
    {
        $tableNames = $this->getRequest()->getParam('table');

        if ($tableNames == NULL) {
            Mage::getModel('M2ePro/Upgrade_RepairTables')->renderBrokenTables();
        } else {
            Mage::getModel('M2ePro/Upgrade_RepairTables')->repairBrokenTables($tableNames);
            $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/checkTables/'));
        }
    }

    /**
     * @title "Change Maintenance Mode"
     * @description "Change Maintenance Mode"
     * @group "Development"
     * @new_line
     */
    public function changeMaintenanceModeAction()
    {
        if (Mage::helper('M2ePro/Module')->isMaintenanceEnabled()) {
            Mage::helper('M2ePro/Module')->disableMaintenance();
            Mage::helper('M2ePro')->getSessionValue('warning_message', true);
            $message = 'Maintenance was deactivated.';
        } else {
            Mage::helper('M2ePro/Module')->enableMaintenance();
            $message = 'Maintenance was activated.';
        }

        Mage::helper('M2ePro')->setSessionValue('success_message', $message);
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //#############################################

    /**
     * @title "Make Location Files"
     * @description "Make test russian and clear for translates files"
     * @group "Development"
     */
    public function localesMenuAction()
    {
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_cmd_controlPanel');

        $commands = array(
            0 => array(
                'title' => 'Make Single Locale File',
                'description' => 'All groups in single file',
                'group' => 'Locales',
                'url' => $this->getUrl('*/adminhtml_cmd/makeLocale/'),
                'content' => '$this->printBack();',
                'new_line' => false,
                'confirm' => 'Are you sure?',
                'components' => false
            ),
            1 => array(
                'title' => 'Make Groups Locale Files',
                'description' => 'Separate groups in differnt files',
                'group' => 'Locales',
                'url' => $this->getUrl('*/adminhtml_cmd/makeLocaleGroups/'),
                'content' => '$this->printBack();',
                'new_line' => false,
                'confirm' => 'Are you sure?',
                'components' => false
            ),
            2 => array(
                'title' => 'Make Differences',
                'description' => 'Make differences files between current locale files and out of date files.',
                'group' => 'Locales',
                'url' => $this->getUrl('*/adminhtml_cmd/makeDiffLocales/'),
                'content' => '$this->printBack();',
                'new_line' => false,
                'confirm' => '',
                'components' => false
            )
        );

        $blockParams = array(
            'groups' => array(
                'Locales' => $commands
            )
        );

        $block->setData($blockParams);
        echo $block->toHtml();
    }

    

    
    public function makeLocaleGroupsAction()
    {
        Mage::getModel('M2ePro/Build_Translator')->createGroupsTemplateLocaleFiles();
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/localesMenu/'));
    }

    
    public function makeDiffLocalesAction()
    {
        if (!is_null($this->getRequest()->getParam('action'))) {
            Mage::getModel('M2ePro/Build_Translator')->createLocaleDiffFiles(
                $_FILES['old_file']['tmp_name'], $_FILES['current_file']['tmp_name']
            );
            $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/localesMenu/'));
        }

        $formAction = $this->getUrl('*/adminhtml_cmd/makeDiffLocales', array('action' => 'make'));

        $this->printBack();

        $formKey = Mage::getSingleton('core/session')->getFormKey();

        echo '<form action="'.$formAction.'" method="post" enctype="MULTIPART/FORM-DATA">
                    <input type="hidden" name="form_key" value="'.$formKey.'">
                    <label for="current_file">Current File</label>
                    <input type="file" name="current_file" id="current_file">
                    <label for="old_file">Old File</label>
                    <input type="file" name="old_file" id="old_file">
                    <input type="submit" value="Make">
              </form>';
    }

    // -------------------------------

    

    //#############################################
}