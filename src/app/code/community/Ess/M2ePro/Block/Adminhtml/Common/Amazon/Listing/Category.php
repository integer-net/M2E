<?php

    /*
    * @copyright  Copyright (c) 2013 by  ESS-UA.
    */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Category extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingCategory');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/category.phtml');
    }
}