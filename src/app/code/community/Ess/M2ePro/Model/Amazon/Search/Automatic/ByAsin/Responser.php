<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic_ByAsin_Responser
{
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
    protected $unsetProcessingLock = true;

    // ########################################

    public function initialize(array $params = array(),
                               Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->params = $params;
        $this->marketplace = $marketplace;
        $this->account = $account;

        $this->listingsProducts = array();

        foreach ($this->params['listings_products_ids'] as $listingProductId) {
            $this->listingsProducts[] = Mage::helper('M2ePro/Component_Amazon')
                        ->getObject('Listing_Product',$listingProductId);
        }
    }

    // ########################################

    public function unsetLocks($hash, $fail = false, $message = NULL)
    {
        if (!$this->unsetProcessingLock) {
            return;
        }

        $tempListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->deleteObjectLocks(NULL,$hash);
            $listingProduct->deleteObjectLocks('in_action',$hash);
            $listingProduct->deleteObjectLocks('search_action',$hash);

            $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
            $listingProduct->getChildObject()->setData('general_id_search_status',$processingStatus)->save();

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->deleteObjectLocks(NULL,$hash);
            $listingProduct->getListing()->deleteObjectLocks('products_in_action',$hash);
            $listingProduct->getListing()->deleteObjectLocks('products_search_action',$hash);

            $tempListings[$listingProduct->getListingId()] = true;
        }

        $this->getAccount()->deleteObjectLocks('products_in_action',$hash);
        $this->getAccount()->deleteObjectLocks('products_search_action',$hash);

        $this->getMarketplace()->deleteObjectLocks('products_in_action',$hash);
        $this->getMarketplace()->deleteObjectLocks('products_search_action',$hash);

        if ($fail) {

            $logModel = Mage::getModel('M2ePro/Listing_Log');
            $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

            $tempListings = array();
            foreach ($this->listingsProducts as $listingProduct) {

                /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                if (isset($tempListings[$listingProduct->getListingId()])) {
                    continue;
                }

                $logModel->addListingMessage($listingProduct->getListingId() ,
                                             Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN ,
                                             NULL ,
                                             Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN ,
                                             $message,
                                             Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR ,
                                             Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);

                $tempListings[$listingProduct->getListingId()] = true;
            }
        }
    }

    public function processSucceededResponseData($receivedItems, $hash)
    {
        $listingsProductsByQuery = array();

        $this->unsetProcessingLock = true;
        $this->unsetLocks($hash);
        $this->unsetProcessingLock = false;

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $childListingProduct = $listingProduct->getChildObject();
            $generalId = $this->params['items'][$listingProduct->getId()];

            $isAsin = Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId);

            if (!$isAsin) {

                $isIsbn = Mage::helper('M2ePro/Component_Amazon')->isISBN($generalId);

                if (!$isIsbn) {

                    $listingsProductsByQuery[] = $listingProduct;
                    continue;
                }
            }

            if (!isset($receivedItems[$listingProduct->getId()]) ||
                !is_array($receivedItems[$listingProduct->getId()])) {

                $listingsProductsByQuery[] = $listingProduct;
                continue;
            }

            $childListingProduct->setData('general_id',$generalId);
            $childListingProduct->setData('is_isbn_general_id',
                                          (int)Mage::helper('M2ePro/Component_Amazon')->isISBN($generalId));

            $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;
            $childListingProduct->setData('general_id_search_status', $temp);
            $childListingProduct->setData('general_id_search_suggest_data',NULL);
            $childListingProduct->save();
        }

        if (count($listingsProductsByQuery) > 0) {
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
            $dispatcher->runAutomatic($listingsProductsByQuery, false);
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->marketplace;
    }

    // ########################################
}