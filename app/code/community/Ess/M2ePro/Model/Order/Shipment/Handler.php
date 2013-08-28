<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Shipment_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $trackingDetails = $this->getTrackingDetails($shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        return $order->getChildObject()->updateShippingStatus($trackingDetails)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    protected function getTrackingDetails(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $track = $shipment->getTracksCollection()->getLastItem();
        $trackingDetails = array();

        if ($track->getData('number') != '') {
            $carrierCode = trim($track->getData('carrier_code'));

            if (strtolower($carrierCode) == 'dhlint') {
                $carrierCode = 'dhl';
            }

            $trackingDetails = array(
                'carrier_title'   => trim($track->getData('title')),
                'carrier_code'    => $carrierCode,
                'tracking_number' => (string)$track->getData('number')
            );
        }

        return $trackingDetails;
    }
}