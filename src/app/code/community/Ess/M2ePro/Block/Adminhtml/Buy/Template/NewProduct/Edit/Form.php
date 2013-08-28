<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Template_NewProduct_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProductEditForm');
        //------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->nodes = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category'))
            ->where('parent_id = ?', 0)
            ->query()
            ->fetchAll();

        //------------------------------
        $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                'id' => 'category_confirm_button',
                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => 'BuyTemplateNewProductHandlerObj.confirmCategory();',
        ) );
        $this->setChild('category_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                'id' => 'category_change_button',
                'label'   => Mage::helper('M2ePro')->__('Change Category'),
                'onclick' => 'BuyTemplateNewProductHandlerObj.changeCategory();',
        ) );
        $this->setChild('category_change_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'category_cancel_button',
            'label'   => Mage::helper('M2ePro')->__('Cancel'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.cancelCategory();',
        ) );
        $this->setChild('category_cancel_button',$buttonBlock);
        //------------------------------

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        $this->setData('attributes_sets', $attributesSets);

        $temp = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $this->attribute_set_locked = false;

        if (!is_null($temp)) {
            $this->attribute_set_locked = (bool)Mage::getModel('M2ePro/Buy_Listing_Product')->getCollection()
                ->addFieldToFilter('template_new_product_id',$temp['category']['id'])
                ->getSize();
        }

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
            'onclick' => 'BuyTemplateNewProductHandlerObj.confirmAttributeSets();',
            'class'   => 'attribute_sets_confirm_button',
            'style'   => 'display: none'
        ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}