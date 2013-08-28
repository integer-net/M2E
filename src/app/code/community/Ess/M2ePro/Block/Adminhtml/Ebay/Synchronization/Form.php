<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebaySynchronizationForm');
        $this->setContainerId('magento_block_ebay_synchronization');
        $this->setTemplate('M2ePro/ebay/synchronization.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templates = Mage::helper('M2ePro/Module')->getConfig()
                                                       ->getAllGroupValues('/ebay/synchronization/settings/templates/');

        $this->orders = Mage::helper('M2ePro/Module')->getConfig()
                                                     ->getGroupValue('/ebay/synchronization/settings/orders/', 'mode');
        $this->feedbacks = Mage::helper('M2ePro/Module')->getConfig()
                                                        ->getGroupValue('/ebay/synchronization/settings/feedbacks/',
                                                                        'mode');
        $this->otherListings = Mage::helper('M2ePro/Module')
                                            ->getConfig()
                                            ->getGroupValue('/ebay/synchronization/settings/other_listings/',
                                                            'mode');
        $this->messages = Mage::helper('M2ePro/Module')->getConfig()
                                                       ->getGroupValue('/ebay/synchronization/settings/messages/',
                                                                       'mode');
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowTemplates\', \''
                                        .Ess_M2ePro_Helper_Component_Ebay::NICK
                                        .'\');',
                           'class'   => 'templates_run_now'
                       ) );
        $this->setChild('ebay_templates_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'deleteConfirm(\''.$tempStr.'\', \''
                         .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                        array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_TEMPLATES,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK,
                                              'back'=>$back)) . '\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' => $onclickAction,
                           'class' => 'templates_clear_log'
                       ) );
        $this->setChild('ebay_templates_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('back'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'setLocation(\''
                         .$this->getUrl('*/adminhtml_log/synchronization',
                                        array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_TEMPLATES,
                                              'back'=>$back,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK))
                         .'\')';

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' => $onclickAction,
                           'class' => 'button_link'
                       ) );
        $this->setChild('ebay_templates_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOrders\', \''
                                        .Ess_M2ePro_Helper_Component_Ebay::NICK.'\');',
                           'class' => 'orders_run_now'
                       ) );
        $this->setChild('ebay_orders_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'deleteConfirm(\''. $tempStr.'\', \''
                         .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                        array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK,
                                              'back'=>$back))
                         .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' => $onclickAction,
                           'class' => 'orders_clear_log'
                       ) );
        $this->setChild('ebay_orders_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'setLocation(\''
                         .$this->getUrl('*/adminhtml_log/synchronization',
                                        array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS,
                                              'back'=>$back,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK))
                         .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' => $onclickAction,
                           'class' => 'button_link'
                       ) );
        $this->setChild('ebay_orders_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowFeedbacks\', \''
                                        .Ess_M2ePro_Helper_Component_Ebay::NICK.'\');',
                           'class' => 'feedbacks_run_now'
                       ) );
        $this->setChild('ebay_feedbacks_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'deleteConfirm(\''. $tempStr.'\', \''
                         .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                        array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_FEEDBACKS,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK,
                                              'back'=>$back))
                         .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' => $onclickAction,
                           'class' => 'feedbacks_clear_log'
                       ) );
        $this->setChild('ebay_feedbacks_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'setLocation(\''
                         .$this->getUrl('*/adminhtml_log/synchronization',
                                        array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_FEEDBACKS,
                                              'back'=>$back,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK))
                         .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' => $onclickAction,
                           'class' => 'button_link'
                       ) );
        $this->setChild('ebay_feedbacks_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOtherListings\', \''
                                        .Ess_M2ePro_Helper_Component_Ebay::NICK.'\');',
                           'class' => 'other_listings_run_now'
                       ) );
        $this->setChild('ebay_other_listings_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'deleteConfirm(\''. $tempStr.'\', \''
                         .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                        array('synch_task'=>
                                                        Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK,
                                              'back'=>$back))
                         .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' => $onclickAction,
                           'class' => 'orders_clear_log'
                       ) );
        $this->setChild('ebay_other_listings_clear_log', $buttonBlock);

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'setLocation(\''
                         .$this->getUrl('*/adminhtml_log/synchronization',
                                        array('synch_task'=>
                                                        Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS,
                                              'back'=>$back,
                                              'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK))
                         .'\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' => $onclickAction,
                           'class' => 'button_link'
                       ) );
        $this->setChild('ebay_other_listings_view_log', $buttonBlock);

        $back = Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                           array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
        $onclickAction = 'window.open(\''
            .$this->getUrl('*/adminhtml_ebay_listingOtherSynchronization/edit', array('back' => $back))
            .'\', \'_blank\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
            'label' => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick' => $onclickAction,
            'class' => 'button_link'
        ));
        $this->setChild('ebay_other_listings_synchronization_settings', $buttonBlock);
        //-------------------------------

        //-------------------------------
        // TODO uncomment code when messages was completed
        //        $buttonBlock = $this->getLayout()
        //                            ->createBlock('adminhtml/widget_button')
        //                            ->setData( array(
        //                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
        //                                'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowMessages\');',
        //                                'class' => 'messages_run_now'
        //                            ) );
        //        $this->setChild('messages_run_now',$buttonBlock);
        //
        //        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');
        //
        //        $buttonBlock = $this->getLayout()
        //                            ->createBlock('adminhtml/widget_button')
        //                            ->setData( array(
        //                                'label'   => Mage::helper('M2ePro')->__('Clear Log'),
        //                                'onclick' => 'deleteConfirm(\''. $tempStr.'\', \''
//.$this->getUrl('*/adminhtml_synchronization/clearLog',array('synch_task'=>
//        Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_MESSAGES)) . '\')',
        //                                'class' => 'messages_clear_log'
        //                            ) );
        //        $this->setChild('messages_clear_log',$buttonBlock);
        //
        //        $buttonBlock = $this->getLayout()
        //                            ->createBlock('adminhtml/widget_button')
        //                            ->setData( array(
        //                                'label'   => Mage::helper('M2ePro')->__('View Log'),
        //                                'onclick' => 'setLocation(\''
//                                                     .$this->getUrl('*/adminhtml_logs/synchronizations',
// array('synch_task'=>Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_MESSAGES,
// 'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))).'\')',
        //                                'class' => 'button_link'
        //                            ) );
        //        $this->setChild('messages_view_log',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}