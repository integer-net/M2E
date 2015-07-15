<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking
{
    // ########################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $generalId = null;

    private $sku = null;

    private $additionalData = array();

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

    public function setSku($sku)
    {
        $this->sku = $sku;
        return $this;
    }

    public function setAdditionalData(array $data)
    {
        $this->additionalData = $data;
        return true;
    }

    // ########################################

    public function link()
    {
        $this->validate();

        if (!$this->getVariationManager()->isRelationMode()) {
            return $this->linkSimpleOrIndividualProduct();
        }

        if ($this->getVariationManager()->isRelationChildType()) {
            return $this->linkChildProduct();
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return $this->linkParentProduct();
        }

        return false;
    }

    public function createAmazonItem()
    {
        $data = array(
            'account_id'     => $this->getListingProduct()->getListing()->getAccountId(),
            'marketplace_id' => $this->getListingProduct()->getListing()->getMarketplaceId(),
            'sku'            => $this->getSku(),
            'product_id'     => $this->getListingProduct()->getProductId(),
            'store_id'       => $this->getListingProduct()->getListing()->getStoreId(),
        );

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit $typeModel */
            $typeModel = $this->getVariationManager()->getTypeModel();
            $data['variation_options'] = json_encode($typeModel->getProductOptions());
        }

        /** @var Ess_M2ePro_Model_Amazon_Item $object */
        $object = Mage::getModel('M2ePro/Amazon_Item');
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

        $sku = $this->getSku();
        if (empty($sku)) {
            throw new InvalidArgumentException('SKU was not set.');
        }
    }

    // ########################################

    private function linkSimpleOrIndividualProduct()
    {
        $this->getListingProduct()->addData(array(
            'general_id'         => $this->getGeneralId(),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($this->getGeneralId()),
            'general_id_owner'   => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO,
            'sku'                => $this->getSku(),
            'status'             => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
        ));
        $this->getListingProduct()->save();

        $this->createAmazonItem();

        return true;
    }

    private function linkChildProduct()
    {
        $this->getListingProduct()->addData(array(
            'general_id'         => $this->getGeneralId(),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($this->getGeneralId()),
            'sku'                => $this->getSku(),
            'status'             => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        ));

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
        $parentTypeModel = $typeModel->getParentListingProduct()
            ->getChildObject()
            ->getVariationManager()
            ->getTypeModel();

        $parentVariations = $parentTypeModel->getChannelVariations();
        if (!isset($parentVariations[$this->generalId])) {
            return false;
        }

        $typeModel->setChannelVariation($parentVariations[$this->generalId]);

        $this->createAmazonItem();

        $parentTypeModel->getProcessor()->process();

        return true;
    }

    private function linkParentProduct()
    {
        $data = $this->getAdditionalData();
        if (empty($data['parentage']) || $data['parentage'] != 'parent' || !empty($data['bad_parent'])) {
            return false;
        }

        $dataForUpdate = array(
            'general_id'         => $this->getGeneralId(),
            'is_isbn_general_id' => Mage::helper('M2ePro')->isISBN($this->getGeneralId()),
            'sku'                => $this->getSku(),
        );

        $descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        $listingProductSku = $this->getAmazonListingProduct()->getSku();

        // improve check is sku existence
        if (empty($listingProductSku) && !empty($descriptionTemplate) && $descriptionTemplate->isNewAsinAccepted()) {
            $dataForUpdate['general_id_owner'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES;
        } else {
            $dataForUpdate['general_id_owner'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO;
        }

        $this->getListingProduct()->addData($dataForUpdate);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
        $typeModel = $this->getVariationManager()->getTypeModel();

        $typeModel->setChannelAttributesSets($data['variations']['set'], false);

        $channelVariations = array();
        foreach ($data['variations']['asins'] as $generalId => $options) {
            $channelVariations[$generalId] = $options['specifics'];
        }
        $typeModel->setChannelVariations($channelVariations, false);

        $this->getListingProduct()->save();

        $typeModel->getProcessor()->process();

        return true;
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    private function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    private function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    // -----------------------------------------

    private function getGeneralId()
    {
        return $this->generalId;
    }

    private function getSku()
    {
        if (!is_null($this->sku)) {
            return $this->sku;
        }

        return $this->getAmazonListingProduct()->getSku();
    }

    private function getAdditionalData()
    {
        if (!empty($this->additionalData)) {
            return $this->additionalData;
        }

        return $this->additionalData = $this->getDataFromAmazon();
    }

    // ########################################

    private function getDataFromAmazon()
    {
        $params = array(
            'item'    => $this->generalId,
            'id_type' => Mage::helper('M2ePro')->isISBN($this->generalId) ? 'ISBN' : 'ASIN',
            'variation_child_modification' => 'none',
        );

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('product', 'search', 'byIdentifier',
                                                               $params, 'items',
                                                               $this->getListingProduct()->getListing()->getAccount());

        $result = $dispatcherObject->process($connectorObj);
        return !empty($result) ? reset($result) : array();
    }

    // ########################################
}