<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Reserve_Cancellation
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    // ->__('eBay Qty Reserve Cancellation Synchronization')
    private $name = 'eBay Qty Reserve Cancellation Synchronization';

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //$this->createRunnerActions();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        //$this->executeRunnerActions();
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

    private function execute()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Ebay_Account::ORDERS_MODE_YES);

        $accountsTotalCount = $accountsCollection->getSize();

        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL;

        if ($accountsTotalCount > 0) {
            $percentsForAccount = self::PERCENTS_INTERVAL/(int)$accountsCollection->getSize();
        }

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Model_Order_Log::INITIATOR_EXTENSION);

        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */

            $this->processAccount($accountObj, $percentsForAccount);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccount(Ess_M2ePro_Model_Account $account, $percentsForAccount)
    {
        //---------------------------
        $this->_profiler->addEol();
        $this->_profiler->addTitle('Starting account "'.$account->getTitle().'"');

        // ->__('Task "%s" for eBay account: "%s" is started. Please wait...')
        $status = 'Task "%s" for eBay account: "%s" is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle());
        $this->_lockItem->setStatus($status);

        $currentPercent = $this->_lockItem->getPercents();
        //---------------------------

        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order')
                ->addFieldToFilter('account_id', $account->getId())
                ->addFieldToFilter('reservation_state', Ess_M2ePro_Model_Order_Reserve::STATE_PLACED);

        $reservationDays = (int)$account->getChildObject()->getQtyReservationDays();
        if ($reservationDays > 0) {
            $minReservationStartDate = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
            $minReservationStartDate->modify('- ' . $reservationDays . ' days');
            $minReservationStartDate = $minReservationStartDate->format('Y-m-d H:i');

            $collection->addFieldToFilter('reservation_start_date', array('lteq' => $minReservationStartDate));
        }

        /** @var $reservedOrders Ess_M2ePro_Model_Order[] */
        $reservedOrders = $collection->getItems();

        //---------------------------
        $this->_profiler->addEol();
        $this->_profiler->addTitle('Total orders with expired reservation: '.count($reservedOrders));

        $currentPercent = $currentPercent + $percentsForAccount * 0.1;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();
        //---------------------------

        if (count($reservedOrders) == 0) {
            return;
        }

        //---------------------------
        $this->_profiler->addTimePoint(__METHOD__.'process'.$account->getId(),'Release qty for expired reservation');

        $status = 'Task "%s" for eBay account: "%s" is in data processing state. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $account->getTitle());
        $this->_lockItem->setStatus($status);
        //---------------------------

        $percentPerOrder = ($percentsForAccount - $currentPercent) / count($reservedOrders);
        $tempPercent = 0;

        foreach ($reservedOrders as $order) {

            $order->getReserve()->release();

            $tempPercent += $percentPerOrder;

            if (floor($tempPercent) > 0) {
                $currentPercent += floor($tempPercent);
                $tempPercent -= floor($tempPercent);

                $this->_lockItem->setPercents($currentPercent);
                $this->_lockItem->activate();
            }
        }

        //---------------------------
        $this->_profiler->saveTimePoint(__METHOD__.'process'.$account->getId());
        $this->_profiler->addEol();
        $this->_profiler->addTitle('End account "'.$account->getTitle().'"');
        //---------------------------
    }

    //####################################
}