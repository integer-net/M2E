<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_BuyNewSku extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array();

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Buy')->isActive();
    }

    // ########################################
}