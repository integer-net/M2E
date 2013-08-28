<?php

/*
* @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Ebay_ListingOtherSynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('m2epro/configuration')
            ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'))
            ->_title(Mage::helper('M2ePro')->__('Synchronization'));

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Ebay/Listing/Other/SynchronizationHandler.js');

        return $this;
    }

    //#############################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listingOtherSynchronization/edit');
    }

    public function editAction()
    {
        $temp = array();
        $configModel = Mage::helper('M2ePro/Module')->getConfig();
        $temp['source'] = $configModel->getAllGroupValues('/ebay/synchronization/settings/other_listing/source/');
        $temp['revise'] = $configModel->getAllGroupValues('/ebay/synchronization/settings/other_listing/revise/');
        $temp['relist'] = $configModel->getAllGroupValues('/ebay/synchronization/settings/other_listing/relist/');
        $temp['stop'] = $configModel->getAllGroupValues('/ebay/synchronization/settings/other_listing/stop/');

        $temp['attributes'] = $configModel->getAllGroupValues(
            '/ebay/synchronization/settings/other_listing/source/attribute/'
        );

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $temp);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_synchronization_edit'))
            ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_synchronization_edit_tabs'))
            ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->indexAction();
        }

        // Base prepare
        //--------------------
        $data = array();
        $configModel = Mage::helper('M2ePro/Module')->getConfig();
        //--------------------

        // tab: Source
        //--------------------
        $keys = array(
            'qty',
            'price',
            'customer_group_id',
            'title',
            'sub_title',
            'description'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $attributes = array();

        if ($data['qty'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::QTY_SOURCE_ATTRIBUTE) {
            $attributes['qty'] = $post['qty_attribute'];
        }

        if ($data['price'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::PRICE_SOURCE_ATTRIBUTE) {
            $attributes['price'] = $post['price_attribute'];
        }

        if ($data['title'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::TITLE_SOURCE_ATTRIBUTE) {
            $attributes['title'] = $post['title_attribute'];
        }

        if ($data['sub_title'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::SUB_TITLE_SOURCE_ATTRIBUTE) {
            $attributes['sub_title'] = $post['sub_title_attribute'];
        }

        if ($data['description'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::DESCRIPTION_SOURCE_ATTRIBUTE) {
            $attributes['description'] = $post['description_attribute'];
        }

        foreach ($data as $key => $value) {
            $configModel->setGroupValue('/ebay/synchronization/settings/other_listing/source/', $key, $value);
        }

        foreach ($attributes as $key => $value) {
            $configModel->setGroupValue('/ebay/synchronization/settings/other_listing/source/attribute/', $key, $value);
        }

        $tempSourceData = $data;
        $data = array();
        //--------------------

        // tab: Revise
        //--------------------
        $keys = array(
            'revise_update_qty',
            'revise_update_price',
            'revise_update_title',
            'revise_update_sub_title',
            'revise_update_description'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if ($tempSourceData['qty'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::QTY_SOURCE_NONE) {
            $data['revise_update_qty'] =
                          Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_QTY_NONE;
        }

        if ($tempSourceData['price'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::PRICE_SOURCE_NONE) {
            $data['revise_update_price'] =
                          Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_PRICE_NONE;
        }

        if ($tempSourceData['title'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::TITLE_SOURCE_NONE) {
            $data['revise_update_title'] =
                          Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_TITLE_NONE;
        }

        if ($tempSourceData['sub_title'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::SUB_TITLE_SOURCE_NONE) {
            $data['revise_update_sub_title'] =
                          Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_SUB_TITLE_NONE;
        }

        if ($tempSourceData['description'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::DESCRIPTION_SOURCE_NONE) {
            $data['revise_update_description'] =
                          Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::REVISE_UPDATE_DESCRIPTION_NONE;
        }

        foreach ($data as $key => $value) {
            $configModel->setGroupValue('/ebay/synchronization/settings/other_listing/revise/', $key, $value);
        }

        $data = array();
        //--------------------

        // tab: Relist
        //--------------------
        $keys = array(
            'relist_mode',
            'relist_filter_user_lock',
            'relist_send_data',
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

        if ($tempSourceData['qty'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::QTY_SOURCE_NONE) {
            $data['relist_qty'] = Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::RELIST_QTY_NONE;
        }

        foreach ($data as $key => $value) {
            $configModel->setGroupValue('/ebay/synchronization/settings/other_listing/relist/', $key, $value);
        }

        $data = array();
        //--------------------

        // tab: Stop
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

        if ($tempSourceData['qty'] == Ess_M2ePro_Model_Ebay_Listing_Other_Source::QTY_SOURCE_NONE) {
            $data['stop_qty'] = Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization::STOP_QTY_NONE;
        }

        foreach ($data as $key => $value) {
            $configModel->setGroupValue('/ebay/synchronization/settings/other_listing/stop/', $key, $value);
        }
        //--------------------

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('Synchronization settings was successfully saved.')
        );

       return $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('edit',array(),array()));
    }
}