<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Order_Update_Payment
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Order_Update_Abstract
{
    // ->__('Payment status for eBay order was not updated. Reason: eBay Failure.');
    // ->__('Payment status for eBay order was updated to Paid.');

    // ########################################

    protected function prepareResponseData($response)
    {
        if ($this->resultType != parent::MESSAGE_TYPE_ERROR) {

            if (!isset($response['result']) || !$response['result']) {
                $this->order->addErrorLog(
                    'Payment status for eBay order was not updated. Reason: eBay Failure.'
                );
                return false;
            }

//            $this->order->setData('payment_status', Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)->save();
            $this->order->addSuccessLog('Payment status for eBay order was updated to Paid.');
        }

        return $response;
    }

    // ########################################
}