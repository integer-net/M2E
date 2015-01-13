<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

final class Ess_M2ePro_Model_Play_Synchronization_Orders_Update
    extends Ess_M2ePro_Model_Play_Synchronization_Orders_Abstract
{
    // ##########################################################

    const LOCK_ITEM_PREFIX = 'synchronization_play_orders_update';

    // ##########################################################

    protected function getNick()
    {
        return '/update/';
    }

    protected function getTitle()
    {
        return 'Update';
    }

    // ##########################################################

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // ##########################################################

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

    // ##########################################################

    protected function performActions()
    {
        $this->deleteNotActualChanges();

        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 0;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {

            /** @var Ess_M2ePro_Model_Account $account */

            // ----------------------------------------------------------
            $this->getActualOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
            // M2ePro_TRANSLATIONS
            // The "Update" action for Play.com account: "%account_title%" is started. Please wait...
            $status = 'The "Update" action for Play.com account: "%account_title%" is started. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ----------------------------------------------------------

            if (!$this->isLockedAccount($account->getId())) {

                // ----------------------------------------------------------
                $this->getActualOperationHistory()->addTimePoint(
                    __METHOD__.'process'.$account->getId(),
                    'Process account '.$account->getTitle()
                );
                // ----------------------------------------------------------

                $this->processAccount($account);

                // ----------------------------------------------------------
                $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'process'.$account->getId());
                // ----------------------------------------------------------
            }

            // ----------------------------------------------------------
            // M2ePro_TRANSLATIONS
            // The "Update" action for Play.com account: "%account_title%" is finished. Please wait...
            $status = 'The "Update" action for Play.com account: "%account_title%" is finished. Please wait...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();
            // ----------------------------------------------------------

            $iteration++;
        }
    }

    // ##########################################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Play_Account::ORDERS_MODE_YES);

        return $accountsCollection->getItems();
    }

    // ----------------------------------------------------------

    private function isLockedAccount($accountId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId);

        return $lockItem->isExist();
    }

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $relatedChanges = $this->getRelatedChanges($account);
        if (empty($relatedChanges)) {
            return;
        }

        $items = array();
        $changesIds = array();

        foreach ($relatedChanges as $change) {
            $changeParams = $change->getParams();

            $items[] = array(
                'change_id'       => $change->getId(),
                'order_id'        => $change->getOrderId(),
                'play_order_id'   => $changeParams['play_order_id'],
                'carrier_name'    => $changeParams['carrier_name'],
                'tracking_number' => $changeParams['tracking_number'],
            );

            $changesIds[] = $change->getId();
        }

        if (empty($items)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount($changesIds);

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Play_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Play_Dispatcher');
        $dispatcherObject->processConnector(
            'orders', 'update', 'shipping', array('items' => $items), $account
        );
    }

    // ##########################################################

    private function getRelatedChanges(Ess_M2ePro_Model_Account $account)
    {
        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Play::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->getSelect()->group(array('order_id'));

        return $changesCollection->getItems();
    }

    // ----------------------------------------------------------

    private function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Play::NICK
            );
    }

    // ##########################################################
}
