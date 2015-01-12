<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_ListingSwitcher extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewListingSwitcher');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/view/listing_switcher.phtml');
    }
}