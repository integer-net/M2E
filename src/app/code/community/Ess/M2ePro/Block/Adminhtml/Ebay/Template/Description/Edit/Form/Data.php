<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Description_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    private $attributeSets = array();

    public $attributes = array();
    public $attributesConfigurable = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateDescriptionEditFormData');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/description/form/data.phtml');

        $this->attributeSets = Mage::helper('M2ePro/Data_Global')->getValue('ebay_attribute_sets');
//        $this->attributes = Mage::helper('M2ePro/Data_Global')->getValue('ebay_attributes');
        $this->attributes = Mage::helper('M2ePro/Magento_Attribute')->getAll();
//        $this->attributesConfigurable = Mage::helper('M2ePro/Magento_Attribute')
//            ->getAllConfigurableByAttributeSets($this->attributeSets);
        $this->attributesConfigurable = Mage::helper('M2ePro/Magento_Attribute')->getAllConfigurable();
    }

    protected function _beforeToHtml()
    {
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
                'onclick' => "EbayTemplateDescriptionHandlerObj.appendToText"
                ."('select_attributes_for_subtitle', 'subtitle_template');",
                'class' => 'add_subtitle_button'
            ) );
        $this->setChild('add_subtitle_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label' => Mage::helper('M2ePro')->__('Insert'),
                'onclick' => "EbayTemplateDescriptionHandlerObj.appendToText"
                ."('select_attributes_for_title', 'title_template');",
                'class' => 'select_attributes_for_title_button'
            ) );
        $this->setChild('select_attributes_for_title_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Insert'),
                'onclick' => "EbayTemplateDescriptionHandlerObj.appendToText"
                ."('select_attributes_for_condition_note', 'condition_note_template');",
                'class' => 'add_condition_note_button'
            ) );
        $this->setChild('add_condition_note_button',$buttonBlock);
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
                'onclick' => "EbayTemplateDescriptionHandlerObj.appendToTextarea"
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

        //------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Preview'),
                'onclick' => 'EbayTemplateDescriptionHandlerObj.preview_click(\''.implode(',', $this->attributeSets).'\')',
                'class' => 'bt_preview',
            ) );
        $this->setChild('preview_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################

    public function isCustom()
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    public function getTitle()
    {
        if ($this->isCustom()) {
            return isset($this->_data['custom_title']) ? $this->_data['custom_title'] : '';
        }

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_description');

        if (is_null($template)) {
            return '';
        }

        return $template->getTitle();
    }

    // ####################################

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_description');

        if (is_null($template) || is_null($template->getId())) {
            return array();
        }

        $data = $template->getData();

        if (!empty($data['enhancement']) && is_string($data['enhancement'])) {
            $data['enhancement'] = explode(',', $data['enhancement']);
        } else {
            unset($data['enhancement']);
        }

        if (!empty($data['product_details']) && is_string($data['product_details'])) {
            $data['product_details'] = json_decode($data['product_details'], true);
        } else {
            unset($data['product_details']);
        }

        if (!empty($data['watermark_settings']) && is_string($data['watermark_settings'])) {

            $watermarkSettings = json_decode($data['watermark_settings'], true);
            unset($data['watermark_settings']);

            if (isset($watermarkSettings['position'])) {
                $data['watermark_settings']['position'] = $watermarkSettings['position'];
            }
            if (isset($watermarkSettings['scale'])) {
                $data['watermark_settings']['scale'] = $watermarkSettings['scale'];
            }
            if (isset($watermarkSettings['transparent'])) {
                $data['watermark_settings']['transparent'] = $watermarkSettings['transparent'];
            }

            if (isset($watermarkSettings['hashes']['current'])) {
                $data['watermark_settings']['hashes']['current'] = $watermarkSettings['hashes']['current'];
            }
            if (isset($watermarkSettings['hashes']['previous'])) {
                $data['watermark_settings']['hashes']['previous'] = $watermarkSettings['hashes']['previous'];
            }
        } else {
            unset($data['watermark_settings']);
        }

        return $data;
    }

    // ####################################

    public function getDefault()
    {
        $default = Mage::helper('M2ePro/View_Ebay')->isSimpleMode()
            ? Mage::getSingleton('M2ePro/Ebay_Template_Description')->getDefaultSettingsSimpleMode()
            : Mage::getSingleton('M2ePro/Ebay_Template_Description')->getDefaultSettingsAdvancedMode();

        $default['enhancement'] = explode(',', $default['enhancement']);
        $default['product_details'] = json_decode($default['product_details'], true);
        $default['watermark_settings'] = json_decode($default['watermark_settings'], true);

        return $default;
    }

    // ####################################
}