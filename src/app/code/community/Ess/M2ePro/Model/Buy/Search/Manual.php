<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Manual
{
    // ########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct, $query)
    {
        $searchMethod = 'byQuery';

        if ($this->isQueryGeneralId($query) || $this->isQueryUpc($query)) {
            $searchMethod = 'byIdentifier';
        }

        $params = array(
            'query' => $query,
            'type' => 'manual',
            'listing_product_id' => $listingProduct->getId()
        );

        if ($searchMethod == 'byIdentifier') {
            $params['search_type'] = $this->getSearchType($query);
        }

        Mage::getModel('M2ePro/Connector_Buy_Dispatcher')->processConnector(
            'search', $searchMethod, 'requester', $params, $listingProduct->getAccount(), 'Ess_M2ePro_Model_Buy'
        );

        $result = Mage::helper('M2ePro/Data_Global')->getValue('temp_buy_manual_search_result');
        Mage::helper('M2ePro/Data_Global')->unsetValue('temp_buy_manual_search_result');

        return $result;
    }

    public function processResponse(Ess_M2ePro_Model_Listing_Product $listingProduct, $result, $params = array())
    {
        Mage::helper('M2ePro/Data_Global')->setValue('temp_buy_manual_search_result', $result);
    }

    // ########################################

    private function getSearchType($query)
    {
        if (empty($query)) {
            return false;
        }

        if ($this->isQueryGeneralId($query)) {
            return Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_Items::SEARCH_TYPE_GENERAL_ID;
        }

        if ($this->isQueryUpc($query)) {
            return Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_Items::SEARCH_TYPE_UPC;
        }

        return false;
    }

    private function isQueryGeneralId($query)
    {
        if (empty($query)) {
            return false;
        }

        return preg_match('/^\d{8,9}$/', $query);
    }

    private function isQueryUpc($query)
    {
        return Mage::helper('M2ePro')->isUPC($query);
    }

    // ########################################
}