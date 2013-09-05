<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Log_Cron
{
    // ########################################

    public function process()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(256);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        /** @var $tempModel Ess_M2ePro_Model_Log_Cleaning */
        $tempModel = Mage::getModel('M2ePro/Log_Cleaning');

        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS);
    }

    // ########################################
}