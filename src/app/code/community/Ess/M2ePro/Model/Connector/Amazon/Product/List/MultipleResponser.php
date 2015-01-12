<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_List_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            Mage::getModel('M2ePro/Connector_Amazon_Product_Helper')
                        ->updateAfterListAction($listingProduct,
                                                $this->getListingProductRequestNativeData($listingProduct),
                                                $this->params);

            // M2ePro_TRANSLATIONS
            // Item was successfully listed
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully listed',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        try {

            $this->removeFromQueueOfSKus();
            parent::unsetLocks($fail,$message);

        } catch (Exception $e) {

            $exception = new Exception('Failed to clear amazon SKU queue. '.$e->getMessage());
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            throw $e;
        }
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
            'amazon_list_skus_queue_' . $this->getAccount()->getId(),
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