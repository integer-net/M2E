<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Abstract
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    private $variationManager = NULL;

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager
     */
    public function setVariationManager(Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager)
    {
        $this->variationManager = $variationManager;
        $this->listingProduct = $variationManager->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    public function getVariationManager()
    {
        return $this->variationManager;
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getActualMagentoProduct()
    {
        return $this->getAmazonListingProduct()->getActualMagentoProduct();
    }

    // ########################################

    abstract public function clearTypeData();

    // ########################################

    protected function getCurrentMagentoAttributes()
    {
        $magentoVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        return array_keys($magentoVariations['set']);
    }

    // ########################################
}