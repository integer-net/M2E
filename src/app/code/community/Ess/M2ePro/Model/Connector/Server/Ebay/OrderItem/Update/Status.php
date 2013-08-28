<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_OrderItem_Update_Status
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Abstract
{
    // ->__('Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%')
    // ->__('Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: eBay Failure.')
    // ->__('Tracking number "%num%" for "%code%" has been sent to eBay (Item: %item_id%, Transaction: %trn_id%).')
    // ->__('Order item has been marked as shipped (Item: %item_id%, Transaction: %trn_id%).')

    /** @var $orderItem Ess_M2ePro_Model_Order_Item */
    private $orderItem;

    public function __construct(array $params = array(), Ess_M2ePro_Model_Order_Item $orderItem)
    {
        parent::__construct($params, null, $orderItem->getOrder()->getAccount(), null);

        $this->orderItem = $orderItem;
    }

    protected function getCommand()
    {
        return array('sales', 'update', 'status');
    }

    protected function validateNeedRequestSend()
    {
        return true;
    }

    protected function getRequestData()
    {
        $action = Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP;
        if (!empty($this->params['tracking_number']) && !empty($this->params['carrier_code'])) {
            $action = Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK;
        }

        $trackingNumber = !empty($this->params['tracking_number']) ? $this->params['tracking_number'] : null;
        $carrierCode = !empty($this->params['carrier_code']) ? $this->params['carrier_code'] : null;

        return array(
            'account'         => $this->orderItem->getOrder()->getAccount()->getServerHash(),
            'action'          => $action,
            'item_id'         => $this->orderItem->getChildObject()->getItemId(),
            'transaction_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            'tracking_number' => $trackingNumber,
            'carrier_code'    => $carrierCode
        );
    }

    protected function validateResponseData($response)
    {
        return true;
    }

    public function process()
    {
        if (!$this->validateNeedRequestSend()) {
            return false;
        }

        $result = parent::process();

        foreach ($this->messages as $message) {
            if ($message[parent::MESSAGE_TYPE_KEY] != parent::MESSAGE_TYPE_ERROR) {
                continue;
            }

            $message = 'Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%';
            $this->orderItem->getOrder()->addErrorLog($message, array(
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
                'msg'      => $message[parent::MESSAGE_TEXT_KEY]
            ));
        }

        return $result;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return false;
        }

        if (!isset($response['result']) || !$response['result']) {
            $message = 'Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). '.
                       'Reason: eBay Failure.';
            $this->orderItem->getOrder()->addErrorLog($message, array(
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            ));

            return false;
        }

        if (!empty($this->params['tracking_number']) && !empty($this->params['carrier_code'])) {
            $message = 'Tracking number "%num%" for "%code%" has been sent to eBay '.
                       '(Item: %item_id%, Transaction: %trn_id%).';
            $this->orderItem->getOrder()->addSuccessLog($message, array(
                '!num' => $this->params['tracking_number'],
                'code' => $this->params['carrier_code'],
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            ));
        } else {
            $message = 'Order item has been marked as shipped (Item: %item_id%, Transaction: %trn_id%).';
            $this->orderItem->getOrder()->addSuccessLog($message, array(
                '!item_id' => $this->orderItem->getChildObject()->getItemId(),
                '!trn_id'  => $this->orderItem->getChildObject()->getTransactionId(),
            ));
        }

        return true;
    }
}