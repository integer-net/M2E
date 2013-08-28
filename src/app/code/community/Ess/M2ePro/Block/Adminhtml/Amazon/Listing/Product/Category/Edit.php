<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Category_Edit extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingProductCategory');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/listing/product/category/edit.phtml');
    }
}