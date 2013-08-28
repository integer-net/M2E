<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Play_Synchronization_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playSynchronizationForm');
        $this->setContainerId('magento_block_play_synchronization');
        $this->setTemplate('M2ePro/play/synchronization.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templatesMode      = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue(
                '/play/synchronization/settings/templates/',
                'mode'
            );
        $this->ordersMode         = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue(
                '/play/synchronization/settings/orders/',
                'mode'
            );
        $this->otherListingsMode  = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue(
                '/play/synchronization/settings/other_listings/',
                'mode'
            );
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowTemplates\', \''
                    .Ess_M2ePro_Helper_Component_Play::NICK.'\');',
                'class' => 'templates_run_now'
            ) );
        $this->setChild('play_templates_run_now', $buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_synchronization/index',
            array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
        );
        $onclickAction = 'deleteConfirm(\''.$tempStr.'\', \''
            .$this->getUrl('*/adminhtml_synchronization/clearLog',
                array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_TEMPLATES,
                    'component'=>Ess_M2ePro_Helper_Component_Play::NICK,
                    'back'=>$back)) . '\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                'onclick' => $onclickAction,
                'class' => 'templates_clear_log'
            ) );
        $this->setChild('play_templates_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_synchronization/index',
            array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
        );
        $onclickAction = 'setLocation(\''
            .$this->getUrl(
                '*/adminhtml_log/synchronization',
                array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_TEMPLATES,
                    'back'=>$back,
                    'component'=>Ess_M2ePro_Helper_Component_Play::NICK))
            .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('View Log'),
                'onclick' => $onclickAction,
                'class' => 'button_link'
            ) );
        $this->setChild('play_templates_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOrders\', \''
                    .Ess_M2ePro_Helper_Component_Play::NICK.'\');',
                'class' => 'orders_run_now'
            ) );
        $this->setChild('play_orders_run_now', $buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_synchronization/index',
            array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
        );
        $onclickAction = 'deleteConfirm(\''. $tempStr.'\', \''
            .$this->getUrl('*/adminhtml_synchronization/clearLog',
                array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS,
                    'component'=>Ess_M2ePro_Helper_Component_Play::NICK,
                    'back'=>$back))
            .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                'onclick' => $onclickAction,
                'class' => 'orders_clear_log'
            ) );
        $this->setChild('play_orders_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_synchronization/index',
            array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
        );
        $onclickAction = 'setLocation(\''
            .$this->getUrl('*/adminhtml_log/synchronization',
                array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS,
                    'back'=>$back,
                    'component'=>Ess_M2ePro_Helper_Component_Play::NICK)).'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('View Log'),
                'onclick' => $onclickAction,
                'class' => 'button_link'
            ) );
        $this->setChild('play_orders_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOtherListings\', \''
                    .Ess_M2ePro_Helper_Component_Play::NICK
                    .'\');',
                'class' => 'other_listings_run_now'
            ) );
        $this->setChild('play_other_listings_run_now', $buttonBlock);

        $tempStr = Mage::helper('adminhtml')->__('Are you sure?');

        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_synchronization/index',
            array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
        );
        $onclickAction = 'deleteConfirm(\''. $tempStr.'\', \''
            .$this->getUrl(
                '*/adminhtml_synchronization/clearLog',
                array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS,
                    'component'=>Ess_M2ePro_Helper_Component_Play::NICK,
                    'back'=>$back))
            .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                'onclick' => $onclickAction,
                'class' => 'orders_clear_log'
            ) );
        $this->setChild('play_other_listings_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_synchronization/index',
            array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_PLAY)
        );
        $onclickAction = 'setLocation(\''
            .$this->getUrl(
                '*/adminhtml_log/synchronization',
                array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS,
                    'back'=>$back,
                    'component'=>Ess_M2ePro_Helper_Component_Play::NICK))
            .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('View Log'),
                'onclick' => $onclickAction,
                'class' => 'button_link'
            ) );
        $this->setChild('play_other_listings_view_log', $buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}