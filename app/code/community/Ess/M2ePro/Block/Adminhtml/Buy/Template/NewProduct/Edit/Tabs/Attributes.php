<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Template_NewProduct_Edit_Tabs_Attributes extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProductEditTabsAttributes');
        //------------------------------

        $this->setTemplate('M2ePro/buy/template/newProduct/tabs/attributes.phtml');
    }
}