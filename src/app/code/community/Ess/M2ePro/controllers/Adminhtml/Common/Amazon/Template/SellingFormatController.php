<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('Selling Format Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/AttributeSetHandler.js')
             ->addJs('M2ePro/Common/Amazon/Template/SellingFormatHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/templates/selling_format');
    }

    //#############################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_template_sellingFormat/index');
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_SellingFormat')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/*/index');
        }

        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
        $templateAttributeSetsCollection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('object_id', $id)
                                        ->addFieldToFilter('object_type', $temp);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');

        $model->setData('attribute_sets', $templateAttributeSetsCollection->getColumnValues('attribute_set_id'));

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
             ->_addContent(
                 $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_sellingFormat_edit')
             )
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        //--------------------
        $data = array();

        $keys = array(
            'title',

            'qty_mode',
            'qty_custom_value',
            'qty_custom_attribute',
            'qty_percentage',
            'qty_modification_mode',
            'qty_min_posted_value',
            'qty_max_posted_value',

            'price_mode',
            'price_coefficient',
            'price_custom_attribute',

            'sale_price_mode',
            'sale_price_coefficient',
            'sale_price_custom_attribute',

            'price_variation_mode',

            'sale_price_start_date_mode',
            'sale_price_end_date_mode',

            'sale_price_start_date_value',
            'sale_price_end_date_value',

            'sale_price_start_date_custom_attribute',
            'sale_price_end_date_custom_attribute'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $tempConstant = Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_SellingFormat_Edit_Form
                            ::QTY_MODE_PRODUCT_FIXED_VIRTUAL_ATTRIBUTE_VALUE;

        // virtual attribute for QTY_FIXED replacement
        if ($data['qty_mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE &&
            $data['qty_custom_attribute'] == $tempConstant) {

            $data['qty_mode'] = Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED;
            $data['qty_custom_attribute'] = '';
        }

        if ($data['sale_price_start_date_value'] === '') {
            $data['sale_price_start_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false,'Y-m-d 00:00:00'
            );
        } else {
            $data['sale_price_start_date_value'] = Mage::helper('M2ePro')->getDate(
                $data['sale_price_start_date_value'],false,'Y-m-d 00:00:00'
            );
        }
        if ($data['sale_price_end_date_value'] === '') {
            $data['sale_price_end_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate(
                false,'Y-m-d 00:00:00'
            );
        } else {
            $data['sale_price_end_date_value'] = Mage::helper('M2ePro')->getDate(
                $data['sale_price_end_date_value'],false,'Y-m-d 00:00:00'
            );
        }

        $data['title'] = strip_tags($data['title']);
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_SellingFormat')->load($id);

        $oldData = $model->getDataSnapshot();
        $model->addData($data)->save();
        $newData = $model->getDataSnapshot();

        $model->getChildObject()->setSynchStatusNeed($newData,$oldData);

        $id = $model->getId();
        //--------------------

        // Attribute sets
        //--------------------
        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
        $oldAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                                    ->getCollection()
                                    ->addFieldToFilter('object_type',$temp)
                                    ->addFieldToFilter('object_id',(int)$id);
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
            $oldAttributeSet->deleteInstance();
        }

        if (!is_array($post['attribute_sets'])) {
            $post['attribute_sets'] = explode(',', $post['attribute_sets']);
        }
        foreach ($post['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT,
                'object_id' => (int)$id,
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################
}