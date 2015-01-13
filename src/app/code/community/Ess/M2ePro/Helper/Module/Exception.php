<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Exception extends Mage_Core_Helper_Abstract
{
    const FILTER_TYPE_TYPE = 1;
    const FILTER_TYPE_INFO = 2;

    // ########################################

    public function process(Exception $exception)
    {
        try {

            $temp = Mage::helper('M2ePro/Data_Global')->getValue('send_exception_to_server');
            if (!empty($temp)) {
                return;
            }
            Mage::helper('M2ePro/Data_Global')->setValue('send_exception_to_server', true);

            if ((bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/debug/exceptions/','send_to_server')) {

                $type = get_class($exception);

                $info = $this->getExceptionInfo($exception, $type);
                $info .= $this->getExceptionStackTraceInfo($exception);
                $info .= $this->getCurrentUserActionInfo();
                $info .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

                if ($this->isExceptionFiltered($info, $type)) {
                    return;
                }

                $this->send($info, $type);
            }

            Mage::helper('M2ePro/Data_Global')->unsetValue('send_exception_to_server');

        } catch (Exception $exceptionTemp) {}
    }

    public function processFatal($error, $traceInfo)
    {
        try {

            $temp = Mage::helper('M2ePro/Data_Global')->getValue('send_exception_to_server');
            if (!empty($temp)) {
                return;
            }
            Mage::helper('M2ePro/Data_Global')->setValue('send_exception_to_server', true);

            if ((bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/debug/fatal_error/','send_to_server')) {

                $type = 'Fatal Error';

                $info = $this->getFatalInfo($error, $type);
                $info .= $traceInfo;
                $info .= $this->getCurrentUserActionInfo();
                $info .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

                if ($this->isExceptionFiltered($info, $type)) {
                    return;
                }

                $this->send($info, $type);
            }

            Mage::helper('M2ePro/Data_Global')->unsetValue('send_exception_to_server');

        } catch (Exception $exceptionTemp) {}
    }

    //-----------------------------------------

    public function setFatalErrorHandler()
    {
        $temp = Mage::helper('M2ePro/Data_Global')->getValue('set_fatal_error_handler');

        if (!empty($temp)) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('set_fatal_error_handler', true);

        $functionCode = '$error = error_get_last();

                         if (is_null($error)) {
                             return;
                         }

                         $fatalErrors = array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR);

                         if (in_array((int)$error[\'type\'], $fatalErrors)) {
                             $trace = @debug_backtrace(false);
                             $traceInfo = Mage::helper(\'M2ePro/Module_Exception\')->getFatalStackTraceInfo($trace);
                             Mage::helper(\'M2ePro/Module_Exception\')->processFatal($error,$traceInfo);
                         }';

        $shutdownFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownFunction);
    }

    public function getUserMessage(Exception $exception)
    {
        return Mage::helper('M2ePro')->__('Fatal error occurred').': "'.$exception->getMessage().'".';
    }

    // ########################################

    private function getExceptionInfo(Exception $exception, $type)
    {
        $exceptionInfo = <<<EXCEPTION
-------------------------------- EXCEPTION INFO ----------------------------------
Type: {$type}
File: {$exception->getFile()}
Line: {$exception->getLine()}
Message: {$exception->getMessage()}
Code: {$exception->getCode()}


EXCEPTION;

        return $exceptionInfo;
    }

    private function getExceptionStackTraceInfo(Exception $exception)
    {
        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$exception->getTraceAsString()}


TRACE;

        return $stackTraceInfo;
    }

    //-----------------------------------------

    private function getFatalInfo($error, $type)
    {
        $exceptionInfo = <<<FATAL
-------------------------------- FATAL ERROR INFO --------------------------------
Type: {$type}
File: {$error['file']}
Line: {$error['line']}
Message: {$error['message']}


FATAL;

        return $exceptionInfo;
    }

    public function getFatalStackTraceInfo($stackTrace)
    {
        if (!is_array($stackTrace)) {
            $stackTrace = array();
        }

        $stackTrace = array_reverse($stackTrace);
        $info = '';

        if (count($stackTrace) > 1) {
            foreach ($stackTrace as $key => $trace) {
                $info .= "#{$key} {$trace['file']}({$trace['line']}):";
                $info .= " {$trace['class']}{$trace['type']}{$trace['function']}(";

                if (count($trace['args'])) {
                    foreach ($trace['args'] as $key => $arg) {
                        $key != 0 && $info .= ',';

                        if (is_object($arg)) {
                            $info .= get_class($arg);
                        } else {
                            $info .= $arg;
                        }
                    }
                }
                $info .= ")\n";
            }
        }

        if ($info == '') {
            $info = 'Unavailable';
        }

        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$info}


TRACE;

        return $stackTraceInfo;
    }

    //-----------------------------------------

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

    private function send($info, $type)
    {
        Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                ->processVirtual('exception','add','entity',
                                 array('info' => $info, 'type' => $type));
    }

    private function isExceptionFiltered($info, $type)
    {
        if (!(bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                            ->getGroupValue('/debug/exceptions/','filters_mode')) {
            return false;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_exceptions_filters');

        $exceptionFilters = $connRead->select()
                                     ->from($tableName,array('preg_match','type'))
                                     ->query()
                                     ->fetchAll();

        foreach ($exceptionFilters as $exceptionFilter) {

            try {

                if ($exceptionFilter['type'] == self::FILTER_TYPE_TYPE) {
                    $tempResult = preg_match($exceptionFilter['preg_match'],$type);
                } else {
                    $tempResult = preg_match($exceptionFilter['preg_match'],$info);
                }

            } catch (Exception $exception) {
                return false;
            }

            if ($tempResult) {
                return true;
            }
        }

        return false;
    }

    // ########################################
}