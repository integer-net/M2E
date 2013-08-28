<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Product_Category_Edit extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingProductCategory');
        //------------------------------

        $this->setTemplate('M2ePro/buy/listing/product/category/edit.phtml');
    }
}
