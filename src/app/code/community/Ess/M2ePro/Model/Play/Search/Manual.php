<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Manual
{
    // ########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct, $query)
    {
        $searchMethod = 'byQuery';

        $tempQuery = str_replace('-','',$query);
        if ($this->isQueryEan($tempQuery) || $this->isQueryIsbn($tempQuery)) {
            $query = $tempQuery;
            $searchMethod = 'byEanIsbn';
        }

        $params = array(
            'query' => $query,
            'type' => 'manual',
            'listing_product_id' => $listingProduct->getId()
        );

        Mage::getModel('M2ePro/Connector_Play_Dispatcher')->processConnector(
            'search', $searchMethod, 'requester', $params, $listingProduct->getAccount(), 'Ess_M2ePro_Model_Play'
        );

        $result = Mage::helper('M2ePro/Data_Global')->getValue('temp_play_manual_search_result');
        Mage::helper('M2ePro/Data_Global')->unsetValue('temp_play_manual_search_result');

        return $result;
    }

    public function processResponse(Ess_M2ePro_Model_Listing_Product $listingProduct, $result, $params = array())
    {
        Mage::helper('M2ePro/Data_Global')->setValue('temp_play_manual_search_result', $result);
    }

    // ########################################

    private function isQueryIsbn($query)
    {
        return Mage::helper('M2ePro')->isISBN($query);
    }

    private function isQueryEan($query)
    {
        return Mage::helper('M2ePro')->isEAN($query);
    }

    // ########################################
}