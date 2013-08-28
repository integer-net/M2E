<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
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

        $item = $this->getItemToShip($order, $shipment);

        if (!is_null($item) && !is_null($item->getId())) {
            return $item->getChildObject()->updateShippingStatus($trackingDetails)
                ? self::HANDLE_RESULT_SUCCEEDED
                : self::HANDLE_RESULT_FAILED;
        }

        return $order->getChildObject()->updateShippingStatus($trackingDetails)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
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

        return Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order_Item')
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('item_id', $itemId)
                ->addFieldToFilter('transaction_id', $transactionId)
                ->getFirstItem();
    }
}