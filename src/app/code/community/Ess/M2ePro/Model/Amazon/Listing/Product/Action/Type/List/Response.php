<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess($params = array())
    {
        $generalId = $this->getGeneralId($params);

        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ignore_next_inventory_synch' => 1,
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendIdentifiersData($data, $generalId);

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();

        if (!$variationManager->isRelationParentType()) {
            $data['is_afn_channel'] = Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO;

            $data = $this->appendQtyValues($data);
            $data = $this->appendPriceValues($data);
        }

        $this->getListingProduct()->addData($data);
        $this->setVariationData($generalId);
        $this->getListingProduct()->save();

        if (!$variationManager->isRelationParentType()) {
            $this->createAmazonItem();
        }
    }

    // ########################################

    private function appendIdentifiersData($data, $generalId)
    {
        $data['sku'] = $this->getRequestData()->getSku();

        $isGeneralIdOwner = $this->getIsGeneralIdOwner();
        if (!is_null($isGeneralIdOwner)) {
            $data['general_id_owner'] = $isGeneralIdOwner;
        }

        if (!empty($generalId)) {
            $data['general_id']         = $generalId;
            $data['is_isbn_general_id'] = Mage::helper('M2ePro')->isISBN($generalId);
        }

        return $data;
    }

    // ########################################

    private function setVariationData($generalId)
    {
        if (empty($generalId)) {
            return;
        }

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();
        if (!$variationManager->isRelationMode()) {
            return;
        }

        $typeModel = $variationManager->getTypeModel();

        if ($variationManager->isRelationParentType()) {

            $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
            $detailsModel->setMarketplaceId($this->getMarketplace()->getId());

            $channelAttributes = $detailsModel->getVariationThemeAttributes(
                $this->getRequestData()->getProductDataNick(), $typeModel->getChannelTheme()
            );

            $typeModel->setChannelAttributesSets(array_fill_keys($channelAttributes, array()), false);

            return;
        }

        if (!$this->getRequestData()->hasVariationAttributes()) {
            return;
        }

        if ($typeModel->isVariationChannelMatched()) {
            return;
        }

        $channelOptions = $this->getRequestData()->getVariationAttributes();

        // set child product options
        // -------------------
        $typeModel->setChannelVariation($channelOptions);
        // -------------------

        $parentListingProduct = $typeModel->getParentListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */
        $parentTypeModel = $parentListingProduct->getChildObject()
            ->getVariationManager()
            ->getTypeModel();

        // add child variation to parent
        // -------------------
        $channelVariations = (array)$parentTypeModel->getChannelVariations();
        $channelVariations[$generalId] = $channelOptions;
        $parentTypeModel->setChannelVariations($channelVariations, false);
        // -------------------

        // update parent attributes sets
        // -------------------
        $channelAttributesSets = $parentTypeModel->getChannelAttributesSets();
        foreach ($channelOptions as $attribute => $value) {
            if (!isset($channelAttributesSets[$attribute])) {
                $channelAttributesSets[$attribute] = array();
            }

            if (in_array($value, $channelAttributesSets[$attribute])) {
                continue;
            }

            $channelAttributesSets[$attribute][] = $value;
        }
        $parentTypeModel->setChannelAttributesSets($channelAttributesSets, false);
        // -------------------

        $parentListingProduct->save();
    }

    // ########################################

    private function getGeneralId(array $params)
    {
        if (!empty($params['general_id'])) {
            return $params['general_id'];
        }

        if ($this->getRequestData()->isTypeModeNew()) {
            return null;
        }

        return $this->getRequestData()->getProductId();
    }

    private function getIsGeneralIdOwner()
    {
        $variationManager = $this->getAmazonListingProduct()->getVariationManager();

        if ($variationManager->isRelationChildType()) {
            return null;
        }

        if ($variationManager->isRelationParentType()) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES;
        }

        if ($this->getRequestData()->isTypeModeNew()) {
            return Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES;
        }

        return Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO;
    }

    // ########################################

    private function createAmazonItem()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Type_List_Linking');
        $linkingObject->setListingProduct($this->getListingProduct());

        $linkingObject->createAmazonItem();
    }

    // ########################################
}