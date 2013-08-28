<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Synchronization_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonSynchronizationForm');
        $this->setContainerId('magento_block_amazon_synchronization');
        $this->setTemplate('M2ePro/amazon/synchronization.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templatesMode      = Mage::helper('M2ePro/Module')
                                                    ->getConfig()
                                                    ->getGroupValue('/amazon/synchronization/settings/templates/',
                                                                    'mode');
        $this->ordersMode         = Mage::helper('M2ePro/Module')
                                                    ->getConfig()
                                                    ->getGroupValue('/amazon/synchronization/settings/orders/',
                                                                    'mode');
        $this->otherListingsMode  = Mage::helper('M2ePro/Module')
                                                    ->getConfig()
                                                    ->getGroupValue('/amazon/synchronization/settings/other_listings/',
                                                                    'mode');
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowTemplates\', \''
                                        .Ess_M2ePro_Helper_Component_Amazon::NICK.'\');',
                           'class' => 'templates_run_now'
                       ) );
        $this->setChild('amazon_templates_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $synchTaskTemplates = Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_TEMPLATES;
        $tabIdAmazon = Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_AMAZON;

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' =>
                               'deleteConfirm(\''.$tempStr.'\', \''
                                   .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                                  array('synch_task'=>$synchTaskTemplates,
                                                        'component'=>Ess_M2ePro_Helper_Component_Amazon::NICK,
                                                        'back'=>
                                                              Mage::helper('M2ePro')
                                                                 ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                                                     array('tab'=>$tabIdAmazon)))) . '\')',
                           'class' => 'templates_clear_log'
                       ) );
        $this->setChild('amazon_templates_clear_log', $buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' =>
                               'setLocation(\''
                                   .$this->getUrl('*/adminhtml_log/synchronization',
                                                  array('synch_task'=>$synchTaskTemplates,
                                                        'back'=>
                                                            Mage::helper('M2ePro')
                                                                ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                                                    array('tab'=>$tabIdAmazon)),
                                                        'component'=>Ess_M2ePro_Helper_Component_Amazon::NICK)).'\')',
                           'class' => 'button_link'
                       ) );
        $this->setChild('amazon_templates_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOrders\', \''
                                        .Ess_M2ePro_Helper_Component_Amazon::NICK.'\');',
                           'class' => 'orders_run_now'
                       ) );
        $this->setChild('amazon_orders_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' =>
                               'deleteConfirm(\''
                                   .$tempStr.'\', \''
                                   .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                                  array('synch_task'=>
                                                            Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS,
                                                  'component'=>Ess_M2ePro_Helper_Component_Amazon::NICK,
                                                  'back'=>Mage::helper('M2ePro')
                                                      ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                                                         array('tab'=>$tabIdAmazon)))) . '\')',
                           'class' => 'orders_clear_log'
                       ) );
        $this->setChild('amazon_orders_clear_log', $buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' =>
                               'setLocation(\''
                               .$this->getUrl('*/adminhtml_log/synchronization',
                                              array('synch_task'=>
                                                        Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS,
                                                    'back'=>Mage::helper('M2ePro')
                                                                ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                                                                   array('tab'=>$tabIdAmazon)),
                                                    'component'=>Ess_M2ePro_Helper_Component_Amazon::NICK)).'\')',
                           'class' => 'button_link'
                       ) );
        $this->setChild('amazon_orders_view_log', $buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Run Now'),
                           'onclick' => 'SynchronizationHandlerObj.saveSettings(\'runNowOtherListings\', \''
                                        .Ess_M2ePro_Helper_Component_Amazon::NICK.'\');',
                           'class' => 'other_listings_run_now'
                       ) );
        $this->setChild('amazon_other_listings_run_now', $buttonBlock);

        $tempStr = Mage::helper('M2ePro')->__('Are you sure?');

        $synchTaskOtherListings = Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS;

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('Clear Log'),
                           'onclick' => 'deleteConfirm(\''. $tempStr.'\', \''
                                        .$this->getUrl('*/adminhtml_synchronization/clearLog',
                                                       array('synch_task'=>$synchTaskOtherListings,
                                                             'component'=>Ess_M2ePro_Helper_Component_Amazon::NICK,
                                                             'back'=>Mage::helper('M2ePro')
                                                                 ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                                                                    array('tab'=>$tabIdAmazon))))
                                        .'\')',
                           'class' => 'orders_clear_log'
                       ) );
        $this->setChild('amazon_other_listings_clear_log', $buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                           'label'   => Mage::helper('M2ePro')->__('View Log'),
                           'onclick' => 'setLocation(\''
                                        .$this->getUrl('*/adminhtml_log/synchronization',
                                                       array('synch_task'=>$synchTaskOtherListings,
                                                             'back'=>Mage::helper('M2ePro')
                                                                 ->makeBackUrlParam('*/adminhtml_synchronization/index',
                                                                                    array('tab'=>$tabIdAmazon)),
                                                             'component'=>Ess_M2ePro_Helper_Component_Amazon::NICK))
                                        .'\')',
                           'class' => 'button_link'
                       ) );
        $this->setChild('amazon_other_listings_view_log', $buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}