<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Buy_Template_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Templates'))
            ->_title(Mage::helper('M2ePro')->__('Synchronization Templates'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Common/Buy/Template/SynchronizationHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/templates/synchronization');
    }

    //#############################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_common_template_synchronization/index');
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Template_Synchronization')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/adminhtml_common_template_synchronization/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        $this->_initAction()
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_synchronization_edit_tabs'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_synchronization_edit'))
            ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_common_template_synchronization/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        // tab: list
        //--------------------
        $keys = array(
            'title',
            'list_mode',
            'list_status_enabled',
            'list_is_in_stock',
            'list_qty',
            'list_qty_value',
            'list_qty_value_max',
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        //--------------------

        // tab: revise
        //--------------------
        $keys = array(
            'revise_update_qty',
            'revise_update_qty_max_applied_value_mode',
            'revise_update_qty_max_applied_value',
            'revise_update_price',
            'revise_change_selling_format_template',
            'revise_change_listing'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // tab: relist
        //--------------------
        $keys = array(
            'relist_mode',
            'relist_filter_user_lock',
            'relist_status_enabled',
            'relist_is_in_stock',
            'relist_qty',
            'relist_qty_value',
            'relist_qty_value_max'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // tab: stop
        //--------------------
        $keys = array(
            'stop_status_disabled',
            'stop_out_off_stock',
            'stop_qty',
            'stop_qty_value',
            'stop_qty_value_max'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Buy')->getModel('Template_Synchronization');
        $model->load($id);

        $oldData = $model->getDataSnapshot();
        $model->addData($data)->save();
        $newData = $model->getDataSnapshot();

        $model->getChildObject()->setSynchStatusNeed($newData,$oldData);

        $id = $model->getId();
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################
}
