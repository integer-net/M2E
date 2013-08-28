<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Category_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingProductCategoryHelp');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/listing/product/category/help.phtml');
    }
}