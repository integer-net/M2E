<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Manual
{
    // ########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct, $query)
    {
        $searchMethod = 'byQuery';
        $validation = Mage::helper('M2ePro');
        $tempQuery = str_replace('-', '', $query);

        if (Mage::helper('M2ePro/Component_Amazon')->isASIN($tempQuery) ||
            $validation->isISBN10($tempQuery)) {

            $query = $tempQuery;
            $searchMethod = 'byAsin';

        } elseif ($validation->isEAN($tempQuery) ||
                  $validation->isUPC($tempQuery) ||
                  $validation->isISBN13($tempQuery)) {

            $query = $tempQuery;
            $searchMethod = 'byIdentifier';
        }

        $params = array(
            'item' => $query,
            'type' => 'manual',
            'only_realtime' => true,
            'search_method' => $searchMethod,
            'listing_product_id' => $listingProduct->getId()
        );

        if ($searchMethod == 'byIdentifier') {
            $params['id_type'] = $this->getIdentifierType($query);
        }

        Mage::getModel('M2ePro/Connector_Amazon_Dispatcher')->processConnector(
            'search', $searchMethod, 'requester', $params, $listingProduct->getAccount(), 'Ess_M2ePro_Model_Amazon'
        );

        $result = Mage::helper('M2ePro/Data_Global')->getValue('temp_amazon_manual_search_result');
        Mage::helper('M2ePro/Data_Global')->unsetValue('temp_amazon_manual_search_result');

        return $result;
    }

    public function processResponse(Ess_M2ePro_Model_Listing_Product $listingProduct, $result, $params = array())
    {
        if ($params['search_method'] == 'byAsin' && $result !== false) {
            $result = is_null($result) ? array() : array($result);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_amazon_manual_search_result', $result);
    }

    // ########################################

    private function getIdentifierType($identifier)
    {
        $validation = Mage::helper('M2ePro');

        return (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier) ? 'ASIN' :
               ($validation->isISBN($identifier)                             ? 'ISBN' :
               ($validation->isUPC($identifier)                              ? 'UPC'  :
               ($validation->isEAN($identifier)                              ? 'EAN'  : false))));
    }

    // ########################################
}