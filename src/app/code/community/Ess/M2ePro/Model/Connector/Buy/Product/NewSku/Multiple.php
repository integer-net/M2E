<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_NewSku_Multiple
    extends Ess_M2ePro_Model_Connector_Buy_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','add','newSku');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'new_sku';
    }

    protected function getResponserModel()
    {
        return 'Buy_Product_NewSku_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isNotListed()) {

                // M2ePro_TRANSLATIONS
                // Item is already on Rakuten.com, or not available.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'Item is already on Rakuten.com, or not available.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            /** @var Ess_M2ePro_Model_Buy_Listing_Product $buyListingProduct */
            $buyListingProduct = $listingProduct->getChildObject();

            if ($buyListingProduct->isVariationProduct() && !$buyListingProduct->isVariationMatched()) {

                // M2ePro_TRANSLATIONS
                // You have to select variation.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'You have to select variation.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            $templateNewProductId = $buyListingProduct->getTemplateNewProductId();

            if (empty($templateNewProductId)) {
                // M2ePro_TRANSLATIONS
                // New SKU template is required.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'New SKU template is required.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }
        }
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = array();

        $requestData['items'] = array();

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $nativeData = Mage::getModel('M2ePro/Connector_Buy_Product_Helper')
                                         ->getNewSkuRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        return $requestData;
    }

    // ########################################
}