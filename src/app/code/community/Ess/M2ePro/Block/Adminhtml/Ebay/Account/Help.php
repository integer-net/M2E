<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Help extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/account/help.phtml');
    }
}