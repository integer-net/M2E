<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Revise_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            Mage::getModel('M2ePro/Connector_Amazon_Product_Helper')
                        ->updateAfterReviseAction($listingProduct,
                                                  $this->getListingProductRequestNativeData($listingProduct),
                                                  $this->params);

            // M2ePro_TRANSLATIONS
            // Item was successfully revised
            $this->addListingsProductsLogsMessage($listingProduct, $this->getSuccessfullyMessage(),
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    protected function getSuccessfullyMessage()
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully revised
        $defaultMessage = 'Item was successfully revised';

        if (isset($this->params['params']['all_data']) || !isset($this->params['params']['only_data'])) {
            return $defaultMessage;
        }

        $tempOnlyString = '';

        if (isset($this->params['params']['only_data']['qty'])) {

            // M2ePro_TRANSLATIONS
            // qty
            $tempStr = 'qty';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if (isset($this->params['params']['only_data']['price'])) {

            // M2ePro_TRANSLATIONS
            // price
            $tempStr = 'price';
            $tempOnlyString == '' && $tempStr = ucwords($tempStr);
            $tempOnlyString != '' && $tempOnlyString .= ', ';
            $tempOnlyString .= $tempStr;
        }

        if ($tempOnlyString != '') {
            // M2ePro_TRANSLATIONS
            // was successfully revised
            return $tempOnlyString.' was successfully revised';
        }

        return $defaultMessage;
    }

    // ########################################
}