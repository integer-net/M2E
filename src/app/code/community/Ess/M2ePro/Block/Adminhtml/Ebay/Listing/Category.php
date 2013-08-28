<?php

    /*
    * @copyright  Copyright (c) 2011 by  ESS-UA.
    */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategory');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category.phtml');
    }
}