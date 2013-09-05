<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_CronPopup extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayCronPopup');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/cron_popup.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'cronPopupConfirm();',
            'style' => 'float:right;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('cron_popup_confirm',$buttonBlock);
        //-------------------------------
    }

    // ########################################
}