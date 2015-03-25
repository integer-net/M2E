<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Relist_Multiple
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','update','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'relist';
    }

    protected function getResponserModel()
    {
        return 'Amazon_Product_Relist_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if ($listingProduct->isBlocked()) {

                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The action can not be executed as the item was Closed, Incomplete or Blocked on Amazon.
                     Please, go to Amazon seller central and Active the item.
                     After the next synchronization the item will be available.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if (!$listingProduct->isStopped()) {

                // M2ePro_TRANSLATIONS
                // The item either is listed, or not listed yet or not available
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The item either is listed, or not listed yet or not available',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if ($amazonListingProduct->isVariationProduct() && !$amazonListingProduct->isVariationMatched()) {

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

            if ($amazonListingProduct->getPrice() <= 0) {

                // M2ePro_TRANSLATIONS
                // The price must be greater than 0. Please, check the Selling Format Template and Product settings.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The price must be greater than 0. Please, check the Selling Format Template and Product settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $this->removeAndUnlockListingProduct($listingProduct);
                continue;
            }

            if ($amazonListingProduct->getQty() <= 0) {

                // M2ePro_TRANSLATIONS
                // The quantity must be greater than 0. Please, check the Selling Format Template and Product settings.
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'The quantity must be greater than 0. '.
                    'Please, check the Selling Format Template and Product settings.',
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

            $nativeData = Mage::getModel('M2ePro/Connector_Amazon_Product_Helper')
                                         ->getRelistRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        $this->checkQtyWarnings();

        return $requestData;
    }

    // ########################################
}