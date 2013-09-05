<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // #################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayConfigurationGeneralForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/configuration/general/form.phtml');
    }

    // #################################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->view_ebay_mode = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/', 'mode'
        );
        $this->view_ebay_feedbacks_notification_mode =
            (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/view/ebay/feedbacks/notification/','mode'
            );
        $this->cron_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/cron/notification/','mode'
        );

        return parent::_beforeToHtml();
    }

    // #################################################
}