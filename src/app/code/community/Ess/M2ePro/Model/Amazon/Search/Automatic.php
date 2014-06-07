<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic
{
    const STEP_ASIN_ISBN = 1;
    const STEP_UPC_EAN = 2;
    const STEP_MAGENTO_TITLE = 3;

    // ########################################

    private $steps = array(
        self::STEP_ASIN_ISBN,
        self::STEP_UPC_EAN,
        self::STEP_MAGENTO_TITLE
    );

    // ########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct, $step = NULL)
    {
        $step = $step ? $step : reset($this->steps);

        if (!in_array($step, $this->steps)) {
            return $this->processNotFound($listingProduct);
        }

        $query = $this->getQueryParam($listingProduct, $step);

        if (!$query) {
            return $this->process($listingProduct, ++$step);
        }

        $searchMethod = $this->getSearchMethod($step, $query);

        $params = array(
            'step' => $step,
            'item' => $query,
            'type' => 'automatic',
            'search_method' => $searchMethod,
            'listing_product_id' => $listingProduct->getId(),
        );

        if ($searchMethod == 'byIdentifier') {
            $params['id_type'] = $this->getIdentifierType($query);
        }

        return Mage::getModel('M2ePro/Connector_Amazon_Dispatcher')->processConnector(
            'search', $searchMethod, 'requester', $params,
            $listingProduct->getAccount(), 'Ess_M2ePro_Model_Amazon'
        );
    }

    public function processResponse(Ess_M2ePro_Model_Listing_Product $listingProduct, $result, $params = array())
    {
        if (empty($result)) {
            return $this->process($listingProduct, $params['step'] + 1);
        }

        $params['search_method'] == 'byAsin' && $result = array($result);

        /* @var $childListingProduct Ess_M2ePro_Model_Amazon_Listing_Product */
        $childListingProduct = $listingProduct->getChildObject();

        $statusNone = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
        $statusAuto = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;

        if ($params['step'] == self::STEP_MAGENTO_TITLE) {
            $tempResult = $this->filterReceivedItemsFullTitleMatch($result, $listingProduct);
            count($tempResult) == 1 && $result = $tempResult;
        }

        if (count($result) > 1) {

            $childListingProduct->setData('general_id_search_status', $statusNone);
            $childListingProduct->setData('general_id_search_suggest_data', json_encode($result));

            return $childListingProduct->save();
        }

        $result = reset($result);

        $generalId = $result['general_id'];

        if ($result['is_variation_product']) {

            // attempt to assign to parent product
            if (empty($result['requested_child_id'])) {
                $childListingProduct->setData('general_id_search_status', $statusNone);
                $childListingProduct->setData('general_id_search_suggest_data', json_encode(array($result)));

                return $childListingProduct->save();
            }

            $generalId = $result['requested_child_id'];
        }

        $dataForUpdate = array(
            'general_id' => $generalId,
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($generalId),
            'general_id_search_status' => $statusAuto,
            'general_id_search_suggest_data' => NULL
        );

        $childListingProduct->addData($dataForUpdate)->save();
    }

    // ----------------------------------------

    private function processNotFound(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $childListingProduct = $listingProduct->getChildObject();
        $childListingProduct->setData('general_id_search_status',
                                      Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE);

        $message = Mage::helper('M2ePro')->__('The Product(s) was not found on Amazon.');
        $childListingProduct->setData('general_id_search_suggest_data',json_encode(array('message'=>$message)));
        $childListingProduct->save();

        return true;
    }

    // ########################################

    private function getQueryParam(Ess_M2ePro_Model_Listing_Product $listingProduct, $step)
    {
        $validation = Mage::helper('M2ePro');

        switch ($step) {
            case self::STEP_ASIN_ISBN:

                $query = $listingProduct->getChildObject()->getGeneralId();
                empty($query) && $query = $listingProduct->getChildObject()->getAddingGeneralId();

                if (!Mage::helper('M2ePro/Component_Amazon')->isASIN($query) && !$validation->isISBN($query)) {
                    $query = false;
                }

                break;

            case self::STEP_UPC_EAN:

                $query = $listingProduct->getChildObject()->getWorldwideId();
                empty($query) && $query = $listingProduct->getChildObject()->getAddingWorldwideId();

                if (!$validation->isEAN($query) && !$validation->isUPC($query)) {
                    $query = false;
                }

                break;

            case self::STEP_MAGENTO_TITLE:

                $query = false;

                if ($listingProduct->getListing()->getChildObject()->isSearchByMagentoTitleModeEnabled()) {
                    $query = $listingProduct->getChildObject()->getActualMagentoProduct()->getName();
                }

                break;

            default: throw new Exception('Step is out of knowledge base.');
        }

        return $query;
    }

    private function getSearchMethod($step, $query)
    {
        $searchMethods = array_combine(
            $this->steps, array('byAsin', 'byIdentifier', 'byQuery')
        );

        if (!isset($searchMethods[$step])) {
            throw new Exception('Step is out of knowledge base.');
        }

        $searchMethod = $searchMethods[$step];

        if ($searchMethod == 'byAsin' && Mage::helper('M2ePro')->isISBN13($query)) {
            $searchMethod = 'byIdentifier';
        }

        return $searchMethod;
    }

    private function getIdentifierType($identifier)
    {
        $validation = Mage::helper('M2ePro');

        return (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier) ? 'ASIN' :
               ($validation->isISBN($identifier)                             ? 'ISBN' :
               ($validation->isUPC($identifier)                              ? 'UPC'  :
               ($validation->isEAN($identifier)                              ? 'EAN'  : false))));
    }

    // ########################################

    private function filterReceivedItemsFullTitleMatch($results, Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $return = array();

        $magentoProductTitle = $listingProduct->getChildObject()->getActualMagentoProduct()->getName();
        $magentoProductTitle = trim(strtolower($magentoProductTitle));

        foreach ($results as $item) {
            $itemTitle = trim(strtolower($item['title']));
            if ($itemTitle == $magentoProductTitle) {
                $return[] = $item;
            }
        }

        return $return;
    }

    // ########################################
}