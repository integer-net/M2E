<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector
    extends Ess_M2ePro_Model_Synchronization_Templates_Inspector
{
    //####################################

    public function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isNotListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isListable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        $searchGeneralId   = $amazonListingProduct->getListingSource()->getSearchGeneralId();
        $searchWorldwideId = $amazonListingProduct->getListingSource()->getSearchWorldwideId();

        if ($variationManager->isVariationProduct()) {

            if ($variationManager->isPhysicalUnit() &&
                !$variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                return false;
            }

            if ($variationManager->isRelationParentType() && $amazonListingProduct->getGeneralId()) {
                return false;
            }

            if ($variationManager->isRelationChildType()) {
                if (!$amazonListingProduct->getGeneralId() && !$amazonListingProduct->isGeneralIdOwner()) {
                    return false;
                }
            }

            if ($variationManager->isIndividualType()) {
                if (!$amazonListingProduct->getGeneralId() &&
                    ($listingProduct->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
                        $listingProduct->getMagentoProduct()->isBundleType())
                ) {
                    return false;
                }
            }

            if ($variationManager->isRelationParentType() &&
                empty($searchGeneralId) &&
                !$amazonListingProduct->isGeneralIdOwner()
            ) {
                return false;
            }
        }

        if (!$amazonListingProduct->getGeneralId() && !$amazonListingProduct->isGeneralIdOwner() &&
            empty($searchGeneralId) && empty($searchWorldwideId)
        ) {
            return false;
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isListMode()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        $additionalData = $listingProduct->getAdditionalData();

        if ($amazonSynchronizationTemplate->isListStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                     Status of Magento Product is Disabled (%date%) though in Synchronization Rules “Product Status”
                     is set to Enabled.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                       $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Status of Magento Product Variation is Disabled (%date%) though in Synchronization Rules
                         “Product Status“ is set to Enabled.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($amazonSynchronizationTemplate->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                     Stock Availability of Magento Product is Out of Stock though in
                     Synchronization Rules “Stock Availability” is set to In Stock.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                       $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Stock Availability of Magento Product Variation is Out of Stock though
                         in Synchronization Rules “Stock Availability” is set to In Stock.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($amazonSynchronizationTemplate->isListWhenQtyMagentoHasValue()) {

            $result = false;

            if ($variationManager->isRelationParentType()) {
                $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);
            } else {
                $productQty = (int)$amazonListingProduct->getQty(true);
            }

            $typeQty = (int)$amazonSynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity“ is set to less then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity” is set to more then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity” is set between  %template_min_qty% and %template_max_qty%',
                        array(
                            '!template_min_qty' => $minQty,
                            '!template_max_qty' => $maxQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($amazonSynchronizationTemplate->isListWhenQtyCalculatedHasValue() &&
            !$variationManager->isRelationParentType()
        ) {

            $result = false;
            $productQty = (int)$amazonListingProduct->getQty(false);

            $typeQty = (int)$amazonSynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set to less then %template_min_qty%',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set to more then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set between  %template_min_qty% and %template_max_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!template_max_qty' => $maxQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($listingProduct->getSynchStatus() != Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED &&
            $this->isTriedToList($listingProduct) &&
            $this->isChangeInitiatorOnlyInspector($listingProduct)
        ) {
            return false;
        }

        return true;
    }

    public function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isStopped() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRelistable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct()) {

            if ($variationManager->isRelationParentType()) {
                return false;
            }

            if (!$variationManager->getTypeModel()->isVariationProductMatched()) {
                return false;
            }
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($amazonSynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($amazonSynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                       $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($amazonSynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($variationManager->isPhysicalUnit() &&
                       $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($amazonSynchronizationTemplate->isRelistWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$amazonListingProduct->getQty(true);

            $typeQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyMagentoHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyMagentoHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if ($amazonSynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$amazonListingProduct->getQty(false);

            $typeQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct()) {

            if ($variationManager->isRelationParentType()) {
                return false;
            }
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($amazonSynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($variationManager->isPhysicalUnit() &&
                       $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($amazonSynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($variationManager->isPhysicalUnit() &&
                       $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($amazonSynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {

            $productQty = (int)$amazonListingProduct->getQty(true);

            $typeQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($amazonSynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {

            $productQty = (int)$amazonListingProduct->getQty(false);

            $typeQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        return false;
    }

    // -----------------------------------

    public function isMeetReviseGeneralRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct()) {

            if ($variationManager->isRelationParentType()) {
                return false;
            }

            if ($variationManager->isPhysicalUnit() &&
                !$variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                return false;
            }
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        return true;
    }

    // -----------------------------------

    public function isMeetReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        $isMaxAppliedValueModeOn = $amazonSynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $amazonSynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $amazonListingProduct->getQty();
        $channelQty = $amazonListingProduct->getOnlineQty();

        if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
            return false;
        }

        if ($productQty > 0 && $productQty != $channelQty) {
            return true;
        }

        return false;
    }

    public function isMeetRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        if (!$amazonSynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        $currentPrice = $amazonListingProduct->getPrice();
        $onlinePrice = $amazonListingProduct->getOnlinePrice();

        $needRevise = $this->checkRevisePricesRequirements($amazonSynchronizationTemplate, $onlinePrice, $currentPrice);
        if ($needRevise) {
            return true;
        }

        $currentSalePriceInfo = $amazonListingProduct->getSalePriceInfo();
        if ($currentSalePriceInfo !== false) {
            $currentSalePrice          = $currentSalePriceInfo['price'];
            $currentSalePriceStartDate = $currentSalePriceInfo['start_date'];
            $currentSalePriceEndDate   = $currentSalePriceInfo['end_date'];
        } else {
            $currentSalePrice          = 0;
            $currentSalePriceStartDate = null;
            $currentSalePriceEndDate   = null;
        }

        $onlineSalePrice = $amazonListingProduct->getOnlineSalePrice();

        if (!$currentSalePrice && !$onlineSalePrice) {
            return false;
        }

        if ((is_null($currentSalePrice) && !is_null($onlineSalePrice)) ||
            (!is_null($currentSalePrice) && is_null($onlineSalePrice))
        ) {
            return true;
        }

        $needRevise = $this->checkRevisePricesRequirements(
            $amazonSynchronizationTemplate, $onlineSalePrice, $currentSalePrice
        );

        if ($needRevise) {
            return true;
        }

        $onlineSalePriceStartDate  = $amazonListingProduct->getOnlineSalePriceStartDate();
        $onlineSalePriceEndDate    = $amazonListingProduct->getOnlineSalePriceEndDate();

        if ($currentSalePriceStartDate != $onlineSalePriceStartDate ||
            $currentSalePriceEndDate   != $onlineSalePriceEndDate
        ) {
            return true;
        }

        return false;
    }

    // -----------------------------------

    public function isMeetReviseDetailsRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        if (!$amazonSynchronizationTemplate->isReviseWhenChangeDetails()) {
            return false;
        }

        return true;
    }

    public function isMeetReviseImagesRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();
        if (!$amazonSynchronizationTemplate->isReviseWhenChangeImages()) {
            return false;
        }

        return true;
    }

    // -----------------------------------

    public function isMeetReviseSynchReasonsRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $reasons = $listingProduct->getSynchReasons();
        if (empty($reasons)) {
            return false;
        }

        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        $synchronizationTemplate = $amazonListingProduct->getSynchronizationTemplate();
        $amazonSynchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

        foreach ($reasons as $reason) {

            $method = 'isRevise'.ucfirst($reason);

            if (method_exists($synchronizationTemplate, $method)) {
                if ($synchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }

            if (method_exists($amazonSynchronizationTemplate, $method)) {
                if ($amazonSynchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }
        }

        return false;
    }

    //####################################

    private function checkRevisePricesRequirements(
        Ess_M2ePro_Model_Amazon_Template_Synchronization $amazonSynchronizationTemplate,
        $onlinePrice, $productPrice
    ) {
        if ((float)$onlinePrice == (float)$productPrice) {
            return false;
        }

        if ((float)$onlinePrice <= 0) {
            return true;
        }

        if ($amazonSynchronizationTemplate->isReviseUpdatePriceMaxAllowedDeviationModeOff()) {
            return true;
        }

        $deviation = abs($onlinePrice - $productPrice) / $onlinePrice * 100;

        return $deviation > $amazonSynchronizationTemplate->getReviseUpdatePriceMaxAllowedDeviation();
    }

    //####################################
}