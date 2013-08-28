<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Description_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //#############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateDescriptionEditForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/description/form.phtml');
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

    //#############################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        $this->setData('attributes_sets', $attributesSets);
        //------------------------------

        //------------------------------
        $this->attribute_set_locked = false;
        if (Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()) {
            $this->attribute_set_locked = Mage::helper('M2ePro')->getGlobalValue('temp_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => 'EbayTemplateDescriptionHandlerObj.openInsertImageWindow();',
                                'class' => 'insert_image_window_button'
                            ) );
        $this->setChild('insert_image_window_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => "AttributeSetHandlerObj.appendToText"
                                             ."('select_attributes_for_subtitle', 'subtitle_template');",
                                'class' => 'add_subtitle_button'
                            ) );
        $this->setChild('add_subtitle_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_select_all_button',
                                'label'   => Mage::helper('M2ePro')->__('Select All'),
                                'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
                                'class' => 'attribute_sets_select_all_button'
                            ) );
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_confirm_button',
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'EbayTemplateDescriptionHandlerObj.attribute_sets_confirm();',
                                'class' => 'attribute_sets_confirm_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label' => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => "AttributeSetHandlerObj.appendToText"
                                             ."('select_attributes_for_title', 'title_template');",
                                'class' => 'select_attributes_for_title_button'
                            ) );
        $this->setChild('select_attributes_for_title_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'toggletext',
                                'label' => Mage::helper('M2ePro')->__('Show / Hide Editor'),
                                'class' => 'show_hide_mce_button',
                            ) );
        $this->setChild('show_hide_mce_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => "AttributeSetHandlerObj.appendToTextarea"
                                             ."('#' + $('select_attributes').value + '#');",
                                'class' => 'add_product_attribute_button',
                            ) );
        $this->setChild('add_product_attribute_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Insert'),
                                'onclick' => 'EbayTemplateDescriptionHandlerObj.insertGallery();',
                                'class' => 'insert_gallery_button',
                            ) );
        $this->setChild('insert_gallery_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    //#############################################
}