<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Tools_AdditionalController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Memory Limit Test"
     * @description "Memory Limit Test"
     * @confirm "Are you sure?"
     */
    public function testMemoryLimitAction()
    {
        ini_set('display_errors', 1);

        $dir = Mage::getBaseDir('var') . DS . 'log' . DS;
        $file = 'm2epro_memory_limit.log';

        is_file($dir . $file) && unlink($dir . $file);

        $i = 0;
        $array = array();

        while (1)  {
            ($array[] = $array) && ((++$i % 100) == 0) && Mage::log(memory_get_usage(true) / 1000000 ,null,$file,1);
        }
    }

    /**
     * @title "Execution Time Test"
     * @description "Execution Time Test"
     * @new_line
     */
    public function testExecutionTimeAction()
    {
        ini_set('display_errors', 1);

        $seconds = (int)$this->getRequest()->getParam('seconds', null);

        $logDir = Mage::getBaseDir('var').DS.'log'.DS;
        $fileName = 'm2epro_execution_time.log';

        $isLogFileExists = is_file($logDir . $fileName);

        if ($seconds) {

            $isLogFileExists && unlink($logDir . $fileName);

            $i = 0;
            while ($i < $seconds) {
                sleep(1);
                ((++$i % 10) == 0) && Mage::log("{$i} seconds passed",null,$fileName,1);
            }

            echo "<div>{$seconds} seconds passed successfully!</div><br>";
        }

        if ($isLogFileExists) {

            $contentsRows = explode("\n",file_get_contents($logDir . $fileName));

            if (count($contentsRows) >= 2) {
                $lastRecord = trim($contentsRows[count($contentsRows)-2], "\r\n");
                echo "<button onclick=\"alert('{$lastRecord}')\">show prev. log</button>";
            }
        }

        $url = Mage::helper('adminhtml')->getUrl('*/*/*');

        return print <<<HTML
<form action="{$url}" method="get">
    <input type="text" name="seconds" class="input-text" value="180" style="text-align: right; width: 100px" />
    <button type="submit">Test</button>
</form>
HTML;
    }

    /**
     * @title "Clear APC Opcode"
     * @description "Clear APC Opcode"
     * @confirm "Are you sure?"
     */
    public function clearApcOpcodeAction()
    {
        if (!Mage::helper('M2ePro/Client_Cache')->isApcAvailable()) {
            $this->_getSession()->addError('APC not installed');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
            return;
        }

        apc_clear_cache('system');

        $this->_getSession()->addSuccess('APC opcode cache has been cleared');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear COOKIES"
     * @description "Clear all current cookies"
     * @confirm "Are you sure?"
     */
    public function clearCookiesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }
        $this->_getSession()->addSuccess('Cookies was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //#############################################
}