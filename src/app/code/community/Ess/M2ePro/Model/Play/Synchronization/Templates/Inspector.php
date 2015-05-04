<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Templates_Inspector
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

        if ($listingProduct->isLockedObject('in_action')) {
           return false;
        }

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();

        if (!$playListingProduct->getGeneralId()) {
            $searchGeneralId = $playListingProduct->getListingSource()->getSearchGeneralId();
            if (empty($searchGeneralId)) {
                return false;
            }
        }

        $playSynchronizationTemplate = $playListingProduct->getPlaySynchronizationTemplate();

        if (!$playSynchronizationTemplate->isListMode()) {
            return false;
        }

        $variationManager = $playListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct() && !$variationManager->isVariationProductMatched()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        $additionalData = $listingProduct->getAdditionalData();

        if ($playSynchronizationTemplate->isListStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product was not automatically listed according to the List Rules in Synchronization Policy.
                     Status of Magento Product is Disabled (%date%) though in Synchronization Rules “Product Status”
                     is set to Enabled.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
                         Status of Magento Product Variation is Disabled though in Synchronization Rules
                         “Product Status“ is set to Enabled.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($playSynchronizationTemplate->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                    'Product was not automatically listed according to the List Rules in Synchronization Policy.
                     Stock Availability of Magento Product is Out of Stock though in
                     Synchronization Rules “Stock Availability” is set to In Stock.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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

        if ($playSynchronizationTemplate->isListWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$playListingProduct->getQty(true);

            $typeQty = (int)$playSynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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

        if ($playSynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$playListingProduct->getQty(false);

            $typeQty = (int)$playSynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    $note = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                        'Product was not automatically listed according to the List Rules in Synchronization Policy.
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
        if (!$listingProduct->isStopped()) {
            return false;
        }

        if (!$listingProduct->isRelistable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();

        $playSynchronizationTemplate = $playListingProduct->getPlaySynchronizationTemplate();

        if (!$playSynchronizationTemplate->isRelistMode()) {
            return false;
        }

        $variationManager = $playListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct() && !$variationManager->isVariationProductMatched()) {
            return false;
        }

        if ($playSynchronizationTemplate->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($playSynchronizationTemplate->isRelistStatusEnabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($playSynchronizationTemplate->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return false;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return false;
                }
            }
        }

        if ($playSynchronizationTemplate->isRelistWhenQtyMagentoHasValue()) {

            $result = false;
            $productQty = (int)$playListingProduct->getQty(true);

            $typeQty = (int)$playSynchronizationTemplate->getRelistWhenQtyMagentoHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getRelistWhenQtyMagentoHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getRelistWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }

        if ($playSynchronizationTemplate->isRelistWhenQtyCalculatedHasValue()) {

            $result = false;
            $productQty = (int)$playListingProduct->getQty(false);

            $typeQty = (int)$playSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getRelistWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::RELIST_QTY_BETWEEN &&
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
        if (!$listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();
        $playSynchronizationTemplate = $playListingProduct->getPlaySynchronizationTemplate();
        $variationManager = $playListingProduct->getVariationManager();
        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($playSynchronizationTemplate->isStopStatusDisabled()) {

            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($playSynchronizationTemplate->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if($variationManager->isVariationProduct() && $variationManager->isVariationProductMatched()) {

                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if (!is_null($temp) && $temp) {
                    return true;
                }
            }
        }

        if ($playSynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {

            $productQty = (int)$playListingProduct->getQty(true);

            $typeQty = (int)$playSynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($playSynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {

            $productQty = (int)$playListingProduct->getQty(false);

            $typeQty = (int)$playSynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$playSynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$playSynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Play_Template_Synchronization::STOP_QTY_BETWEEN &&
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

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($listingProduct->isLockedObject('in_action')) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();

        $variationManager = $playListingProduct->getVariationManager();
        if ($variationManager->isVariationProduct() && !$variationManager->isVariationProductMatched()) {
            return false;
        }

        return true;
    }

    //------------------------------------

    public function isMeetReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();

        $playSynchronizationTemplate = $playListingProduct->getPlaySynchronizationTemplate();

        if (!$playSynchronizationTemplate->isReviseWhenChangeQty()) {
            return false;
        }

        $isMaxAppliedValueModeOn = $playSynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $playSynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $playListingProduct->getQty();
        $channelQty = $playListingProduct->getOnlineQty();

        //-- Check ReviseUpdateQtyMaxAppliedValue
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

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();

        $playSynchronizationTemplate = $playListingProduct->getPlaySynchronizationTemplate();

        if (!$playSynchronizationTemplate->isReviseWhenChangePrice()) {
            return false;
        }

        $onlinePriceGbr  = $playListingProduct->getOnlinePriceGbr();
        $currentPriceGbr = $playListingProduct->getPriceGbr(true);

        if ($currentPriceGbr != $onlinePriceGbr) {
            return true;
        }

        $onlinePriceEuro  = $playListingProduct->getOnlinePriceEuro();
        $currentPriceEuro = $playListingProduct->getPriceEuro(true);

        if ($onlinePriceEuro != $currentPriceEuro) {
            return true;
        }

        return false;
    }

    //------------------------------------

    public function isMeetReviseSynchReasonsRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $reasons = $listingProduct->getSynchReasons();
        if (empty($reasons)) {
            return false;
        }

        if (!$this->isMeetReviseGeneralRequirements($listingProduct)) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Play_Listing_Product $playListingProduct */
        $playListingProduct = $listingProduct->getChildObject();

        $synchronizationTemplate = $playListingProduct->getSynchronizationTemplate();
        $playSynchronizationTemplate = $playListingProduct->getPlaySynchronizationTemplate();

        foreach ($reasons as $reason) {

            $method = 'isRevise'.ucfirst($reason);

            if (method_exists($synchronizationTemplate, $method)) {
                if ($synchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }

            if (method_exists($playSynchronizationTemplate, $method)) {
                if ($playSynchronizationTemplate->$method()) {
                    return true;
                }

                continue;
            }
        }

        return false;
    }

    //####################################
}