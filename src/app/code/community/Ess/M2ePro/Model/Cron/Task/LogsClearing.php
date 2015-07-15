<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Task_LogsClearing extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'logs_clearing';
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
        /** @var $tempModel Ess_M2ePro_Model_Log_Clearing */
        $tempModel = Mage::getModel('M2ePro/Log_Clearing');

        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_OTHER_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_SYNCHRONIZATIONS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS);

        return true;
    }

    //####################################
}