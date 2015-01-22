<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Delete_Multiple
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','delete','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return 'delete_and_remove';
        }
        return 'delete';
    }

    protected function getResponserModel()
    {
        return 'Amazon_Product_Delete_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        if (isset($this->params['remove']) && (bool)$this->params['remove']) {
            return Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT;
        }
        return Ess_M2ePro_Model_Listing_Log::_ACTION_DELETE_PRODUCT_FROM_COMPONENT;
    }

    // ########################################

    protected function filterManualListingsProducts()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if ($listingProduct->isNotListed()) {

                $this->removeAndUnlockListingProduct($listingProduct);

                if (!isset($this->params['remove']) || !(bool)$this->params['remove']) {

                    // M2ePro_TRANSLATIONS
                    // Item is not listed or not available
                    $this->addListingsProductsLogsMessage(
                        $listingProduct,
                        'Item is not listed or not available',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );
                } else {
                    $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                    $listingProduct->deleteInstance();
                }

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
                                         ->getDeleteRequestData($listingProduct,$this->params);

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