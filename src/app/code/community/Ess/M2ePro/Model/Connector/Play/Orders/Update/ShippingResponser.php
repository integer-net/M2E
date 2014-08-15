<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Orders_Update_ShippingResponser
    extends Ess_M2ePro_Model_Connector_Play_Responser
{
    // M2ePro_TRANSLATIONS
    // Play.com Order status was not updated. Reason: %msg%
    // M2ePro_TRANSLATIONS
    // Play.com Order status was updated to Shipped.
    // M2ePro_TRANSLATIONS
    // Tracking number "%num%" for "%code%" has been sent to Play.com.

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        if (!$fail) {
            return;
        }

        $logs = array();
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
            'Play.com Order status was not updated. Reason: %msg%', array('msg' => $message)
        );

        foreach (array_keys($this->params) as $orderId) {
            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => $logMessage,
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
                'create_date'    => $currentDate
            );
        }

        $this->createLogEntries($logs);
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        $logs = array();
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        // Check global messages
        //----------------------
        $globalMessages = $this->messages;
        if (isset($response['messages']['0-id']) && is_array($response['messages']['0-id'])) {
            $globalMessages = array_merge($globalMessages,$response['messages']['0-id']);
        }

        if (count($globalMessages) > 0) {
            foreach ($this->getOrdersIds() as $orderId) {
                foreach ($globalMessages as $message) {
                    $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                    $logs[] = array(
                        'order_id'       => $orderId,
                        'message'        => $text,
                        'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
                        'create_date'    => $currentDate
                    );
                }
            }

            $this->createLogEntries($logs);

            return;
        }
        //----------------------

        // Check separate messages
        //----------------------
        $failedOrdersIds = array();

        foreach ($response['messages'] as $orderItemId => $messages) {
            $orderItemId = (int)$orderItemId;

            if ($orderItemId <= 0) {
                continue;
            }

            $orderId = $this->getOrderIdByOrderItemId($orderItemId);

            if (!is_numeric($orderId)) {
                continue;
            }

            $failedOrdersIds[] = $orderId;

            foreach ($messages as $message) {
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                $logs[] = array(
                    'order_id'       => $orderId,
                    'message'        => $text,
                    'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
                    'create_date'    => $currentDate
                );
            }
        }
        //----------------------

        //----------------------
        foreach ($this->getOrdersIds() as $orderId) {

            if (in_array($orderId, $failedOrdersIds)) {
                continue;
            }

            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => 'Play.com Order status was updated to Shipped.',
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
                'create_date'    => $currentDate
            );

            $logMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'Tracking number "%num%" for "%code%" has been sent to Play.com.', array(
                    '!num' => $this->params[$orderId]['tracking_number'],
                    'code' => $this->params[$orderId]['tracking_type']
                )
            );

            $logs[] = array(
                'order_id'       => (int)$orderId,
                'message'        => $logMessage,
                'type'           => Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                'component_mode' => Ess_M2ePro_Helper_Component_Play::NICK,
                'create_date'    => $currentDate
            );
        }
        //----------------------

        $this->createLogEntries($logs);
    }

    // ########################################

    private function getOrderIdByOrderItemId($orderItemId)
    {
        foreach ($this->params as $requestOrderItemId => $requestData) {
            if ($orderItemId == $requestOrderItemId) {
                return $requestData['order_id'];
            }
        }

        return null;
    }

    private function getOrdersIds()
    {
        $ids = array();

        foreach ($this->params as $requestData) {
            $ids[] = (int)$requestData['order_id'];
        }

        return array_unique($ids);
    }

    // ########################################

    private function createLogEntries(array $data)
    {
        if (count($data) == 0) {
            throw new InvalidArgumentException('Number of log entries cannot be zero.');
        }

        /** @var $writeConnection Varien_Db_Adapter_Interface */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $writeConnection->insertMultiple(Mage::getResourceModel('M2ePro/Order_Log')->getMainTable(), $data);
    }

    // ########################################
}