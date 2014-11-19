<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_Product_Category_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingProductCategoryHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/buy/listing/product/category/help.phtml');
    }
}
