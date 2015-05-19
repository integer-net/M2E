<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_List_Linking
{
    // ########################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $generalId = null;

    private $generalIdType = null;

    private $sku = null;

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
        return $this;
    }

    public function setGeneralId($generalId)
    {
        $this->generalId = $generalId;
        return $this;
    }

    public function setGeneralIdType($generalIdType)
    {
        $this->generalIdType = $generalIdType;
        return $this;
    }

    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    // ########################################

    public function link()
    {
        $this->validate();

        $this->getListingProduct()->addData(array(
            'general_id'      => $this->getGeneralId(),
            'general_id_type' => $this->getGeneralIdType(),
            'sku'             => $this->getSku(),
        ));
        $this->getListingProduct()->save();

        $this->createPlayItem();
    }

    public function createPlayItem()
    {
        $data = array(
            'account_id'     => $this->getListingProduct()->getListing()->getAccountId(),
            'marketplace_id' => $this->getListingProduct()->getListing()->getMarketplaceId(),
            'sku'            => $this->getSku(),
            'product_id'     => $this->getListingProduct()->getProductId(),
            'store_id'       => $this->getListingProduct()->getListing()->getStoreId(),
        );

        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()
        ) {
            $data['variation_options'] = json_encode($this->getVariationManager()->getProductOptions());
        }

        /** @var Ess_M2ePro_Model_Play_Item $object */
        $object = Mage::getModel('M2ePro/Play_Item');
        $object->setData($data);
        $object->save();

        return $object;
    }

    // ########################################

    private function validate()
    {
        $listingProduct = $this->getListingProduct();
        if (empty($listingProduct)) {
            throw new InvalidArgumentException('Listing Product was not set.');
        }

        $generalId = $this->getGeneralId();
        if (empty($generalId)) {
            throw new InvalidArgumentException('General ID was not set.');
        }

        $generalIdType = $this->getGeneralIdType();
        if (empty($generalIdType)) {
            throw new InvalidArgumentException('General ID type was not set.');
        }

        $sku = $this->getSku();
        if (empty($sku)) {
            throw new InvalidArgumentException('SKU was not set.');
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    private function getListingProduct()
    {
        return $this->listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product
     */
    private function getPlayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product_Variation_Manager
     */
    private function getVariationManager()
    {
        return $this->getPlayListingProduct()->getVariationManager();
    }

    // -----------------------------------------

    private function getGeneralId()
    {
        return $this->generalId;
    }

    private function getGeneralIdType()
    {
        return $this->generalIdType;
    }

    private function getSku()
    {
        if (!is_null($this->sku)) {
            return $this->sku;
        }

        return $this->getPlayListingProduct()->getSku();
    }

    // ########################################
}