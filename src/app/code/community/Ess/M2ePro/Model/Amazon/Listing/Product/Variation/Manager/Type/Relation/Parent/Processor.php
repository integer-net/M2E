<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor
{
    // ##########################################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $marketplaceId = null;

    private $actualMagentoProductVariations = null;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
    private $typeModel = null;

    private $childListingProducts = null;

    /** @var Ess_M2ePro_Model_Amazon_Template_Description $descriptionTemplate */
    private $descriptionTemplate = null;

    private $possibleThemes = null;

    // ##########################################################

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

    public function setListingProduct($listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    // ##########################################################

    public function process()
    {
        if (is_null($this->listingProduct)) {
            throw new Exception('Listing Product was not set.');
        }

        $this->listingProduct->getMagentoProduct()->enableCache();

        foreach ($this->getSortedProcessors() as $processor) {
            $this->getProcessorModel($processor)->process();
        }

        $this->listingProduct->setData('variation_parent_need_processor', 0);

        $this->listingProduct->save();
    }

    // ##########################################################

    private function getSortedProcessors()
    {
        return array(
            'GeneralIdOwner',
            'Attributes',
            'Theme',
            'MatchedAttributes',
            'Options',
            'Status',
            'Selling',
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
     */
    private function getProcessorModel($processorName)
    {
        $model = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_'.$processorName
        );
        $model->setProcessor($this);

        return $model;
    }

    // ##########################################################

    public function isGeneralIdSet()
    {
        return (bool)$this->getAmazonListingProduct()->getGeneralId();
    }

    public function isGeneralIdOwner()
    {
        return $this->getAmazonListingProduct()->isGeneralIdOwner();
    }

    // ##########################################################

    public function getActualMagentoProductVariations()
    {
        if (!is_null($this->actualMagentoProductVariations)) {
            return $this->actualMagentoProductVariations;
        }

        return $this->actualMagentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();
    }

    public function getProductVariation(array $options)
    {
        return $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationTypeStandard($options);
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent
     */
    public function getTypeModel()
    {
        if (!is_null($this->typeModel)) {
            return $this->typeModel;
        }

        return $this->typeModel = $this->getAmazonListingProduct()
            ->getVariationManager()
            ->getTypeModel();
    }

    // ##########################################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    public function getChildListingProducts()
    {
        if (!is_null($this->childListingProducts)) {
            return $this->childListingProducts;
        }

        return $this->childListingProducts = $this->getTypeModel()->getChildListingsProducts();
    }

    public function createChildListingProduct(array $productOptions = array(),
                                              array $channelOptions = array(),
                                              $generalId = null)
    {
        $childListingProduct = $this->getTypeModel()->createChildListingProduct(
            $productOptions, $channelOptions, $generalId
        );

        $this->childListingProducts[$childListingProduct->getId()] = $childListingProduct;
    }

    public function tryToDeleteChildListingProduct(Ess_M2ePro_Model_Listing_Product $childListingProduct)
    {
        if ($childListingProduct->isLocked()) {
            return false;
        }

        if ($childListingProduct->isStoppable()) {
            Mage::getModel('M2ePro/StopQueue')->add($childListingProduct);
        }

        $childListingProduct->deleteInstance();
        unset($this->childListingProducts[$childListingProduct->getId()]);

        return true;
    }

    // ##########################################################

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if (!is_null($this->descriptionTemplate)) {
            return $this->descriptionTemplate;
        }

        return $this->descriptionTemplate = $this->getAmazonListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    // ##########################################################

    public function getPossibleThemes()
    {
        if (!is_null($this->possibleThemes)) {
            return $this->possibleThemes;
        }

        return $this->possibleThemes = Mage::getModel('M2ePro/Amazon_Marketplace_Details')
            ->setMarketplaceId($this->getMarketplaceId())
            ->getVariationThemes(
                $this->getAmazonDescriptionTemplate()->getProductDataNick()
            );
    }

    public function getMarketplaceId()
    {
        if (!is_null($this->marketplaceId)) {
            return $this->marketplaceId;
        }

        return $this->marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
    }

    // ##########################################################
}