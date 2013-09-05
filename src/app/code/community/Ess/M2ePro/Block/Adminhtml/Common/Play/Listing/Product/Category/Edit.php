<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Product_Category_Edit extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingProductCategory');
        //------------------------------

        $this->setTemplate('M2ePro/common/play/listing/product/category/edit.phtml');
    }
}
