<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_Cmd_SystemController
    extends Ess_M2ePro_Controller_Adminhtml_Cmd_SynchronizationController
{
    //#############################################

    /**
     * @title "PHP Info"
     * @description "View server phpinfo() information"
     * @group "System"
     * @new_line
     */
    public function phpInfoAction()
    {
        if ($this->getRequest()->getParam('frame')) {
            phpinfo();
            return;
        }

        $this->printBack();
        $urlPhpInfo = $this->getUrl('*/*/*', array('frame' => 'yes'));
        echo '<iframe src="' . $urlPhpInfo . '" style="width:100%; height:90%;" frameborder="no"></iframe>';
    }

    //#############################################

    /**
     * @title "ESS Configuration"
     * @description "Go to ess configuration edit page"
     * @group "System"
     */
    public function goToEditEssConfigAction()
    {
        $this->_redirect('*/adminhtml_config/ess');
    }

    /**
     * @title "M2ePro Configuration"
     * @description "Go to m2epro configuration edit page"
     * @group "System"
     * @new_line
     */
    public function goToEditM2eProConfigAction()
    {
        $this->_redirect('*/adminhtml_config/m2epro');
    }

    //#############################################

    /**
     * @title "Run Cron"
     * @description "Emulate starting cron"
     * @group "System"
     */
    public function runCronAction()
    {
        Mage::getModel('M2ePro/Cron')->process();
    }

    /**
     * @title "Update License"
     * @description "Send update license request to server"
     * @group "System"
     * @new_line
     */
    public function licenseUpdateAction()
    {
        Mage::getModel('M2ePro/Servicing_Dispatcher')->processTasks(array(
            Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
        ));

        Mage::helper('M2ePro')->setSessionValue('success_message', 'License status was successfully updated.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //#############################################

    /**
     * @title "Clear COOKIES"
     * @description "Clear all current cookies"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearCookiesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Cookies was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //----------------------------------------------

    /**
     * @title "Clear Extension Cache"
     * @description "Clear extension cache"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearExtensionCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearCache();
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Extension cache was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Clear Magento Cache"
     * @description "Clear magento cache"
     * @group "System"
     * @new_line
     * @confirm "Are you sure?"
     */
    public function clearMagentoCacheAction()
    {
        Mage::helper('M2ePro/Magento')->clearCache();
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Magento cache was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //#############################################

    /**
     * @title "Memory Limit Test"
     * @description "Memory Limit Test"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function testMemoryLimitAction()
    {
        ini_set('display_errors', 1);
        $array = array();
        while (1) $array[] = $array;
    }

    /**
     * @title "Execution Time Test"
     * @description "Execution Time Test"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function testExecutionTimeAction()
    {
        ini_set('display_errors', 1);
        $this->printBack();

        $seconds = (int)$this->getRequest()->getParam('seconds', 0);
        if ($seconds) {
            $i = 0;

            while ($i++ < $seconds) {
                sleep(1);
            }

            echo <<<HTML
<div>{$seconds} seconds passed successfully!</div><br>
HTML;
        }

        $url = $this->getUrl('*/*/*');

        return print <<<HTML
<form action="{$url}" method="get">
    <input type="text" name="seconds" class="input-text" value="180" style="text-align: right; width: 100px"/>
    <button type="submit">Test</button>
</form>
HTML;
    }

    //#############################################
}