<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Order_Update_Shipping
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Order_Update_Abstract
{
    // ->__('Shipping status for eBay order was not updated. Reason: eBay Failure.');
    // ->__('Tracking number "%num%" for "%code%" has been sent to eBay.');
    // ->__('Shipping status for eBay order was updated to Shipped.');

    // ########################################

    private $carrierCode = NULL;
    private $trackingNumber = NULL;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Order $order, $action)
    {
        parent::__construct($params, $order, $action);

        if ($this->action == Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $this->carrierCode = $params['carrier_code'];
            $this->trackingNumber = $params['tracking_number'];
        }
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = parent::getRequestData();

        if ($this->action == Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $requestData['carrier_code'] = $this->carrierCode;
            $requestData['tracking_number'] = $this->trackingNumber;
        }

        return $requestData;
    }

    // ########################################

    protected function prepareResponseData($response)
    {
        if ($this->resultType != parent::MESSAGE_TYPE_ERROR) {

            if (!isset($response['result']) || !$response['result']) {
                $this->order->addErrorLog(
                    'Shipping status for eBay order was not updated. Reason: eBay Failure.'
                );

                return false;
            }

            if ($this->action == Ess_M2ePro_Model_Connector_Server_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
                $trackingDetails = $this->order->getChildObject()->getShippingTrackingDetails();
                $trackingDetails[] = array(
                    'title'  => $this->carrierCode,
                    'number' => $this->trackingNumber
                );

                $this->order->setData('shipping_tracking_details', json_encode($trackingDetails))->save();
                $this->order->addSuccessLog(
                    'Tracking number "%num%" for "%code%" has been sent to eBay.', array(
                        '!num'  => $this->trackingNumber,
                        '!code' => $this->carrierCode
                    )
                );
            }

            if (!$this->order->getChildObject()->isShippingCompleted()) {
//             $this->order->setData('shipping_status',Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED)->save();
                $this->order->addSuccessLog(
                    'Shipping status for eBay order was updated to Shipped.'
                );
            }

        }

        return $response;
    }

    // ########################################
}