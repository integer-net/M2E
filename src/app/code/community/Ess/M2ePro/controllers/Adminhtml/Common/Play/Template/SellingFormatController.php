<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Play_Template_SellingFormatController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Policies'))
            ->_title(Mage::helper('M2ePro')->__('Selling Format Policies'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Common/Play/Template/SellingFormatHandler.js');

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
        $model = Mage::helper('M2ePro/Component_Play')->getModel('Template_SellingFormat')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Policy does not exist'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_play_template_sellingFormat_edit'))
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

            'price_gbr_mode',
            'price_gbr_coefficient',
            'price_gbr_custom_attribute',

            'price_euro_mode',
            'price_euro_coefficient',
            'price_euro_custom_attribute',

            'price_variation_mode',
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $tempConstant = Ess_M2ePro_Block_Adminhtml_Common_Play_Template_SellingFormat_Edit_Form
                            ::QTY_MODE_PRODUCT_FIXED_VIRTUAL_ATTRIBUTE_VALUE;

        // virtual attribute for QTY_FIXED replacement
        if ($data['qty_mode'] == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE &&
            $data['qty_custom_attribute'] == $tempConstant) {

            $data['qty_mode'] = Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED;
            $data['qty_custom_attribute'] = '';
        }

        $data['title'] = strip_tags($data['title']);
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Play')->getModel('Template_SellingFormat')->load($id);

        $oldData = $model->getDataSnapshot();
        $model->addData($data)->save();
        $newData = $model->getDataSnapshot();

        $model->getChildObject()->setSynchStatusNeed($newData,$oldData);

        $id = $model->getId();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Policy was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################
}
