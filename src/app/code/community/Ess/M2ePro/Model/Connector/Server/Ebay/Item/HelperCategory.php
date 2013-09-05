<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_HelperCategory
{
    // ########################################

    public function getRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $categoryTemplate Ess_M2ePro_Model_Ebay_Template_Category */
        $categoryTemplate = $listingProduct->getChildObject()->getCategoryTemplate();
        $categoryTemplate->setMagentoProduct($listingProduct->getMagentoProduct());

        $this->addMainCategoriesData($categoryTemplate,$requestData);
        $this->addStoreCategoriesData($categoryTemplate,$requestData);

        $this->addAdditionalData($categoryTemplate,$requestData);
        $this->addMotorsSpecificsData($listingProduct, $listingProduct->getListing()->getMarketplace(),
                                      $categoryTemplate, $requestData);

        $this->addItemSpecificsData($listingProduct, $categoryTemplate, $requestData);
        $this->addCustomItemSpecificsData($listingProduct, $categoryTemplate, $requestData);

        $this->clearConflictedItemSpecificsData($requestData);

        $this->addAttributeSetData($listingProduct, $categoryTemplate, $requestData);
    }

    // ########################################

    protected function addMainCategoriesData(Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                             array &$requestData)
    {
        $requestData['category_main_id'] = $categoryTemplate->getMainCategory();
        $requestData['category_secondary_id'] = $categoryTemplate->getSecondaryCategory();
    }

    protected function addStoreCategoriesData(Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                              array &$requestData)
    {
        $requestData['store_category_main_id'] = $categoryTemplate->getStoreCategoryMain();
        $requestData['store_category_secondary_id'] = $categoryTemplate->getStoreCategorySecondary();
    }

    // ########################################

    protected function addAdditionalData(Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                         array &$requestData)
    {
        $requestData['tax_category'] = $categoryTemplate->getTaxCategory();
    }

    protected function addMotorsSpecificsData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                              Ess_M2ePro_Model_Marketplace $marketplace,
                                              Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                              array &$requestData)
    {
        if ($marketplace->getId() != Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            return;
        }

        $categoryId = $categoryTemplate->getMainCategory();
        $categoryData = $marketplace->getChildObject()->getCategory($categoryId);

        $features = !empty($categoryData['features']) ? (array)json_decode($categoryData['features'], true) : array();
        $attributes = !empty($features['parts_compatibility_attributes'])
                                ? $features['parts_compatibility_attributes']
                                : array();

        if (empty($attributes)) {
            return;
        }

        $categoryTemplate->getMagentoProduct()->clearNotFoundAttributes();

        $specifics = $categoryTemplate->getMotorsSpecifics();

        $notFoundAttributes = $categoryTemplate->getMagentoProduct()->getNotFoundAttributes();
        if (!empty($notFoundAttributes)) {
            Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')->addNotFoundAttributesMessage(
                $listingProduct, Mage::helper('M2ePro')->__('Compatibility'), $notFoundAttributes
            );

            return;
        }

        foreach ($specifics as $specific) {

            $compatibilityList = array();
            $compatibilityData = $specific->getCompatibilityData();

            foreach ($compatibilityData as $key => $value) {

                if ($value == '--') {
                    unset($compatibilityData[$key]);
                    continue;
                }

                $name = $key;
                foreach ($attributes as $attribute) {
                    if ($attribute['title'] == $key) {
                        $name = $attribute['ebay_id'];
                        break;
                    }
                }

                $compatibilityList[] = array(
                    'name'  => $name,
                    'value' => $value
                );
            }

            $requestData['motors_specifics'][] = $compatibilityList;
        }
    }

    // ########################################

    protected function addItemSpecificsData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                            array &$requestData)
    {
        $requestData['item_specifics'] = array();

        $filter = array('mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS);
        $specifics = $categoryTemplate->getSpecifics(true, $filter);

        foreach ($specifics as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $categoryTemplate->getMagentoProduct()->clearNotFoundAttributes();

            $tempAttributeData = $specific->getAttributeData();
            $tempAttributeValues = $specific->getValues();

            $notFoundAttributes = $categoryTemplate->getMagentoProduct()->getNotFoundAttributes();
            if (!empty($notFoundAttributes)) {
                Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__('Specifics'), $notFoundAttributes
                );

                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $requestData['item_specifics'][] = array(
                'name' => $tempAttributeData['id'],
                'value' => $values
            );
        }
    }

    protected function addCustomItemSpecificsData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                  Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                                  array &$requestData)
    {
        $filter = array('mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS);
        $specifics = $categoryTemplate->getSpecifics(true, $filter);

        foreach ($specifics as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $categoryTemplate->getMagentoProduct()->clearNotFoundAttributes();

            $tempAttributeData = $specific->getAttributeData();
            $tempAttributeValues = $specific->getValues();

            $notFoundAttributes = $categoryTemplate->getMagentoProduct()->getNotFoundAttributes();
            if (!empty($notFoundAttributes)) {
                Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__('Specifics'), $notFoundAttributes
                );

                continue;
            }

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $requestData['item_specifics'][] = array(
                'name' => $tempAttributeData['title'],
                'value' => $values
            );
        }
    }

    //----------------------------------------

    protected function addAttributeSetData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                           Ess_M2ePro_Model_Ebay_Template_Category $categoryTemplate,
                                           array &$requestData)
    {
        $requestData['attribute_set'] = array(
            'attribute_set_id' => 0,
            'attributes' => array()
        );

        $filters = array('mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ATTRIBUTE_SET);
        $specifics = $categoryTemplate->getSpecifics(true, $filters);

        foreach ($specifics as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $categoryTemplate->getMagentoProduct()->clearNotFoundAttributes();

            $tempAttributeData = $specific->getAttributeData();
            $tempAttributeValues = $specific->getValues();

            $notFoundAttributes = $categoryTemplate->getMagentoProduct()->getNotFoundAttributes();
            if (!empty($notFoundAttributes)) {
                Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__('Specifics'), $notFoundAttributes
                );

                continue;
            }

            $requestData['attribute_set']['attribute_set_id'] = $specific->getModeRelationId();

            $requestData['attribute_set']['attributes'][] = array(
                'id' => $tempAttributeData['id'],
                'value' => $tempAttributeValues
            );
        }
    }

    // ########################################

    protected function clearConflictedItemSpecificsData(array &$requestData)
    {
        if (empty($requestData['item_specifics'])  || !is_array($requestData['item_specifics'])  ||
            empty($requestData['variations_sets']) || !is_array($requestData['variations_sets']) ||
            empty($requestData['is_variation_item'])) {
            return;
        }

        $variationAttributes = array_keys($requestData['variations_sets']);
        $variationAttributes = array_map('strtolower',$variationAttributes);

        foreach ($requestData['item_specifics'] as $key => $itemSpecific) {
            if (in_array(strtolower($itemSpecific['name']), $variationAttributes)) {
                unset($requestData['item_specifics'][$key]);
            }
        }
    }

    // ########################################
}