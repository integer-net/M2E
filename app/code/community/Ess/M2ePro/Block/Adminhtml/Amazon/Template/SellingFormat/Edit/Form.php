<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_SellingFormat_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateSellingFormatEditForm');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/template/selling_format/form.phtml');
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
        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        $this->setData('attributes_sets', $attributesSets);
        //------------------------------

        //------------------------------
        $this->attribute_set_locked = false;
        if (Mage::helper('M2ePro')->getGlobalValue('temp_data')
            && Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
        ) {
            $this->attribute_set_locked = Mage::helper('M2ePro')->getGlobalValue('temp_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $this->setData('currencies',
                       Mage::helper('M2ePro/Module')->getConfig()
                                                    ->getAllGroupValues('/amazon/currency/',
                                                                    Ess_M2ePro_Model_Config_Abstract::SORT_VALUE_ASC));
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'attribute_sets_select_all_button',
                                'label'   => Mage::helper('M2ePro')->__('Select All'),
                                'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
                                'class'   => 'attribute_sets_select_all_button'
                            ) );
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'      => 'attribute_sets_confirm_button',
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'AmazonTemplateSellingFormatHandlerObj.attribute_sets_confirm();',
                                'class'   => 'attribute_sets_confirm_button',
                                'style'   => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->customerGroups = Mage::getModel('customer/group')->getCollection()->toOptionArray();
        //------------------------------

        return parent::_beforeToHtml();
    }
}