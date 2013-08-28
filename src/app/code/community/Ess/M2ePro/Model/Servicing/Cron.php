<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Cron
{
    const RANDOM_INTERVAL_MIN = 3600;
    const RANDOM_INTERVAL_MAX = 86400;

    // ########################################

    public function process()
    {
        Mage::helper('M2ePro/Server')->setMemoryLimit(256);
        Mage::helper('M2ePro/Exception')->setFatalErrorHandler();

        $interval = Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/cache/servicing/', 'cron_interval');

        if (is_null($interval)) {

            $interval = rand(self::RANDOM_INTERVAL_MIN, self::RANDOM_INTERVAL_MAX);

            Mage::helper('M2ePro/Module')->getConfig()
                ->setGroupValue('/cache/servicing/', 'cron_interval', $interval);
        }

        Mage::getModel('M2ePro/Servicing_Dispatcher')->process((int)$interval);
    }

    // ########################################
}