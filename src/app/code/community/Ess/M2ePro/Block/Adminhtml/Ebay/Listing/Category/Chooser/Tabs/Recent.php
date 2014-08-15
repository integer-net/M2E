<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser_Tabs_Recent extends Mage_Adminhtml_Block_Widget
{

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategoryChooserRecent');
        //------------------------------

        // Set template
        //------------------------------
        $this->setTemplate('M2ePro/ebay/listing/category/chooser/tabs/recent.phtml');
        // -----------------------------
    }

}