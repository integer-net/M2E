<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Play_Listing_Product extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    const SEARCH_SETTINGS_STATUS_NOT_FOUND       = 1;
    const SEARCH_SETTINGS_STATUS_ACTION_REQUIRED = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Play_Listing_Product_Variation_Manager
     */
    protected $variationManager = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Listing_Product');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Account
     */
    public function getPlayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Marketplace
     */
    public function getPlayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing
     */
    public function getPlayListing()
    {
        return $this->getListing()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Source
     */
    public function getListingSource()
    {
        return $this->getPlayListing()->getSource($this->getActualMagentoProduct());
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getPlayListing()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_SellingFormat
     */
    public function getPlaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getPlayListing()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Template_Synchronization
     */
    public function getPlaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getActualMagentoProduct()
    {
        if (!$this->getVariationManager()->isVariationProduct() ||
            !$this->getVariationManager()->isVariationProductMatched()
        ) {
            return $this->getMagentoProduct();
        }

        if ($this->getMagentoProduct()->isConfigurableType() ||
            $this->getMagentoProduct()->isGroupedType()) {

            $variations = $this->getVariations(true);
            $variation  = reset($variations);
            $options    = $variation->getOptions(true);
            $option     = reset($options);

            return $option->getMagentoProduct();
        }

        return $this->getMagentoProduct();
    }

    // ########################################

    public function getPlayItem()
    {
        return Mage::getModel('M2ePro/Play_Item')->getCollection()
                        ->addFieldToFilter('account_id', $this->getListing()->getAccountId())
                        ->addFieldToFilter('marketplace_id', $this->getListing()->getMarketplaceId())
                        ->addFieldToFilter('sku', $this->getSku())
                        ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                        ->getFirstItem();
    }

    public function getVariationManager()
    {
        if (is_null($this->variationManager)) {
            $this->variationManager = Mage::getModel('M2ePro/Play_Listing_Product_Variation_Manager');
            $this->variationManager->setListingProduct($this->getParentObject());
        }

        return $this->variationManager;
    }

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    // ########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    //-----------------------------------------

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    public function getGeneralIdType()
    {
        return $this->getData('general_id_type');
    }

    //-----------------------------------------

    public function getPlayListingId()
    {
        return (int)$this->getData('play_listing_id');
    }

    public function getLinkInfo()
    {
        return $this->getData('link_info');
    }

    //-----------------------------------------

    public function getDispatchTo()
    {
        return $this->getData('dispatch_to');
    }

    public function getDispatchFrom()
    {
        return $this->getData('dispatch_from');
    }

    //-----------------------------------------

    public function getOnlinePriceGbr()
    {
        return (float)$this->getData('online_price_gbr');
    }

    public function getOnlinePriceEuro()
    {
        return (float)$this->getData('online_price_euro');
    }

    //-----------------------------------------

    public function getOnlineShippingPriceGbr()
    {
        return (float)$this->getData('online_shipping_price_gbr');
    }

    public function getOnlineShippingPriceEuro()
    {
        return (float)$this->getData('online_shipping_price_euro');
    }

    //-----------------------------------------

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function getCondition()
    {
        return $this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    //-----------------------------------------

    public function isIgnoreNextInventorySynch()
    {
        return (bool)$this->getData('ignore_next_inventory_synch');
    }

    // ########################################

    public function getSearchSettingsStatus()
    {
        return $this->getData('search_settings_status');
    }

    public function getSearchSettingsData()
    {
        return $this->getSettings('search_settings_data');
    }

    //-----------------------------------------

    public function getGeneralIdSearchInfo()
    {
        return $this->getSettings('general_id_search_info');
    }

    // ########################################

    public function getPriceGbr($includeShippingPrice = true)
    {
        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPriceGbr($includeShippingPrice);
        }

        $src = $this->getPlaySellingFormatTemplate()->getPriceGbrSource();
        $price = $this->getCalculatedPrice($src, Ess_M2ePro_Helper_Component_Play::CURRENCY_GBP, true);

        if ($includeShippingPrice) {
            $price += $this->getListingSource()->getShippingPriceGbr();
        }

        return round($price,2);
    }

    public function getPriceEuro($includeShippingPrice = true)
    {
        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPriceEuro($includeShippingPrice);
        }

        $src = $this->getPlaySellingFormatTemplate()->getPriceEuroSource();
        $price = $this->getCalculatedPrice($src, Ess_M2ePro_Helper_Component_Play::CURRENCY_EUR, true);

        if ($includeShippingPrice) {
            $price += $this->getListingSource()->getShippingPriceEuro();
        }

        return round($price,2);
    }

    // ----------------------------------------

    private function getCalculatedPrice($src, $currency, $modifyByCoefficient = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Play_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Play_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setCurrency($currency)->setModifyByCoefficient($modifyByCoefficient);

        return $calculator->getProductValue();
    }

    // ########################################

    public function getQty($magentoMode = false)
    {
        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getQty($magentoMode);
        }

        /** @var $calculator Ess_M2ePro_Model_Play_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Play_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getParentObject());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getProductValue();
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Play_Product_Dispatcher');
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getListing()->getTrackingAttributes();
    }

    // ########################################
}