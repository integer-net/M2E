<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_AutoAction_Mode_Global
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Global
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/auto_action/mode/global.phtml');
    }

    // ####################################

}
