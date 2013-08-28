<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Exception extends Mage_Core_Helper_Abstract
{
    const FILTER_TYPE_TYPE = 1;
    const FILTER_TYPE_INFO = 2;

    // ########################################

    public function process(Exception $exception)
    {
        try {

            $temp = Mage::helper('M2ePro')->getGlobalValue('send_exception_to_server');
            if (!empty($temp)) {
                return;
            }
            Mage::helper('M2ePro')->setGlobalValue('send_exception_to_server', true);

            if ((bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/debug/exceptions/','send_to_server')) {

                $type = get_class($exception);

                $info = $this->getExceptionInfo($exception, $type);
                $info .= $this->getExceptionStackTraceInfo($exception);
                $info .= $this->getCurrentUserActionInfo();
                $info .= $this->getGeneralSummaryInfo();

                if ($this->isExceptionFiltered($info, $type)) {
                    return;
                }

                $this->send($info, $type);
            }

            Mage::helper('M2ePro')->unsetGlobalValue('send_exception_to_server');

        } catch (Exception $exceptionTemp) {}
    }

    public function processFatal($error, $traceInfo)
    {
        try {

            $temp = Mage::helper('M2ePro')->getGlobalValue('send_exception_to_server');
            if (!empty($temp)) {
                return;
            }
            Mage::helper('M2ePro')->setGlobalValue('send_exception_to_server', true);

            if ((bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/debug/fatal_error/','send_to_server')) {

                $type = 'Fatal Error';

                $info = $this->getFatalInfo($error, $type);
                $info .= $traceInfo;
                $info .= $this->getCurrentUserActionInfo();
                $info .= $this->getGeneralSummaryInfo();

                if ($this->isExceptionFiltered($info, $type)) {
                    return;
                }

                $this->send($info, $type);
            }

            Mage::helper('M2ePro')->unsetGlobalValue('send_exception_to_server');

        } catch (Exception $exceptionTemp) {}
    }

    //-----------------------------------------

    public function setFatalErrorHandler()
    {
        $temp = Mage::helper('M2ePro')->getGlobalValue('set_fatal_error_handler');
        if (!empty($temp)) {
            return;
        }
        Mage::helper('M2ePro')->setGlobalValue('set_fatal_error_handler', true);

        $functionCode = '$error = error_get_last();

                         if (is_null($error)) {
                             return;
                         }

                         $fatalErrors = array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR);

                         if (in_array((int)$error[\'type\'], $fatalErrors)) {
                             $stackTrace = @debug_backtrace(false);
                             $traceInfo = Mage::helper(\'M2ePro/Exception\')->getFatalStackTraceInfo($stackTrace);
                             Mage::helper(\'M2ePro/Exception\')->processFatal($error,$traceInfo);
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

    public function getGeneralSummaryInfo()
    {
        $locationInfo = array();
        $locationInfo['domain'] = Mage::helper('M2ePro/Server')->getDomain();
        $locationInfo['ip'] = Mage::helper('M2ePro/Server')->getIp();
        $locationInfo['directory'] = Mage::helper('M2ePro/Server')->getBaseDirectory();

        $platformInfo = array();
        $platformInfo['name'] = Mage::helper('M2ePro/Magento')->getName();
        $platformInfo['edition'] = Mage::helper('M2ePro/Magento')->getEditionName();
        $platformInfo['version'] = Mage::helper('M2ePro/Magento')->getVersion();
        $platformInfo['revision'] = Mage::helper('M2ePro/Magento')->getRevision();

        $moduleInfo = array();
        $moduleInfo['name'] = Mage::helper('M2ePro/Module')->getName();
        $moduleInfo['version'] = Mage::helper('M2ePro/Module')->getVersion();
        $moduleInfo['revision'] = Mage::helper('M2ePro/Module')->getRevision();

        $phpInfo = Mage::helper('M2ePro/Server')->getPhpSettings();
        $phpInfo['api'] = Mage::helper('M2ePro/Server')->getPhpApiName();
        $phpInfo['version'] = Mage::helper('M2ePro/Server')->getPhpVersion();

        $mysqlInfo = Mage::Helper('M2ePro/Server')->getMysqlSettings();
        $mysqlInfo['api'] = Mage::helper('M2ePro/Server')->getMysqlApiName();
        $prefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $mysqlInfo['prefix'] = $prefix != '' ? $prefix : 'Disabled';
        $mysqlInfo['version'] = Mage::helper('M2ePro/Server')->getMysqlVersion();
        $mysqlInfo['database'] = Mage::helper('M2ePro/Magento')->getDatabaseName();

        $additionalInfo = array();
        $additionalInfo['system'] = Mage::helper('M2ePro/Server')->getSystem();
        $additionalInfo['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A';
        $additionalInfo['admin'] = Mage::helper('M2ePro')->getGlobalValue('base_controller')
            ? Mage::helper('adminhtml')->getUrl('adminhtml')
            : 'N/A';

        $info = <<<DATA
-------------------------------- PLATFORM INFO -----------------------------------
Name: {$platformInfo['name']}
Edition: {$platformInfo['edition']}
Version: {$platformInfo['version']}
Revision: {$platformInfo['revision']}

-------------------------------- MODULE INFO -------------------------------------
Name: {$moduleInfo['name']}
Version: {$moduleInfo['version']}
Revision: {$moduleInfo['revision']}

-------------------------------- LOCATION INFO -----------------------------------
Domain: {$locationInfo['domain']}
Ip: {$locationInfo['ip']}
Directory: {$locationInfo['directory']}

-------------------------------- PHP INFO ----------------------------------------
Version: {$phpInfo['version']}
Api: {$phpInfo['api']}
Memory Limit: {$phpInfo['memory_limit']}
Max Execution Time: {$phpInfo['max_execution_time']}

-------------------------------- MYSQL INFO --------------------------------------
Version: {$mysqlInfo['version']}
Api: {$mysqlInfo['api']}
Database: {$mysqlInfo['database']}
Tables Prefix: {$mysqlInfo['prefix']}
Connection Timeout: {$mysqlInfo['connect_timeout']}
Wait Timeout: {$mysqlInfo['wait_timeout']}

------------------------------ ADDITIONAL INFO -----------------------------------
System Name: {$additionalInfo['system']}
User Agent: {$additionalInfo['user_agent']}
Admin Panel: {$additionalInfo['admin']}
DATA;

        return $info;
    }

    // ########################################

    private function send($info, $type)
    {
        Mage::getModel('M2ePro/Connector_Server_Api_Dispatcher')
                ->processVirtual('domain','add','exception',
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