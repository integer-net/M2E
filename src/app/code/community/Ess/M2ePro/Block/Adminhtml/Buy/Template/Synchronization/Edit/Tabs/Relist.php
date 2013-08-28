<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Template_Synchronization_Edit_Tabs_Relist extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateSynchronizationEditTabsRelist');
        //------------------------------

        $this->setTemplate('M2ePro/buy/template/synchronization/relist.phtml');
    }
}