<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts
    extends Ess_M2ePro_Model_Buy_Synchronization_Defaults_Abstract
{
    const LOCK_ITEM_PREFIX = 'synchronization_buy_default_update_listings_products';

    //####################################

    protected function getNick()
    {
        return '/update_listings_products/';
    }

    protected function getTitle()
    {
        return 'Update Listings Products';
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

        return parent::intervalIsLocked();
    }

    protected function intervalGetLastTime()
    {
        $currentLastTime = parent::intervalGetLastTime();

        if (empty($currentLastTime)) {
            return null;
        }

        if (!in_array(Ess_M2ePro_Model_Synchronization_Task_Abstract::OTHER_LISTINGS, $this->getAllowedTasksTypes())) {
            return $currentLastTime;
        }

        $otherListingsLastTime = $this->getConfigValue('/buy/other_listings/', 'last_time');

        if (empty($otherListingsLastTime)) {
            return null;
        }

        if (strtotime($otherListingsLastTime) < strtotime($currentLastTime)) {
            return $otherListingsLastTime;
        }

        return $currentLastTime;
    }

    //####################################

    protected function performActions()
    {
        $accounts = Mage::helper('M2ePro/Component_Buy')->getCollection('Account')->getItems();

        if (count($accounts) <= 0) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($accounts);

        /** @var $account Ess_M2ePro_Model_Account **/
        foreach ($accounts as $account) {

            $this->getActualOperationHistory()->addText('Starting Account "'.$account->getTitle().'"');
        // M2ePro_TRANSLATIONS
        // The "Update Listings Products" Action for Rakuten.com Account: "%account_title%" is started. Please wait...
            $status = 'The "Update Listings Products" Action for Rakuten.com Account: "%account_title%" is started.';
            $status .= ' Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));

            if (!$this->isLockedAccount($account)) {

                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process Account '.$account->getTitle()
                );

                /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
                $collection = Mage::getModel('M2ePro/Listing')->getCollection();
                $collection->addFieldToFilter('component_mode',Ess_M2ePro_Helper_Component_Buy::NICK);
                $collection->addFieldToFilter('account_id',(int)$account->getId());

                if ($collection->getSize()) {

                    $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
                    $connectorObj = $dispatcherObject->getConnector('defaults', 'updateListingsProducts' ,'requester',
                                                                    array(), $account,
                                                                    'Ess_M2ePro_Model_Buy_Synchronization');
                    $dispatcherObject->process($connectorObj);
                }

                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
            }

        // M2ePro_TRANSLATIONS
        // The "Update Listings Products" Action for Rakuten.com Account: "%account_title%" is finished. Please wait...
            $status = 'The "Update Listings Products" Action for Rakuten.com Account: "%account_title%" is finished.'.
                ' Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();

            $iteration++;
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