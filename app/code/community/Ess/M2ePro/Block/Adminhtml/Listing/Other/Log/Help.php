<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_Log_Help extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/listing/other/log/help.phtml');
    }
}