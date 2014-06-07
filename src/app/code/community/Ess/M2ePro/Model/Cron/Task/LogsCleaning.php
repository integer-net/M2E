<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Task_LogsCleaning extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'logs_cleaning';
    const MAX_MEMORY_LIMIT = 128;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //####################################

    protected function performActions()
    {
        /** @var $tempModel Ess_M2ePro_Model_Log_Cleaning */
        $tempModel = Mage::getModel('M2ePro/Log_Cleaning');

        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS);

        return true;
    }

    //####################################
}