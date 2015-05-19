<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        if (!$order->isComponentModePlay()) {
            throw new InvalidArgumentException('Invalid Component Mode.');
        }

        $trackingDetails = $this->getTrackingDetails($shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        $items = $this->getItemsToShip($order, $shipment);

        if (count($items) == 0) {
            return self::HANDLE_RESULT_FAILED;
        }

        foreach ($items as $item) {
            $item->getChildObject()->updateShippingStatus(
                $trackingDetails, Ess_M2ePro_Model_Order_Change::CREATOR_TYPE_OBSERVER
            );
        }

        return self::HANDLE_RESULT_SUCCEEDED;
    }

    /**
     * @param Ess_M2ePro_Model_Order          $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     *
     * @return Ess_M2ePro_Model_Order_Item[]
     */
    private function getItemsToShip(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $shipmentItems = $shipment->getAllItems();
        $orderItemDataIdentifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        $items = array();

        foreach ($shipmentItems as $shipmentItem) {
            $additionalData = $shipmentItem->getOrderItem()->getAdditionalData();
            $additionalData = is_string($additionalData) ? @unserialize($additionalData) : array();

            if (!isset($additionalData[$orderItemDataIdentifier]['items'][0]['order_item_id'])) {
                continue;
            }

            $id = $additionalData[$orderItemDataIdentifier]['items'][0]['order_item_id'];
            $item = $order->getItemsCollection()->getItemByColumnValue('play_order_item_id', $id);

            if (is_null($item)) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}