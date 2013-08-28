<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Ess extends Mage_Core_Helper_Abstract
{
    // ########################################

    /**
     * @return Ess_M2ePro_Model_Config_Ess
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Ess');
    }

    // ########################################

    public function getModules()
    {
        return $this->getConfig()->getAllGroupValues('/modules/');
    }

    // ########################################
}