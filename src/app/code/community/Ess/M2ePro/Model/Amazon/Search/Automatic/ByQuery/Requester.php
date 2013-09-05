<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Automatic_ByQuery_Requester
{
    const STEP_UPC_EAN = 1;
    const STEP_MAGENTO_TITLE = 2;

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

    protected $currentQuery = '';
    protected $currentStep = self::STEP_UPC_EAN;

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $listingProduct = NULL;

    // ########################################

    public function initialize(array $params = array(),
                               Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->params = $params;
        $this->marketplace = $marketplace;
        $this->account = $account;

        if (isset($this->params['step'])) {
            $this->currentStep = (int)$this->params['step'];
        }

        $this->listingProduct = $this->params['listing_product'];

        $this->calculateCurrentData();
    }

    // ########################################

    public function isPossibleToSearch()
    {
        $result = !empty($this->currentQuery) &&
                  $this->currentStep <= self::STEP_MAGENTO_TITLE;

        if (!$result) {
            $childListingProduct = $this->listingProduct->getChildObject();
            $childListingProduct->setData('general_id_search_status',
                                          Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE);

            $message = Mage::helper('M2ePro')->__('The Product(s) was not found on Amazon.');
            $childListingProduct->setData('general_id_search_suggest_data',json_encode(array('message'=>$message)));
            $childListingProduct->save();
        }

        return $result;
    }

    public function setLocks($hash)
    {
        $this->listingProduct->addObjectLock(NULL,$hash);
        $this->listingProduct->addObjectLock('in_action',$hash);
        $this->listingProduct->addObjectLock('search_action',$hash);

        $this->listingProduct->getListing()->addObjectLock(NULL,$hash);
        $this->listingProduct->getListing()->addObjectLock('products_in_action',$hash);
        $this->listingProduct->getListing()->addObjectLock('products_search_action',$hash);

        $this->account->addObjectLock('products_in_action',$hash);
        $this->account->addObjectLock('products_search_action',$hash);

        $this->marketplace->addObjectLock('products_in_action',$hash);
        $this->marketplace->addObjectLock('products_search_action',$hash);

        $processingStatus = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING;
        $this->listingProduct->getChildObject()->setData('general_id_search_status',$processingStatus)->save();
    }

    public function getResponserParams()
    {
        return array(
            'listing_product_id' => $this->listingProduct->getId(),
            'step' => $this->getCurrentStep()
        );
    }

    // ########################################

    private function calculateCurrentData()
    {
        if (!empty($this->currentQuery) ||
            $this->currentStep > self::STEP_MAGENTO_TITLE) {
            return;
        }

        switch ($this->currentStep) {

            case self::STEP_UPC_EAN:

                    $tempQuery = $this->listingProduct->getChildObject()->getWorldwideId();
                    empty($tempQuery) && $tempQuery = $this->listingProduct->getChildObject()->getAddingWorldwideId();

                    !empty($tempQuery) && $this->currentStep = self::STEP_UPC_EAN;
                    !empty($tempQuery) && $this->currentQuery = (string)$tempQuery;

                break;

            case self::STEP_MAGENTO_TITLE:

                    $tempQuery = '';
                    if ($this->listingProduct->getListing()
                             ->getChildObject()->isSearchByMagentoTitleModeEnabled()) {
                        $tempQuery = $this->listingProduct->getChildObject()->getActualMagentoProduct()->getName();
                    }

                    !empty($tempQuery) && $this->currentStep = self::STEP_MAGENTO_TITLE;
                    !empty($tempQuery) && $this->currentQuery = (string)$tempQuery;

                break;
        }

        empty($this->currentQuery) && $this->currentStep++;
        $this->calculateCurrentData();
    }

    //-----------------------------------------

    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    public function getQueryString()
    {
        return $this->currentQuery;
    }

    // ########################################
}