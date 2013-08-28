<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_General_Edit_Tabs_Payment extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateGeneralEditTabsPayment');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/general/payment.phtml');
    }

    // ####################################
}