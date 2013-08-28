<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Buy_Template_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('m2epro/templates')
            ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
            ->_title(Mage::helper('M2ePro')->__('Templates'))
            ->_title(Mage::helper('M2ePro')->__('Synchronization Templates'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Buy/Template/SynchronizationHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/templates/synchronization');
    }

    //#############################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_template_synchronization/index');
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
            return $this->_redirect('*/adminhtml_template_synchronization/index');
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_buy_template_synchronization_edit'))
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_buy_template_synchronization_edit_tabs'))
            ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_template_synchronization/index');
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
            'revise_update_qty_mode',
            'revise_update_qty_max_applied_value',
            'revise_update_price',
            'revise_change_selling_format_template',
            'revise_change_description_template',
            'revise_change_general_template'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        ($data['revise_update_qty'] ==
            Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_OFF ||
         $data['revise_update_qty_mode'] ==
            Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_OFF) &&

            $data['revise_update_qty_max_applied_value'] = NULL;
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
            'relist_qty_value_max',
            'relist_schedule_type',
            'relist_schedule_through_value',
            'relist_schedule_through_metric',
            'relist_schedule_week_time',
            'relist_schedule_week_start_time',
            'relist_schedule_week_end_time'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $temp = Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_SCHEDULE_TYPE_WEEK;
        if ($post['relist_schedule_type'] == $temp) {

            $keys = array('mo','tu','we','th','fr','sa','su');

            $data['relist_schedule_week'] = '';
            foreach ($keys as $key=>$value) {
                $data['relist_schedule_week'] .= ($data['relist_schedule_week'] == '' ? '' : '_');
                if (array_search($value,$post['relist_schedule_week']) !== false) {
                    $data['relist_schedule_week'] .= $value.'1';
                } else {
                    $data['relist_schedule_week'] .= $value.'0';
                }
            }

            $timeStampTimezone = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
            $timeStampCurrentDay = mktime(
                0, 0, 0,
                date('m',$timeStampTimezone),
                date('d',$timeStampTimezone),
                date('Y',$timeStampTimezone)
            );

            if ($data['relist_schedule_week_time'] == '1') {

                $temp = explode(':',$data['relist_schedule_week_start_time']);
                $timeStampTemp = $timeStampCurrentDay + (int)$temp[0]*60*60 + (int)$temp[1]*60;
                $data['relist_schedule_week_start_time'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                    $timeStampTemp,false,'H:i'
                );

                $temp = explode(':',$data['relist_schedule_week_end_time']);
                $timeStampTemp = $timeStampCurrentDay + (int)$temp[0]*60*60 + (int)$temp[1]*60;
                $data['relist_schedule_week_end_time'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                    $timeStampTemp,false,'H:i'
                );

            } else {
                $data['relist_schedule_week_start_time'] = NULL;
                $data['relist_schedule_week_end_time'] = NULL;
            }

        } else {
            $data['relist_schedule_week'] = 'mo0_tu0_we0_th0_fr0_sa0_su0';
            $data['relist_schedule_week_start_time'] = NULL;
            $data['relist_schedule_week_end_time'] = NULL;
        }

        unset($data['relist_schedule_week_time']);
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
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################
}
