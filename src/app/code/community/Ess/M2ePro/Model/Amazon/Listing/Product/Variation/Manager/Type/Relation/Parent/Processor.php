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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Abstract
     */
    private function getProcessorModel($processorName)
    {
        $model = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_'.$processorName
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
        $productVariation = $this->getProductVariation($productOptions);
        if (empty($productVariation)) {
            return;
        }

        $data = array(
            'listing_id' => $this->getListingProduct()->getListingId(),
            'product_id' => $this->getListingProduct()->getProductId(),
            'status'     => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'general_id' => $generalId,
            'is_general_id_owner' => $this->isGeneralIdOwner(),
            'status_changer'   => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN,
            'is_variation_product'    => 1,
            'is_variation_parent'     => 0,
            'variation_parent_id'     => $this->getListingProduct()->getId(),
            'template_description_id' => $this->getAmazonListingProduct()->getTemplateDescriptionId(),
        );

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentModel(
            Ess_M2ePro_Helper_Component_Amazon::NICK,'Listing_Product'
        )->setData($data)->save();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $amazonListingProduct->getVariationManager()->getTypeModel();

        !empty($productOptions) && $typeModel->setProductVariation($productVariation);
        !empty($channelOptions) && $typeModel->setChannelVariation($channelOptions);

        $this->childListingProducts[$listingProduct->getId()] = $listingProduct;
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