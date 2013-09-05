<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Dispatcher
{
    // ########################################

    public function runManual(Ess_M2ePro_Model_Listing_Product $listingProduct, $query,
                              Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                              Ess_M2ePro_Model_Account $account = NULL)
    {
        if (!$this->checkSearchConditions($listingProduct) || empty($query)) {
            return false;
        }

        $params = array(
            'listing_product' => $listingProduct,
            'query' => $query
        );

        if (is_null($marketplace)) {
            $marketplace = $listingProduct->getListing()->getMarketplace();
        }

        if (is_null($account)) {
            $account = $listingProduct->getListing()->getAccount();
        }

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Buy_Dispatcher');
            $dispatcherObject->processConnector('search', 'manual' ,'requester',
                                                $params, $marketplace, $account,
                                                'Ess_M2ePro_Model_Buy');
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        $result = Mage::helper('M2ePro/Data_Global')->getValue('temp_buy_manual_search_SKU_result');
        Mage::helper('M2ePro/Data_Global')->unsetValue('temp_buy_manual_search_SKU_result');

        if (!is_array($result)) {
            return array();
        }

        return $result;
    }

    public function runAutomatic(array $listingsProducts)
    {
        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$this->checkSearchConditions($listingProduct)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $params = array(
                'listing_product' => $listingProduct
            );

            $marketplace = $listingProduct->getListing()->getMarketplace();
            $account = $listingProduct->getListing()->getAccount();

            try {
                $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Buy_Dispatcher');
                $dispatcherObject->processConnector('search', 'automatic' ,'requester',
                                                    $params, $marketplace, $account,
                                                    'Ess_M2ePro_Model_Buy');
            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
                return false;
            }
        }

        return true;
    }

    // ########################################

    private function checkSearchConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return $listingProduct->isNotListed() &&
               !$listingProduct->getChildObject()->getTemplateNewProductId() &&
               !$listingProduct->getChildObject()->getGeneralId();
    }

    // ########################################
}