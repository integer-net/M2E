<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Product_Revise_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Server_Buy_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $requestData = $this->getListingProductRequestNativeData($listingProduct);

            $tempParams = array(
                'status_changer' => $this->getStatusChanger()
            );

            Mage::getModel('M2ePro/Buy_Connector_Product_Helper')
                        ->updateAfterReviseAction($listingProduct,$requestData,$tempParams);

            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
            $this->addListingsProductsLogsMessage($listingProduct, $this->getSuccessfullyMessage($requestData),
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    protected function getSuccessfullyMessage($nativeRequestData)
    {
        // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
        $defaultMessage = 'Item was successfully revised';

        if (isset($this->params['params']['all_data']) || !isset($this->params['params']['only_data'])) {
            return $defaultMessage;
        }

        $tempOnlyString = '';

        if (isset($this->params['params']['only_data']['qty'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('qty');
            $tempStr = 'qty';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['params']['only_data']['price'])) {

            // Parser hack -> Mage::helper('M2ePro')->__('price');
            $tempStr = 'price';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if ($tempOnlyString != '') {
            // Parser hack -> Mage::helper('M2ePro')->__('was successfully revised');
            return $tempOnlyString.' was successfully revised';
        }

        return $defaultMessage;
    }

    // ########################################
}