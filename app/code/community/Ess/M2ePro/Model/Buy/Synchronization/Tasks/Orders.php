<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Buy_Synchronization_Tasks
{
    //####################################

    private $configGroup = '/buy/synchronization/settings/orders/';

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $generalMode = $config->getGroupValue($this->configGroup, 'mode');
        $receiveMode = $config->getGroupValue($this->configGroup . 'receive/', 'mode');
        $updateMode = $config->getGroupValue($this->configGroup . 'update/', 'mode');

        if (!$generalMode || (!$receiveMode && !$updateMode)) {
            return;
        }
        //-----------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($receiveMode) {
            $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders_Receive();
            $tempSynch->process();
        }

        if ($updateMode) {
            $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders_Update();
            $tempSynch->process();
        }
        //---------------------------
    }

    //####################################
}