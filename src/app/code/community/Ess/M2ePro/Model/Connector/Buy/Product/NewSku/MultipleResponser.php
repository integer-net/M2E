<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_NewSku_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Buy_Product_Responser
{
    private $responseBody = array();

    // ########################################

    protected function processResponseData($response)
    {
        $this->responseBody = $response;
        parent::processResponseData($response);
    }

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        if (count($listingsProducts) <= 0) {
            return;
        }

        $needListListingsProducts = array();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!isset($this->responseBody['skus'][$listingProduct->getId().'-id'])) {

                // M2ePro_TRANSLATIONS
                // New SKU was not added
                $this->addListingsProductsLogsMessage($listingProduct, 'New SKU was not added',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                continue;
            }

            Mage::getModel('M2ePro/Connector_Buy_Product_Helper')
            ->updateAfterNewSkuAction($listingProduct,
                                      $this->getListingProductRequestNativeData($listingProduct),
                                      array_merge($this->params,array(
                                          'general_id' => $this->responseBody['skus'][$listingProduct->getId().'-id']
                                      )));

            // M2ePro_TRANSLATIONS
            // New SKU was successfully added
            $this->addListingsProductsLogsMessage($listingProduct, 'New SKU was successfully added',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            $needListListingsProducts[] = $listingProduct;
        }

        $this->unsetLocks();

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Product_Dispatcher');
        $dispatcherObject->process(Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                                   $needListListingsProducts, $this->params['params']);
    }

    // ########################################

    protected function inspectProducts() {}

    // ########################################
}