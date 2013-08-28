<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_General_Profiler
{
    const MODE_PRODUCTION = 1;
    const MODE_DEBUGGING = 2;
    const MODE_DEVELOPING = 3;

    const TYPE_UNKNOWN = 0;
    const TYPE_NOTICE = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_WARNING = 3;
    const TYPE_ERROR = 4;

    const PRINT_TYPE_VAR_DUMP = 1;
    const PRINT_TYPE_PRINT_AND_BR = 2;
    const PRINT_TYPE_PRINT_AND_EOL = 3;

    private $_state = true;

    private $_startDate = NULL;
    private $_startTime = NULL;
    private $_endDate = NULL;
    private $_endTime = NULL;

    private $_fixedTimePoints = array();

    private $_printType = NULL;

    private $_printLog = false;
    private $_writeLog = false;

    private $_printInfo = false;
    private $_printTime = false;

    private $_logFileModel = NULL;

    private $_writeInfo = false;
    private $_writeTime = false;

    private $_leftPadding = 0;

    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        !isset($params['folder_log_file']) && $params['folder_log_file'] = NULL;
        $params['folder_log_file'] === '' && $params['folder_log_file'] = NULL;

        if (!isset($params['name_log_file']) || $params['name_log_file'] === '') {
            throw new Exception('The name of log file is not specified.');
        }

        $tempParams = array(
            'folder' => $params['folder_log_file'],
            'name_file' => $params['name_log_file']
        );
        $this->_logFileModel = Mage::getModel('M2ePro/General_LogFile',$tempParams);

        if (is_null($this->_printType)) {
            $this->_printType = self::PRINT_TYPE_VAR_DUMP;
        }
    }

    // ########################################

    public function setProductionMode()
    {
        $this->_printLog = false;
        $this->_writeLog = false;

        $this->_printInfo = false;
        $this->_printTime = false;

        $this->_writeInfo = false;
        $this->_writeTime = false;
    }

    public function setDebuggingMode()
    {
        $this->_printLog = false;
        $this->_writeLog = true;

        $this->_printInfo = false;
        $this->_printTime = false;

        $this->_writeInfo = true;
        $this->_writeTime = true;
    }

    public function setDevelopingMode()
    {
        $this->_printLog = true;
        $this->_writeLog = true;

        $this->_printInfo = true;
        $this->_printTime = true;

        $this->_writeInfo = true;
        $this->_writeTime = true;
    }

    //---------------------

    public function setPrintType($type)
    {
        $this->_printType = (int)$type;
    }

    //---------------------

    public function enable()
    {
        $this->_state = true;
    }

    public function disable()
    {
        $this->_state = false;
    }

    //---------------------

    public function start()
    {
        if (!$this->_state) {
            return;
        }

        $this->_startDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->_startTime = $this->getCurrentMicroTime();

        $this->_endDate = NULL;
        $this->_endTime = NULL;

        $this->_fixedTimePoints = array();

        $this->printLog('================================================');
        $this->writeLog('================================================');

        $messageStart = 'Start Profiler ['.$this->_startDate.']';
        $this->printLog($messageStart);
        $this->writeLog($messageStart);

        $this->printLog('================================================');
        $this->writeLog('================================================');
    }

    public function stop()
    {
        if (!$this->_state) {
            return;
        }

        $this->_endDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->_endTime = $this->getCurrentMicroTime();

        $this->printLog('');
        $this->writeLog('');

        $this->printLog('================================================');
        $this->writeLog('================================================');

        $messageEnd = 'End Profiler ['.$this->_endDate.']';
        $this->printLog($messageEnd);
        $this->writeLog($messageEnd);

        $this->printLog('================================================');
        $this->writeLog('================================================');

        $title = 'Total time: '.$this->getIntervalInSeconds($this->_startTime,$this->_endTime);

        if ($this->_printTime) {
            $this->printLog($title);
        }

        if ($this->_writeTime) {
            $this->writeLog($title);
        }

        $this->printLog('');
        $this->writeLog('');
    }

    //---------------------

    public function clearResourcesAfterEnd()
    {
        $this->_logFileModel->makeShutdownFunction();
    }

    // ########################################

    private function printLog($message)
    {
        if (!$this->_printLog) {
            return;
        }

        if ($this->_printType == self::PRINT_TYPE_PRINT_AND_BR) {

            for ($i=0;$i<$this->_leftPadding;$i++) {
                $message = '&nbsp;'.$message;
            }
            print($message.'<br/>');

        } elseif ($this->_printType == self::PRINT_TYPE_PRINT_AND_EOL) {

            for ($i=0;$i<$this->_leftPadding;$i++) {
                $message = ' '.$message;
            }
            print($message.PHP_EOL);

        } else {

            for ($i=0;$i<$this->_leftPadding;$i++) {
                $message = ' '.$message;
            }
            var_dump($message);
        }
    }

    private function writeLog($message)
    {
        if (!$this->_writeLog) {
            return;
        }

        if (!$this->_logFileModel->isExist()) {
            $this->_logFileModel->create();
        }

        for ($i=0;$i<$this->_leftPadding;$i++) {
            $message = ' '.$message;
        }

        $this->_logFileModel->addLine($message);
    }

    // ########################################

    private function getCurrentMicroTime()
    {
        return microtime(true);
    }

    private function getIntervalInSeconds($fromMicroTime, $toMicroTime)
    {
        $timeStr = (string)round($toMicroTime - $fromMicroTime,2);
        strpos($timeStr,'.') === false && $timeStr .= '.00';
        $temp = explode('.',$timeStr);
        strlen($temp[1]) < 2 && $timeStr = str_replace('.'.$temp[1],'.'.$temp[1].'0',$timeStr);
        return $timeStr;
    }

    // ########################################

    public function addEol()
    {
        if (!$this->_state) {
            return;
        }

        if ($this->_printInfo) {
            $this->printLog('');
        }

        if ($this->_writeInfo) {
            $this->writeLog('');
        }
    }

    public function addTitle($title, $type = self::TYPE_UNKNOWN)
    {
        if (!$this->_state) {
            return;
        }

        switch ($type)
        {
            case self::TYPE_UNKNOWN:
                $type = '';
                break;
            case self::TYPE_NOTICE:
                $type = 'Notice';
                break;
            case self::TYPE_ERROR:
                $type = 'Error';
                break;
            case self::TYPE_WARNING:
                $type = 'Warning';
                break;
            case self::TYPE_SUCCESS:
                $type = 'Success';
                break;

            default:
                return false;
        }

        if ($type != self::TYPE_UNKNOWN) {
            $type = '['.$type.']';
            $message = $type.' '.$title;
        } else {
            $message = $title;
        }

        if ($this->_printInfo) {
            $this->printLog($message);
        }

        if ($this->_writeInfo) {
            $this->writeLog($message);
        }
    }

    // ########################################

    public function addTimePoint($id, $title = NULL)
    {
        if (!$this->_state) {
            return;
        }

        foreach ($this->_fixedTimePoints as &$point) {
            if ($point['id'] == $id) {
                $point['time'] = $this->getCurrentMicroTime();
                return true;
            }
        }

        if (is_null($title)) {
            $title = 'Unknown point ('.(count($this->_fixedTimePoints)+1).')';
        }

        $this->_fixedTimePoints[] = array(
            'id' => $id,
            'title' => $title,
            'time' => $this->getCurrentMicroTime()
        );

        return true;
    }

    public function updateTimePoint($id)
    {
        if (!$this->_state) {
            return;
        }

        foreach ($this->_fixedTimePoints as $point) {
            if ($point['id'] == $id) {
                $point['time'] = $this->getCurrentMicroTime();
                return true;
            }
        }

        return false;
    }

    public function saveTimePoint($id)
    {
        if (!$this->_state) {
            return;
        }

        foreach ($this->_fixedTimePoints as $point) {
            if ($point['id'] == $id) {

                $title = $point['title'].': '.$this->getIntervalInSeconds(
                    $point['time'],$this->getCurrentMicroTime()
                ).' sec.';

                if ($this->_printTime) {
                    $this->printLog($title);
                }

                if ($this->_writeTime) {
                    $this->writeLog($title);
                }

                return true;
            }
        }

        return false;
    }

    // ########################################

    public function increaseLeftPadding($nbspCount = 1)
    {
        $this->_leftPadding += (int)$nbspCount;

        if ($this->_leftPadding < 0) {
            $this->_leftPadding = 0;
        }
    }

    public function decreaseLeftPadding($nbspCount = 1)
    {
        $this->_leftPadding -= (int)$nbspCount;

        if ($this->_leftPadding < 0) {
            $this->_leftPadding = 0;
        }
    }

    // ########################################
}