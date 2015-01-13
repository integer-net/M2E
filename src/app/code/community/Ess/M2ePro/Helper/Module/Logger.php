<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Logger extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function process($logData, $type = 'undefined', $overrideExistsLog = false)
    {
        try {

            $logData = $this->prepareLogMessage($logData, $type);
            $this->log($logData, $type, $overrideExistsLog);

            $logData .= $this->getCurrentUserActionInfo();
            $logData .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

            $this->send($logData, $type);

        } catch (Exception $exceptionTemp) {}
    }

    // ########################################

    private function prepareLogMessage($logData, $type)
    {
        !is_string($logData) && $logData = print_r($logData, true);

        $logData = '[DATE] '.date('Y-m-d H:i:s',(int)gmdate('U')).PHP_EOL.
                   '[TYPE] '.$type.PHP_EOL.
                   '[MESSAGE] '.$logData.PHP_EOL.
                   str_repeat('#',80).PHP_EOL.PHP_EOL;

        return $logData;
    }

    private function log($logMessage, $type, $overrideExistsLog = false)
    {
        $varDir = new Ess_M2ePro_Model_VariablesDir(array('child_folder' => 'logs'));
        $varDir->create();

        $fileName = $varDir->getPath().$type.'.log';

        $overrideExistsLog
            ? file_put_contents($fileName, $logMessage)
            : file_put_contents($fileName, $logMessage, FILE_APPEND);
    }

    // ########################################

    private function getCurrentUserActionInfo()
    {
        $server = isset($_SERVER) ? print_r($_SERVER, true) : '';
        $get = isset($_GET) ? print_r($_GET, true) : '';
        $post = isset($_POST) ? print_r($_POST, true) : '';

        $actionInfo = <<<ACTION
-------------------------------- ACTION INFO -------------------------------------
SERVER: {$server}
GET: {$get}
POST: {$post}

ACTION;

        return $actionInfo;
    }

    // ########################################

    private function send($logData, $type)
    {
        Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                ->processVirtual('logger','add','entity', array('info' => $logData, 'type' => $type));
    }

    // ########################################
}