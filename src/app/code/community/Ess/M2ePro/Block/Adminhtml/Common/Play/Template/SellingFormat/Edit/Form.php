<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Template_SellingFormat_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    const QTY_MODE_PRODUCT_FIXED_VIRTUAL_ATTRIBUTE_VALUE = 'qty_mode_product_fixed';

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playTemplateSellingFormatEditForm');
        //------------------------------

        $this->setTemplate('M2ePro/common/play/template/selling_format/form.phtml');
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
        //------------------------------
        $attributesSets = Mage::helper('M2ePro/Magento_AttributeSet')->getAll();
        $this->setData('attributes_sets', $attributesSets);
        //------------------------------

        //------------------------------
        $this->attribute_set_locked = false;
        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            $this->attribute_set_locked = Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'attribute_sets_select_all_button',
            'label'   => Mage::helper('M2ePro')->__('Select All'),
            'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
            'class'   => 'attribute_sets_select_all_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'attribute_sets_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'PlayTemplateSellingFormatHandlerObj.attribute_sets_confirm();',
            'class'   => 'attribute_sets_confirm_button',
            'style'   => 'display: none'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->customerGroups = Mage::getModel('customer/group')->getCollection()->toOptionArray();
        //------------------------------

        return parent::_beforeToHtml();
    }
}
