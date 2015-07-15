<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update
    extends Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_ebay_other_listings';

    //####################################

    protected function getNick()
    {
        return '/update/';
    }

    protected function getTitle()
    {
        return 'Update';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 50;
    }

    //####################################

    protected function performActions()
    {
        $this->executeUpdateInventoryData();
        $this->executeUpdateInventorySku();
    }

    //####################################

    private function executeUpdateInventoryData()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval()/2) / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party Listings" Action for eBay Account: "%account_title%" is started. Please wait...
            $status = 'The "Update 3rd Party Listings" Action for eBay Account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                $this->executeUpdateInventoryDataAccount($account);

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party Listings" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update 3rd Party Listings" Action for eBay Account: "%account_title%" is finished. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    private function executeUpdateInventoryDataAccount(Ess_M2ePro_Model_Account $account)
    {
        $sinceTime = $this->getSinceTimeByAccount($account);

        if (empty($sinceTime)) {

            $marketplaceCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace');
            $marketplaceCollection->addFieldToFilter('status',Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $marketplace = $marketplaceCollection->getFirstItem();

            if (!$marketplace->getId()) {
                $marketplace = Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_US;
            }

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
            $connectorObj = $dispatcherObject->getConnector('otherListings', 'update' ,'requester',
                                                            array(), $marketplace, $account, NULL,
                                                            'Ess_M2ePro_Model_Ebay_Synchronization');
            $dispatcherObject->process($connectorObj);
            return;
        }

        $sinceTime = $this->prepareSinceTime($sinceTime);
        $changes = $this->getChangesByAccount($account, $sinceTime);

        /** @var $updatingModel Ess_M2ePro_Model_Ebay_Listing_Other_Updating */
        $updatingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Updating');
        $updatingModel->initialize($account);
        $updatingModel->processResponseData($changes);
    }

    // -----------------------------------

    private function executeUpdateInventorySku()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval()/2) / count($accounts);

        foreach ($accounts as $account) {

            /** @var $account Ess_M2ePro_Model_Account **/

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party SKU(s)" Action for eBay Account: "%account_title%" is started. Please wait...
            $status = 'The "Update 3rd Party SKU(s)" Action for eBay Account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                $this->executeUpdateInventorySkuAccount($account);

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }
            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party SKU(s)" Action for eBay Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update 3rd Party SKU(s)" Action for eBay Account: "%account_title%" is finished.'.
                ' Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $this->getPercentsInterval()/2 +
                                                    $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    private function executeUpdateInventorySkuAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('`main_table`.account_id',(int)$account->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`sku` IS NULL');
        $listingOtherCollection->getSelect()->order('second_table.start_date ASC');
        $listingOtherCollection->getSelect()->limit(200);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $firstItem = $listingOtherCollection->getFirstItem();

        $sinceTime = $firstItem->getData('start_date');
        $receivedData = $this->receiveSkusFromEbay($account, $sinceTime);

        if (empty($receivedData['items'])) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {
                $listingOther->getChildObject()->setData('sku','')->save();
            }
            return;
        }

        $this->updateSkusForReceivedItems($listingOtherCollection, $account, $receivedData['items']);
        $this->updateSkusForNotReceivedItems($listingOtherCollection, $receivedData['to_time']);
    }

    //####################################

    private function getChangesByAccount(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $response = $this->receiveChangesFromEbay($account, array('since_time'=>$sinceTime));

        if ($response) {
            return (array)$response;
        }

        $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $sinceTime->modify("-1 day");
        $sinceTime = $sinceTime->format('Y-m-d H:i:s');

        $response = $this->receiveChangesFromEbay($account, array('since_time'=>$sinceTime));

        if ($response) {
            return (array)$response;
        }

        $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
        $sinceTime = $sinceTime->format('Y-m-d H:i:s');

        $response = $this->receiveChangesFromEbay($account, array('since_time'=>$sinceTime));

        if ($response) {
            return (array)$response;
        }

        return array();
    }

    private function receiveChangesFromEbay(Ess_M2ePro_Model_Account $account, array $paramsConnector = array())
    {
        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('item','get','changes',
                                                            $paramsConnector,NULL,
                                                            NULL,$account->getId(),NULL);

        $response = $dispatcherObj->process($connectorObj);
        $this->processResponseMessages($connectorObj);

        if (!isset($response['items']) || !isset($response['to_time'])) {
            return NULL;
        }

        return $response;
    }

    private function processResponseMessages(Ess_M2ePro_Model_Connector_Protocol $connectorObj)
    {
        foreach ($connectorObj->getErrorMessages() as $message) {

            if (!$connectorObj->isMessageError($message) && !$connectorObj->isMessageWarning($message)) {
                continue;
            }

            $logType = $connectorObj->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                               : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    // -----------------------------------

    private function updateSkusForReceivedItems($listingOtherCollection,Ess_M2ePro_Model_Account $account,array $items)
    {
        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');

        foreach ($items as $item) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {

                /** @var $listingOther Ess_M2ePro_Model_Listing_Other */

                if ((float)$listingOther->getData('item_id') != $item['id']) {
                    continue;
                }

                $listingOther->getChildObject()->setData('sku',(string)$item['sku'])->save();

                if ($account->getChildObject()->isOtherListingsMappingEnabled()) {
                    $mappingModel->initialize($account);
                    $mappingModel->autoMapOtherListingProduct($listingOther);
                }

                break;
            }
        }
    }

    //-- eBay item IDs which were removed can lead to the issue and getting SKU process freezes
    private function updateSkusForNotReceivedItems($listingOtherCollection, $toTimeReceived)
    {
        foreach ($listingOtherCollection->getItems() as $listingOther) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other $ebayListingOther */
            $ebayListingOther = $listingOther->getChildObject();

            if (!is_null($ebayListingOther->getSku())) {
                continue;
            }

            if (strtotime($ebayListingOther->getStartDate()) >= strtotime($toTimeReceived)) {
                continue;
            }

            $ebayListingOther->setData('sku', '')->save();
        }
    }

    private function receiveSkusFromEbay(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $sinceTime = new DateTime($sinceTime,new DateTimeZone('UTC'));
        $sinceTime->modify('-1 minute');
        $sinceTime = $sinceTime->format('Y-m-d H:i:s');

        $inputData = array(
            'since_time'    => $sinceTime,
            'only_one_page' => true
        );

        $dispatcherObj = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector('item','get','all',
                                                            $inputData,NULL,
                                                            NULL,$account->getId(),NULL);

        $responseData = $dispatcherObj->process($connectorObj);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return array();
        }

        return $responseData;
    }

    //####################################

    private function getSinceTimeByAccount(Ess_M2ePro_Model_Account $account)
    {
        return $account->getData('other_listings_last_synchronization');
    }

    private function prepareSinceTime($sinceTime)
    {
        $minTime = new DateTime('now', new DateTimeZone('UTC'));
        $minTime->modify("-1 month");

        if (empty($sinceTime) || strtotime($sinceTime) < (int)$minTime->format('U')) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime->modify("-10 days");
            $sinceTime = $sinceTime->format('Y-m-d H:i:s');
        }

        return $sinceTime;
    }

    private function isLockedAccount(Ess_M2ePro_Model_Account $account)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$account->getId());
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        return $lockItem->isExist();
    }

    //####################################
}