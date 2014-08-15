<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_List_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Buy_Product_Responser
{
    // ########################################

    protected function processSucceededListingsProducts(array $listingsProducts = array())
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            Mage::getModel('M2ePro/Connector_Buy_Product_Helper')
                        ->updateAfterListAction($listingProduct,
                                                $this->getListingProductRequestNativeData($listingProduct),
                                                $this->params);

            // M2ePro_TRANSLATIONS
            // Item was successfully listed
            $this->addListingsProductsLogsMessage($listingProduct, 'Item was successfully listed',
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }

        $this->updateGeneralIdsWhenItIsEmpty($listingsProducts);
    }

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        $this->removeFromQueueOfSKus();
        parent::unsetLocks($fail,$message);
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
            'buy_list_skus_queue_' . $this->getAccount()->getId(),
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

    protected function updateGeneralIdsWhenItIsEmpty(array $listingsProducts = array())
    {
        $tempListingsProducts = array();
        foreach ($listingsProducts as $listingProduct) {
            if ($listingProduct->getChildObject()->getGeneralId() > 0) {
                continue;
            }
            $tempListingsProducts[] = $listingProduct;
        }

        if (count($tempListingsProducts) <= 0) {
            return;
        }

        $listingProductsPacks = array_chunk($tempListingsProducts,5,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            foreach ($listingProductsPack as $key => $listingProduct) {
                $skus[$key] = $listingProduct->getChildObject()->getSku();
            }

            try {

                /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Buy_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
                $response = $dispatcherObject->processVirtual(
                    'product','search','skuByReferenceId',
                    array('items' => $skus),'items', $this->getAccount()
                );

            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
                continue;
            }

            foreach($response as $key => $value) {

                if ($value === false || empty($value['general_id']) ) {
                    continue;
                }

                $data = array(
                    'general_id' => $value['general_id']
                );

                $listingProductsPack[$key]->addData($data)->save();
            }
        }
    }

    // ########################################
}