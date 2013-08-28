<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_OtherListings extends Ess_M2ePro_Model_Buy_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const INTERVAL_COEFFICIENT_VALUE = 10000;

    const LOCK_ITEM_PREFIX = 'synchronization_buy_other_listings';

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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS);

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Buy::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'3rd Party Listings Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'3rd Party Listings Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" is finished. Please wait...')
        );

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
        $this->executeUpdateInventoryData();

        $this->_lockItem->setPercents(self::PERCENTS_START + self::PERCENTS_INTERVAL/2);
        $this->_lockItem->activate();

        $this->executeUpdateInventoryTitle();
    }

    //####################################

    private function executeUpdateInventoryData()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get and process items from Buy');

        // Prepare last time
        $this->prepareLastTime();

        // Check locked last time
        if ($this->isLockedLastTime() &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER) {
            return;
        }

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $percentsForAccount = self::PERCENTS_INTERVAL/2;
        $accountsTotalCount = (int)$accountsCollection->getSize();

        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        $marketplaceObj = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_VIRTUAL_ID
        );

        $accountIteration = 1;
        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */

            if (!$this->isLockedAccountMarketplace($accountObj->getId(),$marketplaceObj->getId())) {
                $this->processAccountMarketplaceInventoryData($accountObj,$marketplaceObj);
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->setCheckLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executeUpdateInventoryTitle()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get and process titles from Buy');

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $percentsForAccount = self::PERCENTS_INTERVAL/2;
        $accountsTotalCount = (int)$accountsCollection->getSize();

        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        $marketplaceObj = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_VIRTUAL_ID
        );

        $accountIteration = 1;
        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $this->processAccountMarketplaceInventoryTitle($accountObj,$marketplaceObj);

            $this->_lockItem->setPercents(self::PERCENTS_START + self::PERCENTS_INTERVAL/2
                                          + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccountMarketplaceInventoryData(Ess_M2ePro_Model_Account $accountObj,
                                                            Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        $this->_profiler->addTitle(
            'Starting account "'.$accountObj->getTitle().'" and marketplace "'.$marketplaceObj->getTitle().'"'
        );
        $this->_profiler->addTimePoint(__METHOD__.'send'.$accountObj->getId(),'Get inventory from Buy');

        $tempString = Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" for Rakuten.com account: ');
        $tempString .= Mage::helper('M2ePro')->__('"%s" and marketplace "%s" is started. Please wait...',
                                                  $accountObj->getTitle(),
                                                  Mage::helper('M2ePro')->__($marketplaceObj->getTitle()));
        $this->_lockItem->setStatus($tempString);

        // Get all changes on Buy for account
        //---------------------------
        $dispatcherObject = Mage::getModel('M2ePro/Buy_Connector')->getDispatcher();
        $dispatcherObject->processConnector('tasks', 'otherListings' ,'requester',
                                            array(), $marketplaceObj, $accountObj,
                                            'Ess_M2ePro_Model_Buy_Synchronization');
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$accountObj->getId());
        $this->_profiler->addEol();
    }

    private function processAccountMarketplaceInventoryTitle(Ess_M2ePro_Model_Account $accountObj,
                                                             Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        $firstSynchronizationTime = $accountObj->getChildObject()->getOtherListingsFirstSynchronization();
        if (is_null($firstSynchronizationTime) ||
            strtotime($firstSynchronizationTime) + 3*3600 > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return;
        }

        $this->_profiler->addTitle(
            'Starting account "'.$accountObj->getTitle().'" and marketplace "'.$marketplaceObj->getTitle().'"'
        );
        $this->_profiler->addTimePoint(__METHOD__.'send'.$accountObj->getId(),'Get inventory from Buy');

        $tempString = Mage::helper('M2ePro')->__('Task "3rd Party Listings Synchronization" for Rakuten.com account: ');
        $tempString .= Mage::helper('M2ePro')->__(
            '"%s" and marketplace "%s" is started. Please wait...',
            $accountObj->getTitle(),
            Mage::helper('M2ePro')->__($marketplaceObj->getTitle())
        );
        $this->_lockItem->setStatus($tempString);

        $updateByPagesSettings = $accountObj->getChildObject()->getDecodedOtherListingsUpdateTitlesSettings();

        if (is_null($updateByPagesSettings) || (
                (int)$updateByPagesSettings['next_status'] <= 2 &&
                (int)$updateByPagesSettings['next_page'] < 10000
            )) {

            $this->updateInventoryTitlesByPages($accountObj,$marketplaceObj);
        } else {
            $this->updateInventoryTitlesBySkus($accountObj,$marketplaceObj);
        }

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$accountObj->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function updateInventoryTitlesByPages(Ess_M2ePro_Model_Account $accountObj,
                                                  Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        // get server data

        $inputData = array(
            'necessary_status' => 0,
            'necessary_page' => 1
        );

        $updateByPagesSettings = $accountObj->getChildObject()->getDecodedOtherListingsUpdateTitlesSettings();

        if (!is_null($updateByPagesSettings)) {
            $inputData['necessary_status'] = (int)$updateByPagesSettings['next_status'];
            $inputData['necessary_page'] = (int)$updateByPagesSettings['next_page'];
        }

        $responseData = Mage::getModel('M2ePro/Connector_Server_Buy_Dispatcher')
                            ->processVirtualAbstract('inventory','get','pagesTitles',
                                                     $inputData,NULL,
                                                     NULL,$accountObj->getId(),NULL);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        // process and update received data
        $this->updateReceivedTitles($responseData['items'],$accountObj,$marketplaceObj);

        // calculate and save next data

        $nextStatus = (int)$inputData['necessary_status'];
        $nextPage = (int)$inputData['necessary_page'] + (int)$responseData['processed_pages'];

        if ((bool)$responseData['is_last_page']) {
            if ($nextStatus >= 2) {
                $nextPage = 10000;
            } else {
                $nextStatus++;
                $nextPage = 1;
            }
        }

        $updateByPagesSettings = array(
            'next_status' => $nextStatus,
            'next_page' => $nextPage
        );

        $updateByPagesSettings = json_encode($updateByPagesSettings);

        $accountObj->getChildObject()
            ->setData('other_listings_update_titles_settings',$updateByPagesSettings)
            ->save();
    }

    private function updateInventoryTitlesBySkus(Ess_M2ePro_Model_Account $accountObj,
                                                 Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        // get server data

        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('`main_table`.account_id',(int)$accountObj->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`title` IS NULL');
        $listingOtherCollection->getSelect()->limit(10);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $neededItems = array();
        foreach ($listingOtherCollection->getItems() as $tempItem) {
            $neededItems[] = $tempItem->getData('general_id');
        }

        $responseData = Mage::getModel('M2ePro/Connector_Server_Buy_Dispatcher')
                            ->processVirtualAbstract('inventory','get','skusTitles',
                                                     array('items'=>$neededItems),NULL,
                                                     NULL,$accountObj->getId(),NULL);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        // process and update received data
        $this->updateReceivedTitles($responseData['items'],$accountObj,$marketplaceObj);
    }

    //------------------------------------

    private function updateReceivedTitles(array $items,
                                          Ess_M2ePro_Model_Account $accountObj,
                                          Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $bloTable = Mage::getResourceModel('M2ePro/Buy_Listing_Other')->getMainTable();
        $lolTable = Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable();

        /** @var $mappingModel Ess_M2ePro_Model_Buy_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Mapping');

        /** @var $movingModel Ess_M2ePro_Model_Buy_Listing_Other_Moving */
        $movingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Moving');

        $receivedItems = array();
        foreach ($items as $generalId => $title) {

            if (isset($receivedItems[$generalId])) {
                continue;
            }

            $receivedItems[$generalId] = $title;

            $listingsOthersWithEmptyTitles = array();
            if ($accountObj->getChildObject()->isOtherListingsMappingEnabled()) {
                /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
                $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
                $listingOtherCollection->addFieldToFilter('`main_table`.account_id',(int)$accountObj->getId());
                $listingOtherCollection->addFieldToFilter('`second_table`.`general_id`',(int)$generalId);
                $listingOtherCollection->addFieldToFilter('`second_table`.`title`',array('null' => true));
                $listingsOthersWithEmptyTitles = $listingOtherCollection->getItems();
            }

            $connWrite->update(
                $bloTable,
                array('title' => (string)$title),
                array('general_id = ?' => (int)$generalId)
            );

            $connWrite->update(
                $lolTable,
                array('title' => (string)$title),
                array(
                    'identifier = ?' => (int)$generalId,
                    'component_mode = ?' => Ess_M2ePro_Helper_Component_Buy::NICK
                )
            );

            if (count($listingsOthersWithEmptyTitles) > 0) {
                foreach ($listingsOthersWithEmptyTitles as $listingOtherModel) {

                    $listingOtherModel->setData('title',(string)$title);

                    $mappingModel->initialize($marketplaceObj,$accountObj);
                    $mappingResult = $mappingModel->autoMapOtherListingProduct($listingOtherModel);

                    if ($mappingResult) {

                        if (!$accountObj->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
                            continue;
                        }

                        $movingModel->initialize($marketplaceObj,$accountObj);
                        $movingModel->autoMoveOtherListingProduct($listingOtherModel);
                    }
                }
            }
        }
    }

    //####################################

    private function prepareLastTime()
    {
        $lastTime = $this->getCheckLastTime();
        if (empty($lastTime)) {
            $lastTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastTime->modify("-1 year");
            $this->setCheckLastTime($lastTime);
        }
    }

    private function isLockedLastTime()
    {
        $lastTime = strtotime($this->getCheckLastTime());

        $tempGroup = '/buy/synchronization/settings/other_listings/';
        $interval = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'interval');

        $totalItems = (int)Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product')->getSize();
        $totalItems += (int)Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other')->getSize();
        $intervalCoefficient = ($totalItems > 0) ? (int)ceil($totalItems/self::INTERVAL_COEFFICIENT_VALUE) : 1;

        if ($lastTime + ($interval*$intervalCoefficient) > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return true;
        }

        return false;
    }

    private function getCheckLastTime()
    {
        $tempGroup = '/buy/synchronization/settings/other_listings/';
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'last_time');
    }

    private function setCheckLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }
        $tempGroup = '/buy/synchronization/settings/other_listings/';
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue($tempGroup,'last_time',$time);
    }

    //------------------------------------

    private function isLockedAccountMarketplace($accountId, $marketplaceId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId.'_'.$marketplaceId);

        $tempGroup = '/buy/synchronization/settings/other_listings/';
        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')->getConfig()
                                    ->getGroupValue($tempGroup,'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        if ($lockItem->isExist()) {
            return true;
        }

        return false;
    }

    //####################################
}