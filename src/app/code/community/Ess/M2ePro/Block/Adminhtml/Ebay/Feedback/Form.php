<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Feedback_Form extends Mage_Adminhtml_Block_Widget_Form
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayFeedbackForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/feedback/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Send'),
                                'onclick' => 'EbayFeedbackHandlerObj.sendFeedback();',
                                'class'   => 'send_feedback'
                            ) );
        $this->setChild('send_feedback',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'EbayFeedbackHandlerObj.cancelFeedback();',
                                'class'   => 'cancel_feedback'
                            ) );
        $this->setChild('cancel_feedback',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}