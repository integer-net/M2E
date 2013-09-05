<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Profiler extends Ess_M2ePro_Model_General_Profiler
{
    const LOG_FILE_NAME = 'profiler';
    const LOG_FILE_FOLDER = 'synchronization';

    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $mode = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue('/settings/profiler/','mode');

        if ($mode == Ess_M2ePro_Model_General_Profiler::MODE_DEVELOPING) {

           if (isset($params['muteOutput']) && $params['muteOutput'] === true) {
                $this->setDebuggingMode();
           } else {
                $this->setDevelopingMode();
           }

        } elseif ($mode == Ess_M2ePro_Model_General_Profiler::MODE_DEBUGGING) {
            $this->setDebuggingMode();
        } else {
            $this->setProductionMode();
        }

        $printType = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/profiler/','print_type'
        );
        $this->setPrintType($printType);

        $paramsParent = array(
            'folder_log_file' => self::LOG_FILE_FOLDER,
            'name_log_file' => self::LOG_FILE_NAME
        );

        parent::__construct($paramsParent);
    }

    //####################################

    public function setClearResources()
    {
        $deleteResources = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/profiler/','delete_resources'
        );
        if ($deleteResources == 1) {
            $this->clearResourcesAfterEnd();
        }
    }

    public function makeShutdownFunction()
    {
        $functionCode = "Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->stop();";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}