<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Buy_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('m2epro/templates')
            ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
            ->_title(Mage::helper('M2ePro')->__('Templates'))
            ->_title(Mage::helper('M2ePro')->__('Selling Format Templates'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Template/AttributeSetHandler.js')
            ->addJs('M2ePro/Buy/Template/SellingFormatHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/templates/selling_format');
    }

    //#############################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_template_sellingFormat/index');
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Template_SellingFormat')->load($id);

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

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_buy_template_sellingFormat_edit'))
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
            'qty_max_posted_value',
            'qty_max_posted_value_mode',

            'price_mode',
            'price_coefficient',
            'price_custom_attribute',

            'price_variation_mode',

            'customer_group_id'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);

        $data['price_coefficient'] = str_replace(',', '.', $data['price_coefficient']);

        ($data['qty_mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_SINGLE ||
         $data['qty_mode'] == Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_NUMBER ||
         $data['qty_max_posted_value_mode']== Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MAX_POSTED_MODE_OFF) &&

            $data['qty_max_posted_value'] = NULL;

        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Template_SellingFormat');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
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
