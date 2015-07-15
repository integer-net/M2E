<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Inspector
    extends Ess_M2ePro_Model_Synchronization_Templates_Inspector
{
    //####################################

    public function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isNotListed()) {
            return false;
        }

        if (!$listingProduct->isListable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isListMode()) {
            return false;
        }

        $additionalData = $listingProduct->getAdditionalData();

        if ($ebaySynchronizationTemplate->isScheduleEnabled() &&
            (!$ebaySynchronizationTemplate->isScheduleIntervalNow() ||
             !$ebaySynchronizationTemplate->isScheduleWeekNow())
        ) {
            $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'Product was not automatically Listed according to the Schedule Settings in Synchronization Policy.',
                array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
            );
            $additionalData['synch_template_list_rules_note'] = $note;

            $listingProduct->setSettings('additional_data', $additionalData)->save();

            return false;
        }

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($ebaySynchronizationTemplate->isListStatusEnabled()) {

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
            } else if ($ebayListingProduct->isVariationsReady()) {

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

        if ($ebaySynchronizationTemplate->isListIsInStock()) {

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
            } else if ($ebayListingProduct->isVariationsReady()) {

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

        if ($ebaySynchronizationTemplate->isListWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS) {
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

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE) {
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

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN) {
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

        if ($ebaySynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$ebayListingProduct->getQty();

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS) {
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

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE) {
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

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN) {
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
        if ($listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRelistable() && !$listingProduct->isHidden()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$ebaySynchronizationTemplate->isRelistMode()) {
            return false;
        }

        if ($listingProduct->isStopped() &&
            $ebaySynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isScheduleEnabled() &&
            (!$ebaySynchronizationTemplate->isScheduleIntervalNow() ||
             !$ebaySynchronizationTemplate->isScheduleWeekNow())
        ) {
            return false;
        }

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }
        //--------------------

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        // Check filters
        //--------------------
        if ($ebaySynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isRelistWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if ($ebaySynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$ebayListingProduct->getQty();

            $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }
        //--------------------

        return true;
    }

    public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isStoppable() || $listingProduct->isHidden()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($ebaySynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($ebayListingProduct->isVariationsReady()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($ebayListingProduct->isVariationsReady()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {

            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {

            $productQty = (int)$ebayListingProduct->getQty();

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        return false;
    }

    //------------------------------------

    public function isMeetReviseGeneralRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isRevisable() || $listingProduct->isHidden()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
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

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        $isMaxAppliedValueModeOn = $ebaySynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $ebaySynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        if (!$ebayListingProduct->isVariationsReady()) {

            $productQty = $ebayListingProduct->getQty();
            $channelQty = $ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold();

            //-- Check ReviseUpdateQtyMaxAppliedValue
            if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
                return false;
            }

            if ($productQty > 0 && $productQty != $channelQty) {
                return true;
            }

        } else {

            $totalQty = 0;
            $hasChange = false;

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variation->getChildObject();

                $productQty = $ebayVariation->getQty();
                $channelQty = $ebayVariation->getOnlineQty() - $ebayVariation->getOnlineQtySold();

                if ($productQty != $channelQty) {
                    //-- Check ReviseUpdateQtyMaxAppliedValue
                    (!$isMaxAppliedValueModeOn || $productQty <= $maxAppliedValue || $channelQty <= $maxAppliedValue) &&
                    $hasChange = true;
                }

                $totalQty += $productQty;
            }

            if ($totalQty > 0 && $hasChange) {
                return true;
            }
        }

        return false;
    }

    public function isMeetRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        if (!$ebayListingProduct->isVariationsReady()) {

            $needRevise = $this->checkRevisePricesRequirements(
                $ebaySynchronizationTemplate,
                $ebayListingProduct->getOnlineBuyItNowPrice(),
                $ebayListingProduct->getBuyItNowPrice()
            );

            if ($needRevise) {
                return true;
            }

            if ($ebayListingProduct->isListingTypeAuction()) {

                $needRevise = $this->checkRevisePricesRequirements(
                    $ebaySynchronizationTemplate,
                    $ebayListingProduct->getOnlineStartPrice(),
                    $ebayListingProduct->getStartPrice()
                );

                if ($needRevise) {
                    return true;
                }

                $needRevise = $this->checkRevisePricesRequirements(
                    $ebaySynchronizationTemplate,
                    $ebayListingProduct->getOnlineReservePrice(),
                    $ebayListingProduct->getReservePrice()
                );

                if ($needRevise) {
                    return true;
                }
            }

        } else {

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variation->getChildObject();

                $needRevise = $this->checkRevisePricesRequirements(
                    $ebaySynchronizationTemplate,
                    $ebayVariation->getOnlinePrice(),
                    $ebayVariation->getPrice()
                );

                if ($needRevise) {
                    return true;
                }
            }
        }

        return false;
    }

    // -----------------------------------

    public function isMeetReviseTitleRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySynchronizationTemplate()->isReviseWhenChangeTitle()) {
            return false;
        }

        return true;
    }

    public function isMeetReviseVariationTitleRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySynchronizationTemplate()->isReviseWhenChangeTitle()) {
            return false;
        }

        if (!$listingProduct->getMagentoProduct()->isBundleType() &&
            !$listingProduct->getMagentoProduct()->isGroupedType()
        ) {
            return false;
        }

        return true;
    }

    public function isMeetReviseSubTitleRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySynchronizationTemplate()->isReviseWhenChangeSubTitle()) {
            return false;
        }

        return true;
    }

    public function isMeetReviseDescriptionRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySynchronizationTemplate()->isReviseWhenChangeDescription()) {
            return false;
        }

        return true;
    }

    public function isMeetReviseImagesRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySynchronizationTemplate()->isReviseWhenChangeImages()) {
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

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $synchronizationTemplate = $ebayListingProduct->getSynchronizationTemplate();
        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        foreach ($reasons as $reason) {

            if ($reason == 'otherCategoryTemplate') {
                $reason = 'categoryTemplate';
            }

            $method = 'isRevise'.ucfirst($reason);

            if (method_exists($synchronizationTemplate, $method)) {
                if ($synchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }

            if (method_exists($ebaySynchronizationTemplate, $method)) {
                if ($ebaySynchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }
        }

        return false;
    }

    //####################################

    private function checkRevisePricesRequirements(
        Ess_M2ePro_Model_Ebay_Template_Synchronization $ebaySynchronizationTemplate,
        $onlinePrice, $productPrice
    ) {
        if ((float)$onlinePrice == (float)$productPrice) {
            return false;
        }

        if ((float)$onlinePrice <= 0) {
            return true;
        }

        if ($ebaySynchronizationTemplate->isReviseUpdatePriceMaxAllowedDeviationModeOff()) {
            return true;
        }

        $deviation = abs($onlinePrice - $productPrice) / $onlinePrice * 100;

        return $deviation > $ebaySynchronizationTemplate->getReviseUpdatePriceMaxAllowedDeviation();
    }

    //####################################
}