<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Amazon extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'marketplace',
        'synchronization',
        'account'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Amazon')->isActive();
    }

    // ########################################

    public function disableChildWizards()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        $wizardHelper->setStatus('amazonNewAsin', Ess_M2ePro_Helper_Wizard::STATUS_SKIPPED);

        return true;
    }

    // ########################################
}