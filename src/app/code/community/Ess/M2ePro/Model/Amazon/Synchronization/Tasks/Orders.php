<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const LOCK_ITEM_PREFIX = 'synchronization_amazon_orders';

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN RESERVE CANCELLATION SYNCH
        //---------------------------
        $this->executeReserveCancellationSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->executeReceiveSynch();
        //---------------------------

        // RUN UPDATE SYNCH
        //---------------------------
        $this->executeUpdateSynch();
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

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Orders Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Orders Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Orders Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Orders Synchronization" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    public function executeReceiveSynch()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get and process items from Amazon');

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Amazon_Account::ORDERS_MODE_YES);

        $accountsTotalCount = $accountsCollection->getSize();

        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL;

        if ($accountsTotalCount > 0) {
            $percentsForAccount = self::PERCENTS_INTERVAL/(int)$accountsCollection->getSize();
        }

        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */

            $marketplaces = $accountObj->getChildObject()->getMarketplacesItems();

            foreach ($marketplaces as $marketplace) {

                $marketplaceObj = $marketplace['object'];

                if (!$this->isLockedAccountMarketplace($accountObj->getId(),$marketplaceObj->getId())) {
                    $this->updateAccountMarketplace($accountObj,$marketplaceObj);
                }
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function isLockedAccountMarketplace($accountId, $marketplaceId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId.'_'.$marketplaceId);

        $tempGroup = '/amazon/synchronization/settings/orders/';
        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')->getConfig()
                                    ->getGroupValue($tempGroup,'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        return $lockItem->isExist();
    }

    //####################################

    private function updateAccountMarketplace(Ess_M2ePro_Model_Account $accountObj,
                                              Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        $this->_profiler->addTitle(
            'Starting account "'.$accountObj->getTitle().'" and marketplace "'.$marketplaceObj->getTitle().'"'
        );
        $this->_profiler->addTimePoint(__METHOD__.'send'.$accountObj->getId(),'Get orders from Amazon');
//->__('Task "Orders Synchronization" for Amazon account: "%s" and marketplace "%s" is started. Please wait...')
        $tempString = 'Task "Orders Synchronization" for Amazon account: ';
        $tempString .= '"%s" and marketplace "%s" is started. Please wait...';
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__(
            $tempString, $accountObj->getTitle(), $marketplaceObj->getTitle())
        );

        // Get orders from Amazon for account
        //---------------------------
        $fromDate = $this->prepareFromDate($accountObj->getData('orders_last_synchronization'));
        $params = array(
            'from_date' => $fromDate
        );

        if (is_null($accountObj->getData('orders_last_synchronization'))) {
            $accountObj->setData('orders_last_synchronization', $fromDate)->save();
        }

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
        $dispatcherObject->processConnector('tasks', 'orders', 'requester',
                                            $params, $marketplaceObj, $accountObj,
                                            'Ess_M2ePro_Model_Amazon_Synchronization');
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$accountObj->getId());
        $this->_profiler->addEol();
    }

    private function prepareFromDate($lastFromDate)
    {
        // Get last from date
        //------------------------
        if (is_null($lastFromDate)) {
            $lastFromDate = new DateTime('now', new DateTimeZone('UTC'));
            $lastFromDate->modify('-6 hours');
        } else {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }
        //------------------------

        // Get min date for synch
        //------------------------
        $minDate = new DateTime('now',new DateTimeZone('UTC'));
        $minDate->modify('-7 days');
        //------------------------

        // Prepare last date
        //------------------------
        if ((int)$lastFromDate->format('U') < (int)$minDate->format('U')) {
            $lastFromDate = $minDate;
        }
        //------------------------

        return $lastFromDate->format('Y-m-d H:i:s');
    }

    //####################################

    private function executeReserveCancellationSynch()
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $configGroup = '/amazon/synchronization/settings/orders/';

        $reserveCancellationMode = (bool)$config->getGroupValue($configGroup.'reserve_cancellation/', 'mode');

        $interval = $config->getGroupValue($configGroup.'reserve_cancellation/', 'interval');
        $lastAccess = $config->getGroupValue($configGroup.'reserve_cancellation/', 'last_access');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        $isNowTimeToRun = is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval;

        if ($reserveCancellationMode && $isNowTimeToRun) {
            $config->setGroupValue(
                $configGroup.'reserve_cancellation/', 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Reserve_Cancellation();
            $tempSynch->process();
        }
    }

    //####################################

    private function executeUpdateSynch()
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $configGroup = '/amazon/synchronization/settings/orders/';

        $mode = (bool)$config->getGroupValue($configGroup.'update/', 'mode');

        if ($mode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Update();
            $tempSynch->process();
        }
    }

    //####################################
}