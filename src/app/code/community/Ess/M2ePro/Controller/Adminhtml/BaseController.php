<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Controller_Adminhtml_BaseController extends Mage_Adminhtml_Controller_Action
{
    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        if (is_null(Mage::helper('M2ePro')->getGlobalValue('request_params'))) {
            Mage::helper('M2ePro')->setGlobalValue('request_params',$this->getRequest()->getParams());
        }

        if (is_null(Mage::helper('M2ePro')->getGlobalValue('base_controller'))) {
            Mage::helper('M2ePro')->setGlobalValue('base_controller',$this);
        }

        return $this;
    }

    //#############################################
}