<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Category extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingCategory');
        //------------------------------

        $this->setTemplate('M2ePro/common/buy/listing/category.phtml');
    }
}