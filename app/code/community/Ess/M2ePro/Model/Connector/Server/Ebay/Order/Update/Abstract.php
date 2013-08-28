<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Server_Ebay_Order_Update_Abstract
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Abstract
{
    // ->__('eBay Order status was not updated. Reason: %msg%');
    // ->__('Status of India site orders cannot be updated if the buyer uses PaisaPay payment method.');

    // ########################################

    /**
     * @var $order Ess_M2ePro_Model_Order
     */
    protected $order = NULL;
    protected $action = NULL;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Order $order, $action = NULL)
    {
        $this->order = $order;
        $this->action = $action;

        parent::__construct($params, NULL, $order->getAccount());
    }

    // ########################################

    protected function getCommand()
    {
        return array('sales', 'update', 'status');
    }

    // ########################################

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

            $this->order->addErrorLog(
                'eBay Order status was not updated. Reason: %msg%', array('msg' => $message[parent::MESSAGE_TEXT_KEY])
            );
        }

        return $result;
    }

    //----------------------------------------

    protected function validateNeedRequestSend()
    {
        if ($this->order->getMarketplace()->getCode() == 'India'
            && stripos($this->order->getChildObject()->getPaymentMethod(), 'paisa')
        ) {
            $this->order->addErrorLog('eBay Order status was not updated. Reason: %msg%', array(
                'msg' => 'Status of India site orders cannot be updated if the buyer uses PaisaPay payment method.'
            ));

            return false;
        }

        if (!in_array($this->action,array(
            Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_PAY,
            Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP,
            Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK
        ))) {
            throw new LogicException('Invalid action.');
        }

        return true;
    }

    protected function getRequestData()
    {
        $requestData = array('action' => $this->action);

        $ebayOrderId = $this->order->getData('ebay_order_id');

        if (strpos($ebayOrderId, '-') === false) {
            $requestData['order_id'] = $ebayOrderId;
        } else {
            $orderIdParts = explode('-', $ebayOrderId);

            $requestData['item_id'] = $orderIdParts[0];
            $requestData['transaction_id'] = $orderIdParts[1];
        }

        return $requestData;
    }

    // ########################################
}