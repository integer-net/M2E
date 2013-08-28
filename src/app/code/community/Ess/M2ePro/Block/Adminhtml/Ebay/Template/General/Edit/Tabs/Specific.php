<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_General_Edit_Tabs_Specific extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateGeneralEditTabsSpecific');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/general/specific.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => '',
            'onclick' => 'EbayTemplateGeneralSpecificHandlerObj.removeSpecific(this);',
            'class' => 'scalable delete remove_custom_specific_button'
        ) );
        $this->setChild('remove_custom_specific_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => Mage::helper('M2ePro')->__('Add Custom Specific'),
            'onclick' => 'EbayTemplateGeneralSpecificHandlerObj.addRow();',
            'class' => 'add add_custom_specific_button'
        ) );
        $this->setChild('add_custom_specific_button',$buttonBlock);
        //------------------------------
    }

    // ####################################
}