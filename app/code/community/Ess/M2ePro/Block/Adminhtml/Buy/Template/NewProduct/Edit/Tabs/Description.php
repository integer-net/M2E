<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Template_NewProduct_Edit_Tabs_Description extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProductEditTabsDescription');
        //------------------------------

        $this->setTemplate('M2ePro/buy/template/newProduct/tabs/description.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label' => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => "AttributeSetHandlerObj.appendToText('select_attributes_for_title', 'title_template');",
            'class' => 'select_attributes_for_title_button'
        ) );
        $this->setChild('select_attributes_for_title_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label' => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => "AttributeSetHandlerObj.appendToText('select_attributes_for_mfg_name',"
                ." 'manufacturer_template');",
            'class' => 'select_attributes_for_mfg_name_template_button'
        ) );
        $this->setChild('select_attributes_for_mfg_name_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label' => Mage::helper('M2ePro')->__('Insert'),
            'onclick' => "AttributeSetHandlerObj.appendToTextarea('#' + $('select_attributes').value + '#');",
            'class' => 'add_product_attribute_button',
        ) );
        $this->setChild('add_product_attribute_button',$buttonBlock);
        //------------------------------

        //------------------------------
        for ($i = 0; $i < Ess_M2ePro_Model_Buy_Template_NewProduct_Core::FEATURES_COUNT; $i++) {
            $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                'label' => Mage::helper('M2ePro')->__('Insert'),
                'onclick' => "AttributeSetHandlerObj.appendToText('select_attributes_for_features_{$i}',"
                    ." 'features_{$i}');BuyTemplateNewProductHandlerObj.allowAddFeature(this);",
                'class' => "select_attributes_for_features_{$i}_button"
            ) );
            $this->setChild("select_attributes_for_features_{$i}_button",$buttonBlock);
        }
        //------------------------------

        return parent::_beforeToHtml();
    }
}