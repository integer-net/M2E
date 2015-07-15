<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

final class Ess_M2ePro_Model_Buy_Synchronization_OtherListings
    extends Ess_M2ePro_Model_Buy_Synchronization_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_buy_other_listings';

    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::OTHER_LISTINGS;
    }

    protected function getNick()
    {
        return NULL;
    }

    protected function getTitle()
    {
        return '3rd Party Listings';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // -----------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    protected function intervalIsLocked()
    {
        if ($this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_USER ||
            $this->getInitiator() == Ess_M2ePro_Helper_Data::INITIATOR_DEVELOPER) {
            return false;
        }

        if (!in_array(Ess_M2ePro_Model_Synchronization_Task_Abstract::DEFAULTS, $this->getAllowedTasksTypes())) {
            return parent::intervalIsLocked();
        }

        $synchronizationStartTime = $this->getParentOperationHistory()->getObject()->getData('start_date');
        $updateListingsProductsLastTime = $this->getConfigValue(
            '/buy/defaults/update_listings_products/', 'last_time'
        );

        return strtotime($synchronizationStartTime) > strtotime($updateListingsProductsLastTime);
    }

    //####################################

    protected function performActions()
    {
        $this->executeUpdateInventory();
        $this->executeUpdateInventoryTitle();
    }

    //####################################

    private function executeUpdateInventory()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval() / 2) / count($accounts);

        /** @var $account Ess_M2ePro_Model_Account **/
        foreach ($accounts as $account) {

            $this->processAccountInventory($account);

            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    private function executeUpdateInventoryTitle()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Buy_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accounts = $accountsCollection->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = ($this->getPercentsInterval() / 2) / count($accounts);

        /** @var $account Ess_M2ePro_Model_Account **/
        foreach ($accounts as $account) {

            $this->processAccountInventoryTitle($account);

            $offset = $this->getPercentsInterval() / 2 + $iteration * $percentsForOneStep;
            $this->getActualLockItem()->setPercents($offset);
            $this->getActualLockItem()->activate();

            $iteration++;
        }
    }

    //####################################

    private function processAccountInventory(Ess_M2ePro_Model_Account $account)
    {
        $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
        // M2ePro_TRANSLATIONS
        // The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is started. Please wait...
        $status = 'The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is started.';
        $status .= ' Please wait...';
        $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

        if (!$this->isLockedAccount($account)) {

            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'process'.$account->getId(),
                'Process Account '.$account->getTitle()
            );

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
            $connectorObj = $dispatcherObject->getConnector('synchronization', 'otherListings' ,'requester',
                                                            array(), $account, 'Ess_M2ePro_Model_Buy');

            $dispatcherObject->process($connectorObj);

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
        }

        // M2ePro_TRANSLATIONS
        // The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...
        $status = 'The "3rd Party Listings" Action for Rakuten.com Account: "%account_title%" is finished.';
        $status .= ' Please wait...';
        $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
    }

    private function processAccountInventoryTitle(Ess_M2ePro_Model_Account $account)
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$account->getId(),
                                                         'Get and process Titles for Account '.$account->getTitle());

        if (!$this->isPossibleProcessAccountTitles($account)) {
            return;
        }

        $this->isNeedUpdateTitlesByPages($account) ? $this->updateTitlesByPages($account) :
                                                     $this->updateTitlesBySkus($account);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());
    }

    //------------------------------------

    private function isPossibleProcessAccountTitles(Ess_M2ePro_Model_Account $account)
    {
        $firstSynchronizationTime = $account->getChildObject()->getOtherListingsFirstSynchronization();

        if (is_null($firstSynchronizationTime)) {
            return false;
        }

        if (strtotime($firstSynchronizationTime) + 3*3600 > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return false;
        }

        return true;
    }

    //####################################

    private function updateTitlesByPages(Ess_M2ePro_Model_Account $account)
    {
        $inputData = array(
            'necessary_status' => 0,
            'necessary_page' => 1
        );

        $settings = $account->getChildObject()->getDecodedOtherListingsUpdateTitlesSettings();

        if (!is_null($settings)) {
            $inputData['necessary_status'] = (int)$settings['next_status'];
            $inputData['necessary_page'] = (int)$settings['next_page'];
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('inventory','get','pagesTitles',
                                                               $inputData,NULL,
                                                               $account->getId());

        $responseData = $dispatcherObject->process($connectorObj);

        $this->updateReceivedTitles($responseData, $account);
        $pagesSettings = $this->calculateNextPagesSettings($responseData, $inputData);

        $account->getChildObject()
            ->setData('other_listings_update_titles_settings',$pagesSettings)
            ->save();
    }

    private function isNeedUpdateTitlesByPages(Ess_M2ePro_Model_Account $account)
    {
        $settings = $account->getChildObject()->getDecodedOtherListingsUpdateTitlesSettings();

        if (is_null($settings)) {
            return true;
        }

        if ((int)$settings['next_status'] <= 2 && (int)$settings['next_page'] < 10000) {
            return true;
        }

        return false;
    }

    private function calculateNextPagesSettings($responseData, $inputData)
    {
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

        return json_encode(array(
            'next_status' => $nextStatus,
            'next_page'   => $nextPage
        ));
    }

    //####################################

    private function updateTitlesBySkus(Ess_M2ePro_Model_Account $account)
    {
        /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');
        $listingOtherCollection->addFieldToFilter('`main_table`.account_id',(int)$account->getId());
        $listingOtherCollection->getSelect()->where('`second_table`.`title` IS NULL');
        $listingOtherCollection->getSelect()->limit(10);

        if (!$listingOtherCollection->getSize()) {
            return;
        }

        $neededItems = array();
        foreach ($listingOtherCollection->getItems() as $tempItem) {
            $neededItems[] = $tempItem->getData('general_id');
        }

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('inventory','get','skusTitles',
                                                               array('items'=>$neededItems),NULL,
                                                               $account->getId());

        $responseData = $dispatcherObject->process($connectorObj);
        $this->updateReceivedTitles($responseData, $account);
    }

    //------------------------------------

    private function updateReceivedTitles(array $responseData, Ess_M2ePro_Model_Account $account)
    {
        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $bloTable = Mage::getResourceModel('M2ePro/Buy_Listing_Other')->getMainTable();
        $lolTable = Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable();

        /** @var $mappingModel Ess_M2ePro_Model_Buy_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Mapping');

        /** @var $movingModel Ess_M2ePro_Model_Buy_Listing_Other_Moving */
        $movingModel = Mage::getModel('M2ePro/Buy_Listing_Other_Moving');

        $receivedItems = array();
        foreach ($responseData['items'] as $generalId => $title) {

            if (isset($receivedItems[$generalId])) {
                continue;
            }

            $receivedItems[$generalId] = $title;

            $listingsOthersWithEmptyTitles = array();
            if ($account->getChildObject()->isOtherListingsMappingEnabled()) {

                /** @var $listingOtherCollection Mage_Core_Model_Mysql4_Collection_Abstract */
                $listingOtherCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other')
                        ->addFieldToFilter('`main_table`.account_id',(int)$account->getId())
                        ->addFieldToFilter('`second_table`.`general_id`',(int)$generalId)
                        ->addFieldToFilter('`second_table`.`title`',array('null' => true));

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
                    $listingOtherModel->getChildObject()->setData('title',(string)$title);

                    $mappingModel->initialize($account);
                    $mappingResult = $mappingModel->autoMapOtherListingProduct($listingOtherModel);

                    if ($mappingResult) {

                        if (!$account->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
                            continue;
                        }

                        $movingModel->initialize($account);
                        $movingModel->autoMoveOtherListingProduct($listingOtherModel);
                    }
                }
            }
        }
    }

    //####################################

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