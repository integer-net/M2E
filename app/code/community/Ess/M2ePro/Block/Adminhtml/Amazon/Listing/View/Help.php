<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_View_Help extends Mage_Adminhtml_Block_Widget
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingViewHelp');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/listing/view/help.phtml');
    }
}