<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Buy_Synchronization_Tasks
{
    //####################################

    /** @var Ess_M2ePro_Model_Config_Synchronization */
    private $config = NULL;

    //####################################

    public function __construct()
    {
        $this->config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();

        parent::__construct();
    }

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------

        $generalMode = $this->config->getGroupValue('/buy/orders/', 'mode');
        $receiveMode = $this->config->getGroupValue('/buy/orders/receive/', 'mode');
        $updateMode  = $this->config->getGroupValue('/buy/orders/update/', 'mode');

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