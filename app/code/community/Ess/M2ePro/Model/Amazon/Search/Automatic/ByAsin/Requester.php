<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic_ByAsin_Requester
{
    // ########################################

    protected $params = array();

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    // ########################################

    protected $listingsProducts = NULL;

    // ########################################

    public function initialize(array $params = array(),
                               Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->params = $params;
        $this->marketplace = $marketplace;
        $this->account = $account;

        $this->listingsProducts = $this->params['listings_products'];
    }

    // ########################################

    public function setLocks($hash)
    {
        $tempListings = array();

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->addObjectLock(NULL,$hash);
            $listingProduct->addObjectLock('in_action',$hash);
            $listingProduct->addObjectLock('search_action',$hash);

            $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING;
            $listingProduct->getChildObject()->setData('general_id_search_status',$processingStatus)->save();

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->addObjectLock(NULL,$hash);
            $listingProduct->getListing()->addObjectLock('products_in_action',$hash);
            $listingProduct->getListing()->addObjectLock('products_search_action',$hash);

            $tempListings[$listingProduct->getListingId()] = true;
        }

        $this->account->addObjectLock('products_in_action',$hash);
        $this->account->addObjectLock('products_search_action',$hash);

        $this->marketplace->addObjectLock('products_in_action',$hash);
        $this->marketplace->addObjectLock('products_search_action',$hash);
    }

    public function getResponserParams()
    {
        $listingsProductsIds = array();
        foreach ($this->listingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingsProductsIds[] = $listingProduct->getId();
        }

        return array(
            'listings_products_ids' => $listingsProductsIds
        );
    }

    public function getQueryItems()
    {
        $items = array();

        foreach ($this->listingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $items[$listingProduct->getId()] = $listingProduct->getChildObject()->getAddingGeneralId();
        }

        return $items;
    }

    // ########################################
}