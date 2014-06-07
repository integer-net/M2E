<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Synchronization_Form extends Mage_Adminhtml_Block_Widget_Form
{
    private $component = Ess_M2ePro_Helper_Component_Amazon::NICK;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonSynchronizationForm');
        $this->setContainerId('magento_block_amazon_synchronization');
        $this->setTemplate('M2ePro/common/amazon/synchronization.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templatesMode = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/amazon/templates/', 'mode');
        $this->ordersMode = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/amazon/orders/', 'mode');
        $this->otherListingsMode = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/amazon/other_listings/', 'mode');
        //----------------------------

        //----------------------------
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->reviseAllInProcessingState = !is_null(
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/amazon/templates/revise/total/', 'last_listing_product_id'
            )
        );

        $this->reviseAllStartDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/amazon/templates/revise/total/', 'start_date'
        );
        $this->reviseAllStartDate && $this->reviseAllStartDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllStartDate))
            ->toString($format);

        $this->reviseAllEndDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/amazon/templates/revise/total/', 'end_date'
        );
        $this->reviseAllEndDate && $this->reviseAllEndDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllEndDate))
            ->toString($format);
        //----------------------------

        //----------------------------
        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;
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
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowTemplates\', \'' . $this->component . '\');',
            'class'   => 'templates_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_templates_run_now', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $areYouSure = Mage::helper('M2ePro')->__('Are you sure?');

        $backUrl =  Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_common_synchronization/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
            )
        );
        //-------------------------------

        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_TEMPLATES,
                'component' => $this->component,
                'back'=> $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'templates_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_templates_clear_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_TEMPLATES,
                'component' => $this->component
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_templates_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Run Now'),
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOrders\', \'' . $this->component . '\');',
            'class'   => 'orders_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_orders_run_now', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS,
                'component' => $this->component,
                'back' => $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'orders_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_orders_clear_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS,
                'component'  => $this->component,
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_orders_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Run Now'),
            'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOtherListings\', \''.$this->component.'\');',
            'class'   => 'other_listings_run_now'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_other_listings_run_now', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_synchronization/clearLog',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS,
                'component'  => $this->component,
                'back'       => $backUrl
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Clear Log'),
            'onclick' => 'deleteConfirm(\'' . $areYouSure . '\', \'' . $url . '\')',
            'class'   => 'orders_clear_log'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_other_listings_clear_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_log/synchronization',
            array(
                'task' => Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS,
                'component'  => $this->component,
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('View Log'),
            'onclick' => 'window.open(\'' . $url . '\')',
            'class'   => 'button_link'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('amazon_other_listings_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $this->inspectorMode = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/','mode'
        );
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################

    public function isShowReviseAll()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/synchronization/revise_total/','show'
        );
    }

    // ####################################
}