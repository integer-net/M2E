<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Server extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getEndpoint()
    {
        return $this->getBaseUrl().'index.php';
    }

    public function getBaseUrl()
    {
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','baseurl');
    }

    // ########################################

    public function getAdminKey()
    {
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue('/server/','admin_key');
    }

    public function getApplicationKey()
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        return (string)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$moduleName.'/server/','application_key'
        );
    }

    // ########################################
}