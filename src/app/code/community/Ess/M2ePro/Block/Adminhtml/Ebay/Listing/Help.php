<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Help extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/help.phtml');
    }

    public function getContainerId()
    {
        return 'block_notice_ebay_listing';
    }
}