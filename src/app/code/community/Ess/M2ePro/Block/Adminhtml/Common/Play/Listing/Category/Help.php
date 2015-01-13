<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Category_Help extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playListingProductHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/play/listing/category/help.phtml');
    }
}