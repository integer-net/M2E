<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Connector_Server extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getScriptPath()
    {
        $path = $this->getBaseUrl().$this->getDirectory();
        return str_replace(':/', '://', str_replace('//', '/', $path));
    }

    //------------------------------------------

    public function getBaseUrl()
    {
        return (string)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/server/','baseurl');
    }

    public function getDirectory()
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        return (string)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/'.$moduleName.'/server/','directory');
    }

    // ########################################

    public function getAdminKey()
    {
        return (string)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue('/server/','admin_key');
    }

    public function getApplicationKey()
    {
        $moduleName = Mage::helper('M2ePro/Module')->getName();
        return (string)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.$moduleName.'/server/','application_key'
        );
    }

    // ########################################
}