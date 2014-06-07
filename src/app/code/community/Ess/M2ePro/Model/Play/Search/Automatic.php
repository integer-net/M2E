<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Search_Automatic
{
    const STEP_GENERAL_ID = 1;
    const STEP_MAGENTO_TITLE = 2;

    // ########################################

    private $steps = array(
        self::STEP_GENERAL_ID,
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

        $searchMethod = $this->getSearchMethod($listingProduct, $step);

        $params = array(
            'step' => $step,
            'query' => $query,
            'type' => 'automatic',
            'listing_product_id' => $listingProduct->getId(),
        );

        return Mage::getModel('M2ePro/Connector_Play_Dispatcher')->processConnector(
            'search', $searchMethod, 'requester', $params,
            $listingProduct->getAccount(), 'Ess_M2ePro_Model_Play'
        );
    }

    public function processResponse(Ess_M2ePro_Model_Listing_Product $listingProduct, $result, $params = array())
    {
        if (empty($result)) {
            return $this->process($listingProduct, $params['step'] + 1);
        }

        /* @var $childListingProduct Ess_M2ePro_Model_Play_Listing_Product */
        $childListingProduct = $listingProduct->getChildObject();

        $statusAuto = Ess_M2ePro_Model_Play_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;

        if ($params['step'] == self::STEP_MAGENTO_TITLE) {
            $tempResult = $this->filterReceivedItemsFullTitleMatch($result, $listingProduct);
            count($tempResult) == 1 && $result = $tempResult;
        }

        if (count($result) == 1) {

            if (!isset($result[0]['variations'])) {

                $childListingProduct->setData('general_id',$result[0]['general_id']);
                $childListingProduct->setData('general_id_type',Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_GENERAL_ID);
                $childListingProduct->setData('general_id_search_status', $statusAuto);
                $childListingProduct->setData('general_id_search_suggest_data',NULL);

                return $childListingProduct->save();
            }

            if (count($result[0]['variations']['play_ids']) == 1) {

                reset($result[0]['variations']['play_ids']);

                $childListingProduct->setData('general_id',key($result[0]['variations']['play_ids']));
                $childListingProduct->setData('general_id_type',Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_GENERAL_ID);
                $childListingProduct->setData('general_id_search_status', $statusAuto);
                $childListingProduct->setData('general_id_search_suggest_data',NULL);

                return $childListingProduct->save();
            }
        }

        $childListingProduct->setData('general_id_search_suggest_data',json_encode($result));
        $childListingProduct->save();
    }

    // ----------------------------------------

    private function processNotFound(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $childListingProduct = $listingProduct->getChildObject();

        $message = Mage::helper('M2ePro')->__('The Product(s) was not found on Play.com.');
        $childListingProduct->setData('general_id_search_suggest_data',json_encode(array('message'=>$message)));
        $childListingProduct->save();

        return true;
    }

    // ########################################

    private function getQueryParam(Ess_M2ePro_Model_Listing_Product $listingProduct, $step)
    {
        /* @var $playListingProduct Ess_M2ePro_Model_Play_Listing_Product */
        $playListingProduct = $listingProduct->getChildObject();

        switch ($step) {
            case self::STEP_GENERAL_ID:

                $query = $playListingProduct->getGeneralId();
                empty($query) && $query = $playListingProduct->getAddingGeneralId();

                break;

            case self::STEP_MAGENTO_TITLE:

                $query = false;

                if ($playListingProduct->getPlayListing()->isSearchByMagentoTitleModeEnabled()) {
                    $query = $playListingProduct->getActualMagentoProduct()->getName();
                }

                break;

            default: throw new Exception('Step is out of knowledge base.');
        }

        return $query;
    }

    private function getSearchMethod(Ess_M2ePro_Model_Listing_Product $listingProduct, $step)
    {
        /** @var $listing Ess_M2ePro_Model_Play_Listing */
        $listing = $listingProduct->getListing()->getChildObject();

        if ($step == self::STEP_GENERAL_ID) {
            if ($listing->isGeneralIdWorldwideMode() || $listing->isGeneralIdIsbnMode()) {
                return 'byEanIsbn';
            }
        }

        return 'byQuery';
    }

    // ----------------------------------------

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