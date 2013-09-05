<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Cron
{
    const RANDOM_INTERVAL_MIN = 3600;
    const RANDOM_INTERVAL_MAX = 86400;

    // ########################################

    public function process()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(256);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        $interval = Mage::helper('M2ePro/Module')->getCacheConfig()
                        ->getGroupValue('/servicing/', 'cron_interval');

        if (is_null($interval)) {

            $interval = rand(self::RANDOM_INTERVAL_MIN, self::RANDOM_INTERVAL_MAX);

            Mage::helper('M2ePro/Module')->getCacheConfig()
                ->setGroupValue('/servicing/', 'cron_interval', $interval);
        }

        Mage::getModel('M2ePro/Servicing_Dispatcher')->process((int)$interval);
    }

    // ########################################
}