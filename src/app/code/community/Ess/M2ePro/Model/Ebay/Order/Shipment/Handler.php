<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        if (!$order->isComponentModeEbay()) {
            throw new InvalidArgumentException('Invalid component mode.');
        }

        $trackingDetails = $this->getTrackingDetails($shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        // collect change params
        //------------------------------
        $item   = $this->getItemToShip($order, $shipment);
        $params = array('tracking_details' => $trackingDetails, 'item_id' => null);

        if (!is_null($item)) {
            $params['item_id'] = $item->getId();
        }
        //------------------------------

        $this->createChange($order, $params);

        if (!is_null($item)) {
            $succeeded = $item->getChildObject()->updateShippingStatus($trackingDetails);
        } else {
            $succeeded = $order->getChildObject()->updateShippingStatus($trackingDetails);
        }

        return $succeeded ? self::HANDLE_RESULT_SUCCEEDED : self::HANDLE_RESULT_FAILED;
    }

    private function createChange(Ess_M2ePro_Model_Order $order, array $params)
    {
        // save change
        //------------------------------
        $orderId   = $order->getId();
        $action    = Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING;
        $creator   = Ess_M2ePro_Model_Order_Change::CREATOR_TYPE_OBSERVER;
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        Mage::getModel('M2ePro/Order_Change')->create($orderId, $action, $creator, $component, $params);
        //------------------------------
    }

    /**
     * @param Ess_M2ePro_Model_Order          $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return null|Ess_M2ePro_Model_Order_Item
     */
    private function getItemToShip(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        if ($order->isSingle()) {
            return null;
        }

        $shipmentItems = $shipment->getAllItems();

        if (count($shipmentItems) != 1) {
            return null;
        }

        /** @var $shipmentItem Mage_Sales_Model_Order_Shipment_Item */
        $shipmentItem = reset($shipmentItems);
        $additionalData = $shipmentItem->getOrderItem()->getAdditionalData();
        $additionalData = is_string($additionalData) ? @unserialize($additionalData) : array();

        $itemId = $transactionId = null;
        $orderItemDataIdentifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        if (isset($additionalData['ebay_item_id']) && isset($additionalData['ebay_transaction_id'])) {
            // backward compatibility with versions 5.0.4 or less
            $itemId = $additionalData['ebay_item_id'];
            $transactionId = $additionalData['ebay_transaction_id'];
        } elseif (isset($additionalData[$orderItemDataIdentifier]['items'])) {
            if (!is_array($additionalData[$orderItemDataIdentifier]['items'])
                || count($additionalData[$orderItemDataIdentifier]['items']) != 1
            ) {
                return null;
            }

            if (isset($additionalData[$orderItemDataIdentifier]['items'][0]['item_id'])) {
                $itemId = $additionalData[$orderItemDataIdentifier]['items'][0]['item_id'];
            }
            if (isset($additionalData[$orderItemDataIdentifier]['items'][0]['transaction_id'])) {
                $transactionId = $additionalData[$orderItemDataIdentifier]['items'][0]['transaction_id'];
            }
        }

        if (is_null($itemId) || is_null($transactionId)) {
            return null;
        }

        $item = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order_Item')
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('item_id', $itemId)
                ->addFieldToFilter('transaction_id', $transactionId)
                ->getFirstItem();

        return $item->getId() ? $item : null;
    }
}