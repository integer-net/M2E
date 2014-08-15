<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser_Tabs_Search extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryChooserSearch');
        //------------------------------

        // Set template
        //------------------------------
        $this->setTemplate('M2ePro/ebay/listing/category/chooser/tabs/search.phtml');
        // -----------------------------
    }
}