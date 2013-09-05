<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Dispatcher
{
    // ########################################

    public function runManual(Ess_M2ePro_Model_Listing_Product $listingProduct, $query,
                              Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                              Ess_M2ePro_Model_Account $account = NULL)
    {
        if (!$listingProduct->isNotListed() || empty($query)) {
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
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher');
            $dispatcherObject->processConnector('search', 'manual' ,'requester',
                                                $params, $marketplace, $account,
                                                'Ess_M2ePro_Model_Amazon');
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        $result = Mage::helper('M2ePro/Data_Global')->getValue('temp_amazon_manual_search_asin_result');
        Mage::helper('M2ePro/Data_Global')->unsetValue('temp_amazon_manual_search_asin_result');

        if (!is_array($result)) {
            return array();
        }

        return $result;
    }

    // ########################################

    public function runAutomatic(array $listingsProducts, $isUseAsinSearch = true)
    {
        $listingsProductsFiltered = array();

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$this->checkSearchConditions($listingProduct)) {
                continue;
            }

            $listingsProductsFiltered[] = $listingProduct;
        }

        if (count($listingsProductsFiltered) <= 0) {
            return false;
        }

        $listingsProductsByAsin = array();
        $listingsProductsByQuery = array();

        if ($isUseAsinSearch) {
            foreach ($listingsProductsFiltered as $listingProductFiltered) {

                /** @var $listingProductFiltered Ess_M2ePro_Model_Listing_Product */
                $tempGeneralId = $listingProductFiltered->getChildObject()->getAddingGeneralId();

                if (empty($tempGeneralId)) {
                    continue;
                }

                $isAsin = Mage::helper('M2ePro/Component_Amazon_Validation')->isASIN($tempGeneralId);

                if (!$isAsin) {

                    $isIsbn = Mage::helper('M2ePro/Component_Amazon_Validation')->isISBN($tempGeneralId);

                    if (!$isIsbn) {

                        continue;
                    }
                }

                $listingsProductsByAsin[] = $listingProductFiltered;
            }
        } else {
            $listingsProductsByQuery = $listingsProductsFiltered;
        }

        $listingsProductsByAsinIds = array();
        foreach ($listingsProductsByAsin as $listingProductByAsin) {
            /** @var $listingProductByAsin Ess_M2ePro_Model_Listing_Product */
            $listingsProductsByAsinIds[] = $listingProductByAsin->getId();
        }

        foreach ($listingsProductsFiltered as $listingProductFiltered) {
            /** @var $listingProductFiltered Ess_M2ePro_Model_Listing_Product */
            if (in_array($listingProductFiltered->getId(),$listingsProductsByAsinIds)) {
                continue;
            }
            $listingsProductsByQuery[] = $listingProductFiltered;
        }

        try {
            $this->runAutomaticByAsin($listingsProductsByAsin);
            $this->runAutomaticByQuery($listingsProductsByQuery);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return true;
    }

    //----------------------------------------

    private function runAutomaticByAsin(array $listingsProducts)
    {
        $accountsMarketplaces = array();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            /** @var $account Ess_M2ePro_Model_Account */
            $account = $listingProduct->getListing()->getAccount();
            /** @var $marketplace Ess_M2ePro_Model_Marketplace */
            $marketplace = $listingProduct->getListing()->getMarketplace();

            $identifier = $account->getId().'_'.$marketplace->getId();

            if (!isset($accountsMarketplaces[$identifier])) {
                $accountsMarketplaces[$identifier] = array(
                    'account' => $account,
                    'marketplace' => $marketplace,
                    'listings_products' => array()
                );
            }

            $accountsMarketplaces[$identifier]['listings_products'][] = $listingProduct;
        }

        foreach ($accountsMarketplaces as $accountMarketplace) {

            /** @var $account Ess_M2ePro_Model_Account */
            $account = $accountMarketplace['account'];
            /** @var $marketplace Ess_M2ePro_Model_Marketplace */
            $marketplace = $accountMarketplace['marketplace'];

            $listingsProductsParts = array_chunk($accountMarketplace['listings_products'],10);

            foreach ($listingsProductsParts as $listingsProductsPart) {

                if (count($listingsProductsPart) <= 0) {
                    continue;
                }

                $params = array(
                    'listings_products' => $listingsProductsPart
                );

                $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher');
                $dispatcherObject->processConnector('automatic', 'byAsin' ,'requester',
                                                    $params, $marketplace, $account,
                                                    'Ess_M2ePro_Model_Amazon_Search');
            }
        }
    }

    private function runAutomaticByQuery(array $listingsProducts)
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $params = array(
                'listing_product' => $listingProduct
            );

            $marketplace = $listingProduct->getListing()->getMarketplace();
            $account = $listingProduct->getListing()->getAccount();

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher');
            $dispatcherObject->processConnector('automatic', 'byQuery' ,'requester',
                                                $params, $marketplace, $account,
                                                'Ess_M2ePro_Model_Amazon_Search');
        }
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