<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ListingTutorial
    extends Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation
{
    // ########################################

    protected function getStep()
    {
        return 'listingTutorial';
    }

    // ########################################

    protected function _beforeToHtml()
    {
        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('What Is An M2E Pro Listing?');
        //------------------------------

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}