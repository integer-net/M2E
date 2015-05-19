<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_View_ListingSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ListingSwitcher_Abstract
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block

        $this->setAddListingUrl('*/adminhtml_common_play_listing/add/step/1/');
    }
}