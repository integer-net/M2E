<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Other_Help extends Mage_Adminhtml_Block_Widget
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingOtherHelp');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/listing/other/help.phtml');
    }

    public function getContainerId()
    {
        return 'block_notice_amazon_listings';
    }
}