<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_Feedback extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayAccountEditTabsFeedback');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/account/tabs/feedback.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add Template'),
                                'onclick' => 'EbayAccountHandlerObj.feedbacksOpenAddForm();',
                                'class' => 'open_add_form'
                            ) );
        $this->setChild('open_add_form',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'EbayAccountHandlerObj.feedbacksCancelForm();',
                                'class' => 'cancel_form'
                            ) );
        $this->setChild('cancel_form',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Save'),
                                'onclick' => 'EbayAccountHandlerObj.feedbacksAddAction();',
                                'class' => 'add_action'
                            ) );
        $this->setChild('add_action',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Save'),
                                'onclick' => 'EbayAccountHandlerObj.feedbacksEditAction();',
                                'class' => 'edit_action'
                            ) );
        $this->setChild('edit_action',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $this->setChild('feedback_template_grid',
                        $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_feedback_grid'));
        //-------------------------------

        return parent::_beforeToHtml();
    }
}