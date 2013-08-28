<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Ebay extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'marketplace',
        'synchronization',
        'otherListing',
        'account'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    // ########################################

    public function disableChildWizards()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        $wizardHelper->setStatus('ebayOtherListing', Ess_M2ePro_Helper_Wizard::STATUS_SKIPPED);
        $wizardHelper->setStatus('ebayPartsCompatibility', Ess_M2ePro_Helper_Wizard::STATUS_SKIPPED);

        return true;
    }

    // ########################################
}