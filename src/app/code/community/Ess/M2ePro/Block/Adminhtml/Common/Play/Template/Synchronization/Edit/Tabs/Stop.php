<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Template_Synchronization_Edit_Tabs_Stop extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playTemplateSynchronizationEditTabsStop');
        //------------------------------

        $this->setTemplate('M2ePro/common/play/template/synchronization/stop.phtml');
    }
}