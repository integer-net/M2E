<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('edit_form');
        $this->setContainerId('magento_block_ebay_synchronization');
        $this->setTemplate('M2ePro/ebay/synchronization/form.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templates = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getAllGroupValues('/ebay/templates/');
        $this->orders = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/ebay/orders/', 'mode');
        $this->feedbacks = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/ebay/feedbacks/', 'mode');
        $this->otherListings = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/ebay/other_listings/', 'mode');
        //----------------------------

        //----------------------------
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->reviseAllInProcessingState = !is_null(
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/ebay/templates/revise/total/', 'last_listing_product_id'
            )
        );

        $this->reviseAllStartDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/ebay/templates/revise/total/', 'start_date'
        );
        $this->reviseAllStartDate && $this->reviseAllStartDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllStartDate))
            ->toString($format);

        $this->reviseAllEndDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/ebay/templates/revise/total/', 'end_date'
        );
        $this->reviseAllEndDate && $this->reviseAllEndDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllEndDate))
            ->toString($format);
        //----------------------------

        //----------------------------
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;
        $data = array(
            'class'   => 'ok_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => "Windows.getFocusedWindow().close(); SynchronizationHandlerObj.runReviseAll('{$component}');",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('revise_all_confirm_popup_ok_button', $buttonBlock);
        //------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Run Now'),
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowTemplates\');',
            'class'   => 'templates_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_templates_run_now', $buttonBlock);
        //------------------------------

        $areYouSure = Mage::helper('M2ePro')->__('Are you sure?');
        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_synchronization/index');

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_TEMPLATES,
                'back'       => $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'templates_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_templates_clear_log', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_TEMPLATES,
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_templates_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Run Now'),
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOrders\');',
            'class'   => 'orders_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_orders_run_now', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS,
                'back'       => $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'orders_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_orders_clear_log', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS,
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_orders_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Run Now'),
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowFeedbacks\');',
            'class'   => 'feedbacks_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_feedbacks_run_now', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_FEEDBACKS,
                'back'       => $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'feedbacks_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_feedbacks_clear_log', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_FEEDBACKS,
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_feedbacks_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Run Now'),
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOtherListings\');',
            'class'   => 'other_listings_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_other_listings_run_now', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS,
                'back'       => $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'orders_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_other_listings_clear_log', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS,
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_other_listings_view_log', $buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_ebay_listing_other_synchronization/edit', array('back' => $backUrl));
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick' => 'window.open(\'' . $url . '\', \'_blank\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ebay_other_listings_synchronization_settings', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $this->inspectorMode = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/','mode'
        );
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    public function isShowReviseAll()
    {
        $showSetting = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/synchronization/revise_total/','show'
        );

        return $showSetting && Mage::helper('M2ePro/View_Ebay')->isAdvancedMode();
    }

    // ########################################
}