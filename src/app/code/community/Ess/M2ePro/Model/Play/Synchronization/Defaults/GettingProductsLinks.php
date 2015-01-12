<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

final class Ess_M2ePro_Model_Play_Synchronization_Defaults_GettingProductsLinks
    extends Ess_M2ePro_Model_Play_Synchronization_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/getting_products_links/';
    }

    protected function getTitle()
    {
        return 'Getting Products Links';
    }

    //-----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function performActions()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Account');
        $accountsTotalCount = (int)$accountsCollection->getSize();

        if ($accountsTotalCount <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneIteration = $this->getPercentsInterval() / $accountsTotalCount;

        /** @var $account Ess_M2ePro_Model_Account */
        foreach ($accountsCollection->getItems() as $account) {

            $this->processAccount($account);

            $percentsShift = ++$iteration * $percentsForOneIteration;
            $this->getActualLockItem()->setPercents($this->getPercentsInterval() + $percentsShift);
            $this->getActualLockItem()->activate();
        }
    }

    //####################################

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $this->getActualOperationHistory()->addTimePoint(
            __METHOD__.'get'.$account->getId(),'Get and process links on products for Account '.$account->getTitle()
        );

        if (!$this->isPossibleProcessAccount($account)) {
            return;
        }

        $this->isNeedGettingLinksByPages($account) ? $this->updateLinksByPages($account) :
                                                     $this->updateLinksByListingsIds($account);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());
    }

    private function isPossibleProcessAccount(Ess_M2ePro_Model_Account $account)
    {
        if (!$this->getConfigValue('/play/other_listings/','mode')) {
            return true;
        }

        $currentGmtDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        if (strtotime($account->getData('create_date')) + 24*3600 < $currentGmtDate) {
            return true;
        }

        $firstOtherListingsSyncTime = $account->getChildObject()->getOtherListingsFirstSynchronization();

        if (!is_null($firstOtherListingsSyncTime) &&
            strtotime($firstOtherListingsSyncTime) + 3*3600 < $currentGmtDate) {

            return true;
        }

        return false;
    }

    //------------------------------------

    private function isNeedGettingLinksByPages(Ess_M2ePro_Model_Account $account)
    {
        $settings = $account->getChildObject()->getDecodedListingsUpdateLinksSettings();

        if (is_null($settings)) {
            return true;
        }

        if ((int)$settings['next_status'] <= 3 && (int)$settings['next_page'] < 10000) {
            return true;
        }

        return false;
    }

    //####################################

    private function updateLinksByPages(Ess_M2ePro_Model_Account $account)
    {
        $inputData = array(
            'necessary_status' => 2,
            'necessary_page' => 1
        );

        $settings = $account->getChildObject()->getDecodedListingsUpdateLinksSettings();

        if (!is_null($settings)) {
            $inputData['necessary_status'] = (int)$settings['next_status'];
            $inputData['necessary_page'] = (int)$settings['next_page'];
        }

        $responseData = Mage::getModel('M2ePro/Connector_Play_Dispatcher')
                                ->processVirtual('inventory','get','pagesProductsLinks',
                                                 $inputData,NULL,$account->getId());

        $this->updateReceivedLinks($responseData);
        $pagesSettings = $this->calculateNextPagesSettings($responseData, $inputData);

        $account->getChildObject()
            ->setData('listings_update_links_settings', $pagesSettings)
            ->save();
    }

    private function updateLinksByListingsIds(Ess_M2ePro_Model_Account $account)
    {
        $listingsForUpdate = $this->getListingsProductForUpdate($account);
        $listingsForUpdate->getSize() <= 0 && $listingsForUpdate = $this->getListingsOtherForUpdate($account);

        if ($listingsForUpdate->getSize() <= 0) {
            return;
        }

        $neededItems = array();
        foreach ($listingsForUpdate->getItems() as $item) {
            $neededItems[] = $item->getData('play_listing_id');
        }

        $responseData = Mage::getModel('M2ePro/Connector_Play_Dispatcher')
                                ->processVirtual('inventory','get','listingsIdsProductsLinks',
                                                 array('items'=>$neededItems),NULL,
                                                 $account->getId());

        $this->updateReceivedLinks($responseData);
    }

    //------------------------------------

    private function updateReceivedLinks(array $responseData)
    {
        if (!isset($responseData['items']) || !is_array($responseData['items'])) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingOtherTable = Mage::getResourceModel('M2ePro/Play_Listing_Other')->getMainTable();
        $listingTable = Mage::getResourceModel('M2ePro/Play_Listing_Product')->getMainTable();

        $receivedItems = array();
        foreach ($responseData['items'] as $playListingId => $receivedInfo) {

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

    private function calculateNextPagesSettings($responseData, $inputData)
    {
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

        return json_encode(array(
            'next_status' => $nextStatus,
            'next_page'   => $nextPage
        ));
    }

    //####################################

    private function getListingsProductForUpdate(Ess_M2ePro_Model_Account $accountObj)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $listingsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $listingsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $listingsCollection->getSelect()->join(array('l' => $listingTable),
                                               'main_table.listing_id = l.id',
                                               array());
        $listingsCollection->getSelect()->where('l.account_id = ?',$accountObj->getId());
        $listingsCollection->getSelect()->where('`second_table`.`link_info` IS NULL');
        $listingsCollection->getSelect()->where('`second_table`.`play_listing_id` IS NOT NULL');
        $listingsCollection->getSelect()->limit(5);

        return $listingsCollection;
    }

    private function getListingsOtherForUpdate(Ess_M2ePro_Model_Account $accountObj)
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