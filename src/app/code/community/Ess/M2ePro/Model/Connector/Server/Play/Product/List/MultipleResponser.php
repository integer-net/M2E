<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Play_Product_List_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Server_Play_Product_Responser
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

            Mage::getModel('M2ePro/Play_Connector_Product_Helper')
                        ->updateAfterListAction($listingProduct,$requestData,$tempParams);

            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully listed',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    protected function processResponseData($response)
    {
        $this->removeFromQueueOfSKus();
        return parent::processResponseData($response);
    }

    // ########################################

    private function removeFromQueueOfSKus()
    {
        $skusToRemove = array();

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($this->listingsProducts as $listingProduct) {
            $requestData = $this->getListingProductRequestNativeData($listingProduct);
            $skusToRemove[$requestData['sku']] = true;
        }

        $lockItem = Mage::getModel('M2ePro/LockItem')->load(
            'play_list_skus_queue_' . $this->getMarketplace()->getId() . '_' . $this->getAccount()->getId(),
            'nick'
        );

        if (!$lockItem->getId()) {
            return;
        }

        $skus = json_decode($lockItem->getData('data'),true);

        foreach ($skus as $key => $sku) {
            if (isset($skusToRemove[$sku])) {
                unset($skus[$key]);
                continue;
            }
        }

        if (count($skus) == 0) {
            $lockItem->delete();
            return;
        }

        $lockItem->setData('data',json_encode(array_unique($skus)))->save();
    }

    // ########################################
}