<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_NewProduct_Edit_Tabs_Description extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateNewProductEditTabsDescription');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/template/newProduct/tabs/description.phtml');
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
                'onclick' => "AttributeSetHandlerObj.appendToText('select_attributes_for_brand', 'brand_template');",
                'class' => 'select_attributes_for_brand_button'
            ) );
        $this->setChild('select_attributes_for_brand_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label' => Mage::helper('M2ePro')->__('Insert'),
                'onclick' => "AttributeSetHandlerObj.appendToText('select_attributes_for_manufacturer',"
                             ." 'manufacturer_template');",
                'class' => 'select_attributes_for_manufacturer_button'
            ) );
        $this->setChild('select_attributes_for_manufacturer_button',$buttonBlock);
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
        for ($i = 0; $i < 5; $i++) {

            $onClick = <<<JS
AttributeSetHandlerObj.appendToText('select_attributes_for_bullet_points_{$i}', 'bullet_points_{$i}');
AmazonTemplateNewProductDescriptionHandlerObj.multi_element_keyup('bullet_points',{value:' '});
JS;

            $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                    'label' => Mage::helper('M2ePro')->__('Insert'),
                    'onclick' => $onClick,
                    'class' => "select_attributes_for_bullet_points_{$i}_button"
                ) );
            $this->setChild("select_attributes_for_bullet_points_{$i}_button",$buttonBlock);
        }
        //------------------------------

        //------------------------------
        for ($i = 0; $i < 5; $i++) {

            $onClick = <<<JS
AttributeSetHandlerObj.appendToText('select_attributes_for_search_terms_{$i}', 'search_terms_{$i}');
AmazonTemplateNewProductDescriptionHandlerObj.multi_element_keyup('search_terms',{value:' '});
JS;

            $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                    'label' => Mage::helper('M2ePro')->__('Insert'),
                    'onclick' => $onClick,
                    'class' => "select_attributes_for_search_terms_{$i}_button"
                ) );
            $this->setChild("select_attributes_for_search_terms_{$i}_button",$buttonBlock);
        }
        //------------------------------

        return parent::_beforeToHtml();
    }
}