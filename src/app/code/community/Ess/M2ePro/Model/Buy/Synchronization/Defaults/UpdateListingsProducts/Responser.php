<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts_Responser
    extends Ess_M2ePro_Model_Connector_Buy_Inventory_Get_ItemsResponser
{
    protected $logsActionId = NULL;
    protected $synchronizationLog = NULL;

    // ########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages($messages);

        foreach ($this->messages as $message) {

            if (!$this->isMessageError($message) && !$this->isMessageWarning($message)) {
                continue;
            }

            $logType = $this->isMessageError($message) ? Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                                                       : Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY]),
                $logType,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    protected function isNeedToParseResponseData($responseBody)
    {
        if (!parent::isNeedToParseResponseData($responseBody)) {
            return false;
        }

        if ($this->hasErrorMessages()) {
            return false;
        }

        return true;
    }

    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_buy', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,
            $processingRequest->getHash()
        );
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    // ########################################

    protected function processResponseData($response)
    {
        try {

            $this->updateReceivedListingsProducts($response['data']);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    // ########################################

    protected function updateReceivedListingsProducts($receivedItems)
    {
        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $this->getPdoStatementExistingListings(true);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);

        while ($existingItem = $stmtTemp->fetch()) {

            if (!isset($receivedItems[$existingItem['sku']])) {
                continue;
            }

            $receivedItem = $receivedItems[$existingItem['sku']];

            $newData = array(
                'general_id'              => (int)$receivedItem['general_id'],
                'online_price'            => (float)$receivedItem['price'],
                'online_qty'              => (int)$receivedItem['qty'],
                'condition'               => (int)$receivedItem['condition'],
                'condition_note'          => (string)$receivedItem['condition_note'],
                'shipping_standard_rate'  => (float)$receivedItem['shipping_standard_rate'],
                'shipping_expedited_mode' => (int)$receivedItem['shipping_expedited_mode'],
                'shipping_expedited_rate' => (float)$receivedItem['shipping_expedited_rate']
            );

            if ($newData['online_qty'] > 0) {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
            } else {
                $newData['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }

            $existingData = array(
                'general_id'              => (int)$existingItem['general_id'],
                'online_price'            => (float)$existingItem['online_price'],
                'online_qty'              => (int)$existingItem['online_qty'],
                'condition'               => (int)$existingItem['condition'],
                'condition_note'          => (string)$existingItem['condition_note'],
                'shipping_standard_rate'  => (float)$existingItem['shipping_standard_rate'],
                'shipping_expedited_mode' => (int)$existingItem['shipping_expedited_mode'],
                'shipping_expedited_rate' => (float)$existingItem['shipping_expedited_rate'],
                'status'                  => (int)$existingItem['status']
            );

            $existingAdditionalData = @json_decode($existingItem['additional_data'], true);

            if (!empty($existingAdditionalData['last_synchronization_dates']['selling']) &&
                !empty($this->params['request_date'])
            ) {
                $lastSellingSynchDate = $existingAdditionalData['last_synchronization_dates']['selling'];

                if (strtotime($lastSellingSynchDate) > strtotime($this->params['request_date'])) {
                    unset($newData['online_qty'], $newData['online_price'], $newData['status']);
                    unset($existingData['online_qty'], $existingData['online_price'], $existingData['status']);
                }
            }

            if ($newData == $existingData) {
                continue;
            }

            $newData['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_COMPONENT;

            if ((isset($newData['status']) && $newData['status'] != $existingItem['status']) ||
                (isset($newData['online_qty']) && $newData['online_qty'] != $existingItem['online_qty']) ||
                (isset($newData['online_price']) && $newData['online_price'] != $existingItem['online_price'])
            ) {
                Mage::getModel('M2ePro/ProductChange')->addUpdateAction(
                    $existingItem['product_id'], Ess_M2ePro_Model_ProductChange::INITIATOR_SYNCHRONIZATION
                );
            }

            if (isset($newData['status']) && $newData['status'] != $existingItem['status']) {

                $tempLogMessage = '';
                switch ($newData['status']) {
                    case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                        // M2ePro_TRANSLATIONS
                        // Item status was successfully changed to "Active".
                        $tempLogMessage = 'Item status was successfully changed to "Active".';
                        break;
                    case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                        // M2ePro_TRANSLATIONS
                        // Item status was successfully changed to "Inactive".
                        $tempLogMessage = 'Item status was successfully changed to "Inactive".';
                        break;
                }

                $tempLog->addProductMessage(
                    $existingItem['listing_id'],
                    $existingItem['product_id'],
                    $existingItem['listing_product_id'],
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $this->getLogsActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_STATUS_ON_CHANNEL,
                    $tempLogMessage,
                    Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                );
            }

            $listingProductObj = Mage::helper('M2ePro/Component_Buy')
                                    ->getObject('Listing_Product',(int)$existingItem['listing_product_id']);

            $listingProductObj->addData($newData)->save();
        }
    }

    // ########################################

    protected function getPdoStatementExistingListings($withData = false)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $collection->getSelect()->join(array('l' => $listingTable), 'main_table.listing_id = l.id', array());
        $collection->getSelect()->where('l.account_id = ?',(int)$this->getAccount()->getId());
        $collection->getSelect()->where('`main_table`.`status` != ?',
                                        (int)Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->where("`second_table`.`sku` is not null and `second_table`.`sku` != ''");

        $dbSelect = $collection->getSelect();

        $tempColumns = array('second_table.sku');

        if ($withData) {
            $tempColumns = array('main_table.listing_id',
                                 'main_table.product_id','main_table.status',
                                 'main_table.additional_data',
                                 'second_table.sku','second_table.general_id',
                                 'second_table.online_price','second_table.online_qty',
                                 'second_table.condition','second_table.condition_note',
                                 'second_table.shipping_standard_rate',
                                 'second_table.shipping_expedited_mode',
                                 'second_table.shipping_expedited_rate',
                                 'second_table.listing_product_id');
        }

        $dbSelect->reset(Zend_Db_Select::COLUMNS)->columns($tempColumns);

        /** @var $stmtTemp Zend_Db_Statement_Pdo */
        $stmtTemp = $connRead->query($dbSelect->__toString());

        return $stmtTemp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    //-----------------------------------------

    protected function getLogsActionId()
    {
        if (!is_null($this->logsActionId)) {
            return $this->logsActionId;
        }

        return $this->logsActionId = Mage::getModel('M2ePro/Listing_Log')->getNextActionId();
    }

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        /** @var $logs Ess_M2ePro_Model_Synchronization_Log */
        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS);

        return $this->synchronizationLog;
    }

    // ########################################
}