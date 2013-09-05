<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_GettingProductsLinks
        extends Ess_M2ePro_Model_Play_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

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

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Play::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Getting Products Links');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Getting Products Links" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Getting Products Links" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get and process links on products from Play');

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Account');
        $accountsTotalCount = (int)$accountsCollection->getSize();

        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL;

        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        $marketplaceObj = Mage::helper('M2ePro/Component_Play')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Play::MARKETPLACE_VIRTUAL_ID
        );

        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $this->processAccountMarketplaceLinks($accountObj,$marketplaceObj);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount * $accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccountMarketplaceLinks(Ess_M2ePro_Model_Account $accountObj,
                                                    Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        $config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();

        $syncGroup = '/other_listings/';
        $playSyncGroup = '/play/other_listings/';

        $isGlobalSyncEnabled = (bool)$config->getGroupValue($syncGroup,'mode');
        $isPlaySyncEnabled = (bool)$config->getGroupValue($playSyncGroup,'mode');

        $firstOtherListingsSyncTime = $accountObj->getChildObject()->getOtherListingsFirstSynchronization();
        $currentGmtDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $accountCreateDate = $accountObj->getData('create_date');

        if ($isGlobalSyncEnabled && $isPlaySyncEnabled && strtotime($accountCreateDate) + 24*3600 > $currentGmtDate &&
            (is_null($firstOtherListingsSyncTime) ||
             strtotime($firstOtherListingsSyncTime) + 3*3600 > $currentGmtDate)) {

            return;
        }

        $this->_profiler->addTitle(
            'Starting account "'.$accountObj->getTitle().'" and marketplace "'.$marketplaceObj->getTitle().'"'
        );
        $this->_profiler->addTimePoint(__METHOD__.'send'.$accountObj->getId(),'Get Product Links from Play');
        $status = 'Task "Getting Products Links" for Play.com account: "%s" and marketplace "%s" ';
        $status .= 'is started. Please wait...';
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__($status, $accountObj->getTitle(), $marketplaceObj->getTitle())
        );

        $updateByPagesSettings = $accountObj->getChildObject()->getDecodedListingsUpdateLinksSettings();

        if (is_null($updateByPagesSettings) || (
            (int)$updateByPagesSettings['next_status'] <= 3 &&
            (int)$updateByPagesSettings['next_page'] < 10000)) {

            $this->updateInventoryLinksByPages($accountObj,$marketplaceObj);
        } else {
            $this->updateInventoryLinksByListingsIds($accountObj,$marketplaceObj);
        }

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$accountObj->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function updateInventoryLinksByPages(Ess_M2ePro_Model_Account $accountObj,
                                                 Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        // get server data
        $inputData = array(
            'necessary_status' => 2,
            'necessary_page' => 1
        );

        $updateByPagesSettings = $accountObj->getChildObject()->getDecodedListingsUpdateLinksSettings();

        if (!is_null($updateByPagesSettings)) {
            $inputData['necessary_status'] = (int)$updateByPagesSettings['next_status'];
            $inputData['necessary_page'] = (int)$updateByPagesSettings['next_page'];
        }

        $responseData = Mage::getModel('M2ePro/Connector_Server_Play_Dispatcher')
                                ->processVirtualAbstract('inventory','get','pagesProductsLinks',
                                                         $inputData,NULL,
                                                         NULL,$accountObj->getId(),NULL);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        // process and update received data
        $this->updateReceivedLinks($responseData['items']);

        // calculate and save next data
        $nextStatus = (int)$inputData['necessary_status'];
        $nextPage = (int)$inputData['necessary_page'] + (int)$responseData['processed_pages'];

        if ((bool)$responseData['is_last_page']) {
            if ($nextStatus >= 3) {
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
            ->setData('listings_update_links_settings',$updateByPagesSettings)
            ->save();
    }

    private function updateInventoryLinksByListingsIds(Ess_M2ePro_Model_Account $accountObj,
                                                       Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        $listingsForUpdate = $this->getListingsProductForUpdate($accountObj,$marketplaceObj);
        $listingsCollectionSize = $listingsForUpdate->getSize();

        if (!$listingsCollectionSize || $listingsCollectionSize <= 0) {

            $listingsForUpdate = $this->getListingsOtherForUpdate($accountObj,$marketplaceObj);
            $listingsCollectionSize = $listingsForUpdate->getSize();

            if (!$listingsCollectionSize || $listingsCollectionSize <= 0) {
                return;
            }
        }

        $neededItems = array();
        foreach ($listingsForUpdate->getItems() as $tempItem) {
            $neededItems[] = $tempItem->getData('play_listing_id');
        }

        if (count($neededItems) <= 0) {
            return;
        }

        $responseData = Mage::getModel('M2ePro/Connector_Server_Play_Dispatcher')
                                ->processVirtualAbstract('inventory','get','listingsIdsProductsLinks',
                                                         array('items'=>$neededItems),NULL,
                                                         NULL,$accountObj->getId(),NULL);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        // process and update received data
        $this->updateReceivedLinks($responseData['items']);

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$accountObj->getId());
        $this->_profiler->addEol();
    }

    //------------------------------------

    private function updateReceivedLinks(array $items)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $listingOtherTable = Mage::getResourceModel('M2ePro/Play_Listing_Other')->getMainTable();
        $listingTable = Mage::getResourceModel('M2ePro/Play_Listing_Product')->getMainTable();

        $receivedItems = array();
        foreach ($items as $playListingId => $receivedInfo) {

            if (isset($receivedItems[$playListingId]) ||
                $receivedInfo['play_id'] == '' || $receivedInfo['category_code'] == '') {
                continue;
            }

            $linkInfo = array(
                'play_id' => $receivedInfo['play_id'],
                'category_code' => $receivedInfo['category_code']
            );

            $receivedItems[$playListingId] = $linkInfo;

            $connWrite->update(
                $listingOtherTable,
                array('link_info' => json_encode($linkInfo)),
                array('play_listing_id = ?' => (int)$playListingId)
            );

            $connWrite->update(
                $listingTable,
                array('link_info' => json_encode($linkInfo)),
                array('play_listing_id = ?' => (int)$playListingId)
            );
        }
    }

    //####################################

    private function getListingsProductForUpdate(Ess_M2ePro_Model_Account $accountObj,
                                                 Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $listingsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $listingsCollection->getSelect()->join(array('l' => $listingTable),
                                                     'main_table.listing_id = l.id',
                                                     array());
        $listingsCollection->getSelect()->where('l.marketplace_id = ?',$marketplaceObj->getId());
        $listingsCollection->getSelect()->where('l.account_id = ?',$accountObj->getId());
        $listingsCollection->getSelect()->where('`second_table`.`link_info` IS NULL');
        $listingsCollection->getSelect()->where('`second_table`.`play_listing_id` IS NOT NULL');
        $listingsCollection->getSelect()->limit(5);

        return $listingsCollection;
    }

    private function getListingsOtherForUpdate(Ess_M2ePro_Model_Account $accountObj,
                                               Ess_M2ePro_Model_Marketplace $marketplaceObj)
    {
        /** @var $listingsOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingsOtherCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Other');
        $listingsOtherCollection->addFieldToFilter('`main_table`.account_id',(int)$accountObj->getId());
        $listingsOtherCollection->getSelect()->where('`second_table`.`link_info` IS NULL');
        $listingsOtherCollection->getSelect()->where('`second_table`.`play_listing_id` IS NOT NULL');
        $listingsOtherCollection->getSelect()->limit(3);

        return $listingsOtherCollection;
    }

    //####################################
}