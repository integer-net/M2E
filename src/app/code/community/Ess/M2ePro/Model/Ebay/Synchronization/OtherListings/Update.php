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

            $this->getActualOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party Listings" action for eBay account: "%account_title%" is started. Please wait...
            $status = 'The "Update 3rd Party Listings" action for eBay account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process account '.$account->getTitle()
                );

                $this->executeUpdateInventoryDataAccount($account);

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party Listings" action for eBay account: "%account_title%" is finished. Please wait...
            $status = 'The "Update 3rd Party Listings" action for eBay account: "%account_title%" is finished. ';
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
            $dispatcherObject->processConnector('otherListings', 'update' ,'requester',
                                                array(), $marketplace, $account, NULL,
                                                'Ess_M2ePro_Model_Ebay_Synchronization');
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

            $this->getActualOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party SKU(s)" action for eBay account: "%account_title%" is started. Please wait...
            $status = 'The "Update 3rd Party SKU(s)" action for eBay account: "%account_title%" is started. ';
            $status .= 'Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process account '.$account->getTitle()
                );

                $this->executeUpdateInventorySkuAccount($account);

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }
            // M2ePro_TRANSLATIONS
            // The "Update 3rd Party SKU(s)" action for eBay account: "%account_title%" is finished. Please wait...
            $status = 'The "Update 3rd Party SKU(s)" action for eBay account: "%account_title%" is finished.'.
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
        $items = $this->receiveSkusFromEbay($account, $sinceTime);

        if (count($items) <= 0) {
            foreach ($listingOtherCollection->getItems() as $listingOther) {
                $listingOther->getChildObject()->setData('sku','')->save();
            }
            return;
        }

        //-- removed eBay item ID can lead to the issue and getting SKU process freezes
        $isItemIdReturned = false;
        foreach ($items as $item) {
            if ($item['id'] == $firstItem->getData('item_id')) {
                $isItemIdReturned = true;
                break;
            }
        }
        !$isItemIdReturned && $firstItem->getChildObject()->setData('sku','')->save();

        $this->updateSkusByReceivedItems($account, $listingOtherCollection, $items);
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
        $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                                    ->processVirtual('item','get','changes',
                                                     $paramsConnector,NULL,
                                                     NULL,$account->getId(),NULL);

        if (!isset($response['items']) || !isset($response['to_time'])) {
            return NULL;
        }

        return $response;
    }

    // -----------------------------------

    private function updateSkusByReceivedItems(Ess_M2ePro_Model_Account $account, $listingOtherCollection, array $items)
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

    private function receiveSkusFromEbay(Ess_M2ePro_Model_Account $account, $sinceTime)
    {
        $sinceTime = new DateTime($sinceTime,new DateTimeZone('UTC'));
        $sinceTime->modify('-1 minute');
        $sinceTime = $sinceTime->format('Y-m-d H:i:s');

        $inputData = array(
            'since_time'    => $sinceTime,
            'only_one_page' => true
        );

        $responseData = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                            ->processVirtual('item','get','all',
                                             $inputData,NULL,
                                             NULL,$account->getId(),NULL);

        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return array();
        }

        return (array)$responseData['items'];
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