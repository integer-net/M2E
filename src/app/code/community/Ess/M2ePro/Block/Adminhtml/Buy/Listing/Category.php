<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Category extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingCategory');
        //------------------------------

        $this->setTemplate('M2ePro/buy/listing/category.phtml');
    }
}