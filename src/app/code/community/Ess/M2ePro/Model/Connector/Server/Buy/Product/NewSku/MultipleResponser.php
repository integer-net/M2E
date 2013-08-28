<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Product_NewSku_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Server_Buy_Product_Responser
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

            $requestData = $this->getListingProductRequestNativeData($listingProduct);

            if (!isset($this->responseBody['skus'][$listingProduct->getId().'-id'])) {

                // Parser hack -> Mage::helper('M2ePro')->__('New SKU was not added');
                $this->addListingsProductsLogsMessage($listingProduct, 'New SKU was not added',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                continue;
            }

            $tempParams = array(
                'general_id' => $this->responseBody['skus'][$listingProduct->getId().'-id']
            );

            Mage::getModel('M2ePro/Buy_Connector_Product_Helper')
                        ->updateAfterNewSkuAction($listingProduct,$requestData,$tempParams);

            // Parser hack -> Mage::helper('M2ePro')->__('New SKU was successfully added');
            $this->addListingsProductsLogsMessage($listingProduct, 'New SKU was successfully added',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            $needListListingsProducts[] = $listingProduct;
        }

        $this->unsetLocks();

        $dispatcherObject = Mage::getModel('M2ePro/Buy_Connector')->getProductDispatcher();
        $dispatcherObject->process(Ess_M2ePro_Model_Buy_Connector_Product_Dispatcher::ACTION_LIST,
                                   $needListListingsProducts, $this->params['params']);
    }

    // ########################################

    protected function inspectProducts() {}

    // ########################################
}