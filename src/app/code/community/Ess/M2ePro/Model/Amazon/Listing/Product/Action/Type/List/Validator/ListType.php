<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_ListType
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    // ########################################

    private $childGeneralIdsForParent = array();

    private $cachedData = array();

    // ########################################

    public function setChildGeneralIdsForParent(array $generalIds)
    {
        $this->childGeneralIdsForParent = $generalIds;
        return $this;
    }

    public function getChildGeneralIdsForParent()
    {
        return $this->childGeneralIdsForParent;
    }

    // ########################################

    public function validate()
    {
        $generalId = $this->recognizeByListingProductGeneralId();
        if (!empty($generalId)) {
            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        if ($this->getVariationManager()->isIndividualType() && !$this->validateComplexMagentoProductTypes()) {
// M2ePro_TRANSLATIONS
// You cannot list this Product because for selling Bundle or Simple with custom options Magento Products on Amazon the ASIN has to be found manually. Please use manual search to find the required ASIN and try again.
            $this->addMessage('You cannot list this Product because for selling Bundle or Simple
                               With Custom Options Magento Products on Amazon the ASIN/ISBN has to be found manually.
                               Please use Manual Search to find the required ASIN/ISBN and try again.');
            return false;
        }

        $generalId = $this->recognizeBySearchGeneralId();
        if ($generalId === false) {
            return false;
        }

        if (!is_null($generalId)) {

            if ($this->getVariationManager()->isRelationParentType()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking $linkingObject */
                $linkingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Type_List_Linking');
                $linkingObject->setListingProduct($this->getListingProduct());
                $linkingObject->setGeneralId($generalId);
                $linkingObject->setSku($this->data['sku']);
                $linkingObject->setAdditionalData(reset($this->cachedData['amazon_data'][$generalId]));

                $generalIdType = Mage::helper('M2ePro')->isISBN($generalId) ? 'ISBN' : 'ASIN';

                if ($linkingObject->link()) {
// M2ePro_TRANSLATIONS
// Magento Parent Product was successfully linked to Amazon Parent Product by %general_id_type% "%general_id%" via Search Settings.
                    $this->addMessage(
                        Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                            'Magento Parent Product was successfully linked
                             to Amazon Parent Product by %general_id_type% "%general_id%" via Search Settings.',
                            array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                        ),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
                    );
                } else {
// M2ePro_TRANSLATIONS
// Unexpected error has occurred while trying to link Magento Parent Product, although the %general_id_type% "%general_id%" was successfully found on Amazon.
                    $this->addMessage(
                        Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                            'Unexpected error has occurred while trying to link Magento Parent Product,
                             although the %general_id_type% "%general_id%" was successfully found on Amazon.',
                            array('general_id' => $generalId, 'general_id_type' => $generalIdType)
                        )
                    );
                }

                return false;
            }

            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        $generalId = $this->recognizeBySearchWorldwideId();
        if ($generalId === false) {
            return false;
        }

        if (!is_null($generalId)) {
            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        $generalId = $this->recognizeByDescriptionTemplateWorldwideId();
        if ($generalId === false) {
            return false;
        }

        if (!is_null($generalId)) {
            $this->setGeneralId($generalId);
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST);

            return true;
        }

        if ($this->validateNewProduct()) {
            $this->setListType(Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_NEW);
            return true;
        }

        return false;
    }

    // ########################################

    private function recognizeByListingProductGeneralId()
    {
        $generalId = $this->getAmazonListingProduct()->getGeneralId();
        if (empty($generalId)) {
            return null;
        }

        return $generalId;
    }

    private function recognizeBySearchGeneralId()
    {
        if ($this->getVariationManager()->isRelationChildType()) {
            return null;
        }

        $generalId = $this->getAmazonListingProduct()->getListingSource()->getSearchGeneralId();
        if (empty($generalId)) {
            return null;
        }

        if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'M2E Pro did not use New ASIN/ISBN Creation feature assigned because settings
                    for ASIN/ISBN Search were specified in Listing Search Settings and a value
                    %general_id% were set in Magento Attribute for that Product.',
                    array('!general_id' => $generalId)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING
            );
        }

        if (!Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId) &&
            !Mage::helper('M2ePro')->isISBN($generalId)
        ) {
// M2ePro_TRANSLATIONS
// The value "%general_id%" provided for ASIN/ISBN in Listing Search Settings is invalid. Please set the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The value "%general_id%" provided for ASIN/ISBN in Listing Search Settings is invalid.
                     Please set the correct value and try again.',
                    array('!general_id' => $generalId)
                )
            );

            return false;
        }

        $generalIdType = Mage::helper('M2ePro')->isISBN($generalId) ? 'ISBN' : 'ASIN';

        $amazonData = $this->getDataFromAmazon($generalId);
        if (empty($amazonData)) {
// M2ePro_TRANSLATIONS
// %general_id_type% %general_id% provided in Listing Search Settings is not found on Amazon. Please set the correct value and try again. Note: Due to Amazon API restrictions M2E Pro might not see all the existing Products on Amazon.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    '%general_id_type% %general_id% provided in Listing Search Settings
                     is not found on Amazon.
                     Please set the correct value and try again.
                     Note: Due to Amazon API restrictions M2E Pro
                     might not see all the existing Products on Amazon.',
                    array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                )
            );

            return false;
        }

        if (count($amazonData) > 1) {
// M2ePro_TRANSLATIONS
// There is more than one Product found on Amazon using Search by %general_id_type% %general_id%. This situation is not supported by M2E PRO.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'There is more than one Product found on Amazon using Search
                     by %general_id_type% %general_id%.
                     This situation is not supported by M2E Pro.',
                    array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                )
            );

            return false;
        }

        $amazonData = reset($amazonData);

        if (!empty($amazonData['parentage']) && $amazonData['parentage'] == 'parent') {
            if (!$this->getVariationManager()->isRelationParentType()) {
// M2ePro_TRANSLATIONS
// Amazon Parent Product was found using Search by %general_id_type% %general_id% while Simple or Child Product ASIN/ISBN is required.
                $this->addMessage(
                    Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Amazon Parent Product was found using Search by %general_id_type% %general_id%
                         while Simple or Child Product ASIN/ISBN is required.',
                        array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                    )
                );

                return false;
            }

            if (!empty($amazonData['bad_parent'])) {
// M2ePro_TRANSLATIONS
// Working with Amazon Parent Product found using Search by %general_id_type% %general_id% is limited due to Amazon API restrictions.
                $this->addMessage(
                    Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Working with Amazon Parent Product found using Search by %general_id_type% %general_id%
                         is limited due to Amazon API restrictions.',
                        array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                    )
                );

                return false;
            }

            $magentoAttributes = $this->getVariationManager()->getTypeModel()->getProductAttributes();
            $amazonDataAttributes = array_keys($amazonData['variations']['set']);

            if (count($magentoAttributes) != count($amazonDataAttributes)) {
// M2ePro_TRANSLATIONS
// The number of Variation Attributes of the Amazon Parent Product found using Search by %general_id_type% %general_id% does not match the number of Variation Attributes of the Magento Parent Product.
                $this->addMessage(
                    Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'The number of Variation Attributes of the Amazon Parent Product found
                         using Search by %general_id_type% %general_id% does not match the number
                         of Variation Attributes of the Magento Parent Product.',
                        array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                    )
                );

                return false;
            }

            return $generalId;
        }

        if ($this->getVariationManager()->isRelationParentType()) {
// M2ePro_TRANSLATIONS
// Amazon Simple or Child Product was found using Search by %general_id_type% %general_id% while Parent Product ASIN/ISBN is required.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Amazon Simple or Child Product was found using Search by %general_id_type% %general_id%
                     while Parent Product ASIN/ISBN is required.',
                    array('!general_id_type' => $generalIdType, '!general_id' => $generalId)
                )
            );

            return false;
        }

        return $generalId;
    }

    private function recognizeBySearchWorldwideId()
    {
        if ($this->getVariationManager()->isRelationMode()) {
            return null;
        }

        $worldwideId = $this->getAmazonListingProduct()->getListingSource()->getSearchWorldwideId();
        if (empty($worldwideId)) {
            return null;
        }

        $changingListTypeMessage = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
            'M2E Pro did not use New ASIN/ISBN Creation feature assigned because settings
            for UPC/EAN Search were specified in Listing Search Settings and a value
            %worldwide_id% were set in Magento Attribute for that Product.',
            array('!worldwide_id' => $worldwideId)
        );

        if (!Mage::helper('M2ePro')->isUPC($worldwideId) && !Mage::helper('M2ePro')->isEAN($worldwideId)) {
            if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
                $this->addMessage($changingListTypeMessage, Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING);
            }

// M2ePro_TRANSLATIONS
// The value "%worldwide_id%" provided for UPC/EAN in Listing Search Settings is invalid. Please set the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The value "%worldwide_id%" provided for UPC/EAN in Listing Search Settings is invalid.
                     Please set the correct value and try again.',
                    array('!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        $worldwideIdType = Mage::helper('M2ePro')->isUPC($worldwideId) ? 'UPC' : 'EAN';

        $amazonData = $this->getDataFromAmazon($worldwideId);
        if (empty($amazonData)) {
            if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
                return null;
            }

// M2ePro_TRANSLATIONS
// %worldwide_id_type% %worldwide_id% provided in Listing Search Settings is not found on Amazon. Please set the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    '%worldwide_id_type% %worldwide_id% provided in Search Settings
                     is not found on Amazon. Please set the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        if ($this->getAmazonListingProduct()->isGeneralIdOwner()) {
            $this->addMessage($changingListTypeMessage, Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING);
        }

        if (count($amazonData) > 1) {
// M2ePro_TRANSLATIONS
// There is more than one Product found on Amazon using Search by %worldwide_id_type% %worldwide_id%. This situation is not supported by M2E Pro.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'There is more than one Product found on Amazon using Search by %worldwide_id_type% %worldwide_id%.
                     This situation is not supported by M2E Pro.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        $amazonData = reset($amazonData);

        if (!empty($amazonData['parentage']) &&
            $amazonData['parentage'] == 'parent' &&
            empty($amazonData['requested_child_id'])
        ) {
// M2ePro_TRANSLATIONS
// Amazon Parent Product was found using Search by %worldwide_id_type% %worldwide_id% while Simple or Child Product ASIN/ISBN is required.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Amazon Parent Product was found using Search by %worldwide_id_type% %worldwide_id%
                     while Simple or Child Product ASIN/ISBN is required.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        if (!empty($amazonData['requested_child_id'])) {
            return $amazonData['requested_child_id'];
        } else {
            return $amazonData['product_id'];
        }
    }

    private function recognizeByDescriptionTemplateWorldwideId()
    {
        if (!$this->getAmazonListingProduct()->isGeneralIdOwner()) {
            return null;
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return null;
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_Description $descriptionTemplate */
        $descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        if (empty($descriptionTemplate)) {
            return null;
        }

        if (!$descriptionTemplate->isNewAsinAccepted()) {
            return null;
        }

        $worldwideId = $this->getAmazonListingProduct()->getDescriptionTemplateSource()->getWorldwideId();
        if (empty($worldwideId)) {
            return null;
        }

        if (!Mage::helper('M2ePro')->isUPC($worldwideId) && !Mage::helper('M2ePro')->isEAN($worldwideId)) {
// M2ePro_TRANSLATIONS
// The value "%worldwide_id%" provided for UPC/EAN in Description Policy is invalid. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The value "%worldwide_id%" provided for UPC/EAN in Description Policy is invalid.
                     Please provide the correct value and try again.',
                    array('!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        $worldwideIdType = Mage::helper('M2ePro')->isUPC($worldwideId) ? 'UPC' : 'EAN';

        $amazonData = $this->getDataFromAmazon($worldwideId);
        if (empty($amazonData)) {
            return null;
        }

        if (count($amazonData) > 1) {
// M2ePro_TRANSLATIONS
// There is more than one Product found on Amazon using %worldwide_id_type% %worldwide_id% provided in Description Policy. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'There is more than one Product found on Amazon using %worldwide_id_type% %worldwide_id%
                     provided in Description Policy. Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        $amazonData = reset($amazonData);

        if (!empty($amazonData['parentage']) &&
            $amazonData['parentage'] == 'parent' &&
            empty($amazonData['requested_child_id'])
        ) {
// M2ePro_TRANSLATIONS
// Amazon Parent Product was found using %worldwide_id_type% %worldwide_id% provided in Description Policy while Simple or Child Product is required. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Amazon Parent Product was found using %worldwide_id_type% %worldwide_id%
                     provided in Description Policy while Simple or Child Product is required.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        $generalId       = $amazonData['product_id'];
        $parentGeneralId = null;

        if (!empty($amazonData['requested_child_id'])) {
            $parentGeneralId = $generalId;
            $generalId       = $amazonData['requested_child_id'];
        }

        if (!$this->getVariationManager()->isRelationChildType()) {
            return $generalId;
        }

        if (empty($amazonData['requested_child_id']) || !empty($amazonData['bad_parent'])) {
// M2ePro_TRANSLATIONS
// The Product found on Amazon using %worldwide_id_type% %worldwide_id% provided in Description Policy is not a Child Product. Linking was failed because only Child Product is required. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The Product found on Amazon using %worldwide_id_type% %worldwide_id%
                     provided in Description Policy is not a Child Product.
                     Linking was failed because only Child Product is required.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        if ($this->isExistInChildGeneralIdsForParent($parentGeneralId, $generalId)) {
// M2ePro_TRANSLATIONS
// The Product with the same %worldwide_id_type% %worldwide_id% provided in Description Policy was found on Amazon. Linking was failed because this %worldwide_id_type% has already been assigned to another Child Product of this parent. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The Product with the same %worldwide_id_type% %worldwide_id% provided in Description Policy
                     was found on Amazon. Linking was failed because this %worldwide_id% has already been assigned
                     to another Child Product of this parent.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $this->getVariationManager()
            ->getTypeModel()
            ->getParentListingProduct()
            ->getChildObject();

        if ($parentAmazonListingProduct->getGeneralId() != $parentGeneralId) {
// M2ePro_TRANSLATIONS
// The Product was found on Amazon using %worldwide_id_type% %worldwide_id% provided in Description Policy. Linking was failed because found Child Product is related to different Parent. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The Product was found on Amazon using %worldwide_id_type% %worldwide_id%
                     provided in Description Policy. Linking was failed because found Child Product is related to
                     different Parent. Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        $parentChannelVariations = $parentAmazonListingProduct->getVariationManager()
            ->getTypeModel()
            ->getChannelVariations();

        if (!isset($parentChannelVariations[$generalId])) {
// M2ePro_TRANSLATIONS
// The Product was found on Amazon using %worldwide_id_type% %worldwide_id% provided in Description Policy. Linking was failed because the respective Parent has no Child Product with required combination of the Variation Attributes values. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The Product was found on Amazon using %worldwide_id_type% %worldwide_id%
                     provided in Description Policy. Linking was failed because the respective Parent has no
                     Child Product with required combination of the Variation Attributes values.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $childProductCollection */
        $childProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $childProductCollection->addFieldToFilter('variation_parent_id', $parentAmazonListingProduct->getId());
        $existedChildGeneralIds = $childProductCollection->getColumnValues('general_id');

        if (in_array($generalId, $existedChildGeneralIds)) {
// M2ePro_TRANSLATIONS
// The Product was found on Amazon using %worldwide_id_type% %worldwide_id% provided in Description Policy. The Child Product with required combination of the Attributes values has already been added to your Parent Product. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'The Product was found on Amazon using %worldwide_id_type% %worldwide_id%
                     provided in Description Policy. The Child Product with required combination
                     of the Attributes values has already been added to your Parent Product.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        return $generalId;
    }

    // ----------------------------------------

    private function validateNewProduct()
    {
        if (!$this->getAmazonListingProduct()->isGeneralIdOwner()) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because ASIN/ISBN is not assigned, UPC/EAN value is not provided and the Search Settings are invalid. Please set the required Settings and try again.
            $this->addMessage(
                'Product cannot be Listed because ASIN/ISBN is not assigned, UPC/EAN value
                 is not provided and the Search Settings are invalid. Please set the required
                 Settings and try again.'
            );

            return false;
        }

        $descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        if (empty($descriptionTemplate)) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because the process of new ASIN/ISBN creation has started but Description Policy is missing. Please assign the Description Policy and try again.
            $this->addMessage(
                'Product cannot be Listed because the process of new ASIN/ISBN creation has started
                 but Description Policy is missing. Please assign the Description Policy and try again.'
            );

            return false;
        }

        if (!$descriptionTemplate->isNewAsinAccepted()) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because new ASIN/ISBN creation is disabled in the Description Policy assigned to this Product. Please enable new ASIN creation and try again.
            $this->addMessage(
                'Product cannot be Listed because new ASIN/ISBN creation is disabled in the Description
                 Policy assigned to this Product. Please enable new ASIN/ISBN creation and try again.'
            );

            return false;
        }

        if ($this->getVariationManager()->isRelationMode()) {
            $channelTheme = $this->getChannelTheme();

            if (empty($channelTheme)) {
// M2ePro_TRANSLATIONS
// Product is not Listed. The process of New ASIN/ISBN creation has been started, but the Variation Theme was not set. Please, set the Variation Theme to list this Product.
                $this->addMessage(
                    'Product is not Listed. The process of New ASIN/ISBN creation has been started,
                     but the Variation Theme was not set.
                     Please, set the Variation Theme to list this Product.'
                );

                return false;
            }
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            return true;
        }

        $descriptionTemplateSource = $this->getAmazonListingProduct()->getDescriptionTemplateSource();

        $worldwideId = $descriptionTemplateSource->getWorldwideId();
        $registeredParameter = $descriptionTemplate->getRegisteredParameter();

        if (empty($worldwideId) && empty($registeredParameter)) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because no UPC/EAN value or Register Parameter is set in the Description Policy. Please set the required Settings and try again.
            $this->addMessage(
                'Product cannot be Listed because no UPC/EAN value or Register Parameter
                 is set in the Description Policy. Please set the required Settings and try again.'
            );

            return false;
        }

        if (empty($worldwideId)) {
            return true;
        }

        if (!Mage::helper('M2ePro')->isUPC($worldwideId) && !Mage::helper('M2ePro')->isEAN($worldwideId)) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed because the value provided for UPC/EAN in the Description Policy has an invalid format. Please provide the correct value and try again.
            $this->addMessage(
                'Product cannot be Listed because the value provided for UPC/EAN in the
                 Description Policy has an invalid format. Please provide the correct value and try again.'
            );

            return false;
        }

        $worldwideIdType = Mage::helper('M2ePro')->isUPC($worldwideId) ? 'UPC' : 'EAN';

        $amazonData = $this->getDataFromAmazon($worldwideId);
        if (!empty($amazonData)) {
// M2ePro_TRANSLATIONS
// Product cannot be Listed. New ASIN/ISBN cannot be created because %worldwide_id_type% %worldwide_id% provided in the Description Policy has been found on Amazon. Please provide the correct value and try again.
            $this->addMessage(
                Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product cannot be Listed. New ASIN/ISBN cannot be created because %worldwide_id_type%
                     %worldwide_id% provided in the Description Policy has been found on Amazon.
                     Please provide the correct value and try again.',
                    array('!worldwide_id_type' => $worldwideIdType, '!worldwide_id' => $worldwideId)
                )
            );

            return false;
        }

        return true;
    }

    // ########################################

    private function validateComplexMagentoProductTypes()
    {
        if ($this->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            return false;
        }

        if ($this->getMagentoProduct()->isBundleType()) {
            return false;
        }

        return true;
    }

    // ########################################

    private function getDataFromAmazon($identifier)
    {
        if (isset($this->cachedData['amazon_data'][$identifier])) {
            return $this->cachedData['amazon_data'][$identifier];
        }

        $validation = Mage::helper('M2ePro');

        $idType = (Mage::helper('M2ePro/Component_Amazon')->isASIN($identifier) ? 'ASIN' :
                  ($validation->isISBN($identifier)                             ? 'ISBN' :
                  ($validation->isUPC($identifier)                              ? 'UPC'  :
                  ($validation->isEAN($identifier)                              ? 'EAN'  : false))));

        if (empty($idType)) {
            return array();
        }

        $params = array(
            'item'    => $identifier,
            'id_type' => $idType,
            'variation_child_modification' => 'parent',
        );

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('product', 'search', 'byIdentifier',
                                                               $params, 'items',
                                                               $this->getListingProduct()->getListing()->getAccount());

        $result = $dispatcherObject->process($connectorObj);
        return $this->cachedData['amazon_data'][$identifier] = $result;
    }

    // ########################################

    private function getChannelTheme()
    {
        $variationManager = $this->getAmazonListingProduct()->getVariationManager();
        if (!$variationManager->isRelationMode()) {
            return null;
        }

        $typeModel = $variationManager->getTypeModel();

        if ($variationManager->isRelationChildType()) {
            $typeModel = $variationManager->getTypeModel()
                ->getParentListingProduct()
                ->getChildObject()
                ->getVariationManager()
                ->getTypeModel();
        }

        return $typeModel->getChannelTheme();
    }

    // ########################################

    private function setListType($listType)
    {
        $this->data['list_type'] = $listType;
    }

    private function setGeneralId($generalId)
    {
        $this->data['general_id'] = $generalId;

        if ($this->getVariationManager()->isRelationChildType()) {
            $this->addChildGeneralIdForParent(
                $this->getVariationManager()->getTypeModel()->getParentListingProduct()->getId(),
                $generalId
            );
        }
    }

    // ----------------------------------------

    private function isExistInChildGeneralIdsForParent($parentGeneralId, $childGeneralId)
    {
        if (!isset($this->childGeneralIdsForParent[$parentGeneralId])) {
            return false;
        }

        return in_array($childGeneralId, $this->childGeneralIdsForParent[$parentGeneralId]);
    }

    private function addChildGeneralIdForParent($parentGeneralId, $childGeneralId)
    {
        $this->childGeneralIdsForParent[$parentGeneralId][] = $childGeneralId;
    }

    // ########################################
}