<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Update extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const LOCK_ITEM_PREFIX = 'synchronization_ebay_orders_update';

    //####################################

    // ->__('eBay Orders Update Synchronization')
    private $name = 'eBay Orders Update Synchronization';

    /** @var Ess_M2ePro_Model_Config_Synchronization */
    private $config = NULL;

    //####################################

    public function __construct()
    {
        $this->config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();

        parent::__construct();
    }

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS);

        $this->_profiler->addEol();
        $this->_profiler->addTitle($this->name);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($this->name));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is started. Please wait...', $this->name));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is finished. Please wait...', $this->name));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    public function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update orders on eBay');

        // delete changes, which were processed 3 or more times
        //------------------------------
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Ebay::NICK
            );
        //------------------------------

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Ebay_Account::ORDERS_MODE_YES);
        $percentsForAccount = self::PERCENTS_INTERVAL;
        $accountsTotalCount = (int)$accountsCollection->getSize();

        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        $accountIteration = 1;
        foreach ($accountsCollection->getItems() as $account) {
            $this->processAccount($account);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration++);
            $this->_lockItem->activate();
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $title = 'Starting account "%s"';
        $title = sprintf($title, $account->getTitle());

        $this->_profiler->addTitle($title);
        $this->_profiler->addTimePoint(__METHOD__.'send'.$account->getId(),'Update orders on eBay');

        $status = 'Task "%s" for eBay "%s" Account is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle());
        $this->_lockItem->setStatus($status);

        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->setPageSize(200);
        $changesCollection->getSelect()->group(array('order_id'));

        if ($changesCollection->getSize() == 0) {
            return;
        }

        // Update orders status on eBay
        //---------------------------
        foreach ($changesCollection as $change) {
            Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount(array($change->getId()));

            $this->processChange($change);
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$account->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function processChange(Ess_M2ePro_Model_Order_Change $change)
    {
        $changeParams = $change->getParams();

        if ($change->isPaymentUpdateAction()) {
            $order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order')->load($change->getOrderId());
            $order->getId() && $order->getChildObject()->updatePaymentStatus();

            return;
        }

        if ($change->isShippingUpdateAction()) {
            $params = array();

            if (!empty($changeParams['tracking_details'])) {
                $params = $changeParams['tracking_details'];
            }

            if (!empty($changeParams['item_id'])) {
                $item = Mage::helper('M2ePro/Component_Ebay')->getModel('Order_Item')->load($changeParams['item_id']);
                $item->getId() && $item->getChildObject()->updateShippingStatus($params);
            } else {
                $order = Mage::helper('M2ePro/Component_Ebay')->getModel('Order')->load($change->getOrderId());
                $order->getId() && $order->getChildObject()->updateShippingStatus($params);
            }

            return;
        }
    }

    //####################################
}