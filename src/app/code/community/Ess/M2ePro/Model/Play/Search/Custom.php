<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Custom
{
    // ########################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $query = null;

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    // ########################################

    public function process()
    {
        $searchData = Mage::getModel('M2ePro/Connector_Play_Dispatcher')->processConnector(
            'custom', $this->getSearchMethod(), 'requester', $this->getConnectorParams(),
            $this->listingProduct->getAccount(), 'Ess_M2ePro_Model_Play_Search'
        );

        return $this->prepareResult($searchData);
    }

    // ########################################

    private function getConnectorParams()
    {
        $searchMethod = $this->getSearchMethod();

        $params = array(
            'query' => $searchMethod == 'byQuery' ? $this->query : $this->getStrippedQuery(),
        );

        return $params;
    }

    private function getSearchMethod()
    {
        $searchMethod = 'byQuery';
        $strippedQuery = $this->getStrippedQuery();

        if (Mage::helper('M2ePro')->isISBN($strippedQuery) || Mage::helper('M2ePro')->isEAN($strippedQuery)) {
            $searchMethod = 'byEanIsbn';
        }

        return $searchMethod;
    }

    private function prepareResult($searchData)
    {
        $connectorParams = $this->getConnectorParams();

        if ($this->getSearchMethod() == 'byQuery') {
            $type = 'string';
        } else {
            $type = Mage::helper('M2ePro')->isISBN($connectorParams['query']) ? 'isbn' : 'ean';
        }

        return array(
            'type'  => $type,
            'value' => $connectorParams['query'],
            'data'  => $searchData,
        );
    }

    private function getStrippedQuery()
    {
        return str_replace('-', '', $this->query);
    }

    // ########################################
}