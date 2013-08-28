<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Connector
{
    const MODE_SERVER = 'server';

    // ########################################

    public function getDispatcher()
    {
        $dispatcherObject = NULL;

        switch ($this->getCurrentMode()) {

            case self::MODE_SERVER:
                $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Buy_Dispatcher');
                break;
        }

        return $dispatcherObject;
    }

    public function getProductDispatcher()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Buy_Connector_Product_Dispatcher');
        $dispatcherObject->setConnectorMode($this->getCurrentMode());
        return $dispatcherObject;
    }

    // ########################################

    protected function getCurrentMode()
    {
        $componentNick = Ess_M2ePro_Helper_Component_Buy::NICK;
        $configObject = Mage::helper('M2ePro/Module')->getConfig();

        $mode = $configObject->getGroupValue('/'.$componentNick.'/connector/', 'mode');

        if (!in_array($mode,array(self::MODE_SERVER))) {
            $mode = self::MODE_SERVER;
        }

        return $mode;
    }

    // ########################################
}