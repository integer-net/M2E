<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Other_Moving
{
    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    protected $tempObjectsCache = array();

    // ########################################

    public function initialize(Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
        $this->tempObjectsCache = array();
    }

    // ########################################

    public function autoMoveOtherListingsProducts(array $otherListings)
    {
        $otherListingsFiltered = array();

        foreach ($otherListings as $otherListing) {

            if (!($otherListing instanceof Ess_M2ePro_Model_Listing_Other)) {
                continue;
            }

            /** @var $otherListing Ess_M2ePro_Model_Listing_Other */

            $otherListingsFiltered[] = $otherListing;
        }

        if (count($otherListingsFiltered) <= 0) {
            return false;
        }

        $sortedItems = array();

        /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
        foreach ($otherListingsFiltered as $otherListing) {
            $sortedItems[$otherListing->getAccountId()][] = $otherListing;
        }

        $result = true;

        foreach ($sortedItems as $otherListings) {
            foreach ($otherListings as $otherListing) {
                /** @var $otherListing Ess_M2ePro_Model_Listing_Other */
                $temp = $this->autoMoveOtherListingProduct($otherListing);
                $temp === false && $result = false;
            }
        }

        return $result;
    }

    public function autoMoveOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $this->setAccountByOtherListingProduct($otherListing);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
            return false;
        }

        $listing = $this->getDefaultListing($otherListing);

        if (!($listing instanceof Ess_M2ePro_Model_Listing)) {
            return false;
        }

        $listingProduct = $listing->addProduct($otherListing->getProductId());

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $dataForUpdate = array(
            'general_id' => $otherListing->getChildObject()->getGeneralId(),
            'sku' => $otherListing->getChildObject()->getSku(),
            'online_price' => $otherListing->getChildObject()->getOnlinePrice(),
            'online_qty' => $otherListing->getChildObject()->getOnlineQty(),
            'is_afn_channel' => (int)$otherListing->getChildObject()->isAfnChannel(),
            'is_isbn_general_id' => (int)$otherListing->getChildObject()->isIsbnGeneralId(),
            'start_date' => $otherListing->getChildObject()->getStartDate(),
            'end_date' => $otherListing->getChildObject()->getEndDate(),
            'status' => $otherListing->getStatus(),
            'status_changer' => $otherListing->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate)->save();

        // Set listing store id to Amazon Item
        //---------------------------------
        $itemsCollection = Mage::getModel('M2ePro/Amazon_Item')->getCollection();

        $itemsCollection->addFieldToFilter(
            'account_id', $otherListing->getAccountId()
        );
        $itemsCollection->addFieldToFilter(
            'marketplace_id', $otherListing->getMarketplaceId()
        );
        $itemsCollection->addFieldToFilter(
            'sku', $otherListing->getChildObject()->getSku()
        );
        $itemsCollection->addFieldToFilter(
            'product_id', $otherListing->getProductId()
        );

        if ($itemsCollection->getSize() > 0) {
            $itemsCollection->getFirstItem()->setData('store_id', $listing->getStoreId())->save();
        } else {
            $dataForAdd = array(
                'account_id' => $otherListing->getAccountId(),
                'marketplace_id' => $otherListing->getMarketplaceId(),
                'sku' => $otherListing->getChildObject()->getSku(),
                'product_id' => $otherListing->getProductId(),
                'store_id' => $listing->getStoreId()
            );
            Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();
        }
        //---------------------------------

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $logModel->addProductMessage($otherListing->getId(),
                                     Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Other_Log::ACTION_MOVE_LISTING,
                                     // M2ePro_TRANSLATIONS
                                     // Item was successfully moved
                                     'Item was successfully moved',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $tempLog->addProductMessage( $listingProduct->getListingId(),
                                     $otherListing->getProductId(),
                                     $listingProduct->getId(),
                                     Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_FROM_OTHER_LISTING,
                                     // M2ePro_TRANSLATIONS
                                     // Item was successfully moved
                                     'Item was successfully moved',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeNone()) {
            Mage::getModel('M2ePro/ProductChange')
                ->addUpdateAction( $otherListing->getProductId(),
                                   Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION );
        }

        $otherListing->deleteInstance();

        return true;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Listing
     */
    public function getDefaultListing(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $accountId = $this->getAccount()->getId();

        if (isset($this->tempObjectsCache['listing_'.$accountId])) {
            return $this->tempObjectsCache['listing_'.$accountId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing');
        $tempCollection->addFieldToFilter('main_table.title',
                                          'Default ('.$this->getAccount()
                                                           ->getTitle().' - '.$this->getMarketplace()
                                                                                   ->getTitle().')');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['listing_'.$accountId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing');

        $dataForAdd = array(
            'title' => 'Default ('.$this->getAccount()->getTitle().' - '.$this->getMarketplace()->getTitle().')',
            'store_id' => $otherListing->getChildObject()->getRelatedStoreId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'account_id' => $accountId,

            'template_selling_format_id'  => $this->getDefaultSellingFormatTemplate($otherListing)->getId(),
            'template_synchronization_id' => $this->getDefaultSynchronizationTemplate($otherListing)->getId(),

            'source_products' => Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CUSTOM,
            'categories_add_action' => Ess_M2ePro_Model_Listing::CATEGORIES_ADD_ACTION_NONE,
            'categories_delete_action' => Ess_M2ePro_Model_Listing::CATEGORIES_DELETE_ACTION_NONE,

            'sku_mode' => Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_NOT_SET,
            'generate_sku_mode' => Ess_M2ePro_Model_Amazon_Listing::GENERATE_SKU_MODE_NO,
            'general_id_mode' => Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_NOT_SET,
            'worldwide_id_mode' => Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_NOT_SET,
            'search_by_magento_title_mode' =>
                Ess_M2ePro_Model_Amazon_Listing::SEARCH_BY_MAGENTO_TITLE_MODE_YES,
            'handling_time_mode' => Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_NONE,
            'handling_time_value' => 1,
            'restock_date_mode' => Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_NONE,
            'condition_mode' => Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_NOT_SET
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['listing_'.$accountId] = $tempModel;

        $attributesSets = Mage::helper('M2ePro/Magento_AttributeSet')->getAll();
        foreach ($attributesSets as $attributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_LISTING,
                'object_id' => (int)$tempModel->getId(),
                'attribute_set_id' => (int)$attributeSet['attribute_set_id']
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

        return $tempModel;
    }

    //-----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    protected function getDefaultSynchronizationTemplate(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $marketplaceId = $this->getMarketplace()->getId();

        if (isset($this->tempObjectsCache['synchronization_'.$marketplaceId])) {
            return $this->tempObjectsCache['synchronization_'.$marketplaceId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Synchronization');
        $tempCollection->addFieldToFilter('main_table.title','Default ('.$this->getMarketplace()->getTitle().')');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['synchronization_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_Synchronization');

        $dataForAdd = array(
            'title' => 'Default ('.$this->getMarketplace()->getTitle().')',
            'list_mode' => Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_MODE_NONE,
            'list_status_enabled' => Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_STATUS_ENABLED_YES,
            'list_is_in_stock' => Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_IS_IN_STOCK_YES,
            'list_qty' => Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_QTY_NONE,
            'list_qty_value' => 1,
            'list_qty_value_max' => 10,
            'relist_mode' => Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_MODE_NONE,
            'relist_filter_user_lock' => Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_FILTER_USER_LOCK_YES,
            'relist_status_enabled' => Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_STATUS_ENABLED_YES,
            'relist_is_in_stock' => Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_IS_IN_STOCK_YES,
            'relist_qty' => Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_NONE,
            'relist_qty_value' => 1,
            'relist_qty_value_max' => 10,
            'revise_update_qty' => Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_UPDATE_QTY_NONE,
            'revise_update_price' => Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_UPDATE_PRICE_NONE,
            'revise_change_selling_format_template' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE,
            'revise_change_listing' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_LISTING_NONE,
            'stop_status_disabled' => Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_STATUS_DISABLED_NONE,
            'stop_out_off_stock' => Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_OUT_OFF_STOCK_NONE,
            'stop_qty' => Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_NONE,
            'stop_qty_value' => 0,
            'stop_qty_value_max' => 10
        );

        if ($this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModePrice() ||
            $this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeAll()
        ) {
            $dataForAdd['revise_update_price'] =
                Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_UPDATE_PRICE_YES;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeQty() ||
            $this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeAll()
        ) {
            $dataForAdd['revise_update_qty'] = Ess_M2ePro_Model_Amazon_Template_Synchronization::REVISE_UPDATE_QTY_YES;
            $dataForAdd['relist_mode'] = Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_MODE_YES;
            $dataForAdd['stop_status_disabled'] =
                Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_STATUS_DISABLED_YES;
            $dataForAdd['stop_out_off_stock'] =
                Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_OUT_OFF_STOCK_YES;
        }

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['synchronization_'.$marketplaceId] = $tempModel;

        return $tempModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    protected function getDefaultSellingFormatTemplate(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $marketplaceId = $this->getMarketplace()->getId();

        if (isset($this->tempObjectsCache['selling_format_'.$marketplaceId])) {
            return $this->tempObjectsCache['selling_format_'.$marketplaceId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_SellingFormat');
        $tempCollection->addFieldToFilter('main_table.title','Default ('.$this->getMarketplace()->getTitle().')');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['selling_format_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_SellingFormat');

        $dataForAdd = array(
            'title' => 'Default ('.$this->getMarketplace()->getTitle().')',

            'qty_mode' => Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,

            'currency' => $this->getMarketplace()->getChildObject()->getDefaultCurrency(),
            'price_mode' => Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT,

            'price_variation_mode' => Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT,

            'sale_price_mode' => Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_NOT_SET,
            'sale_price_start_date_mode' => Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_VALUE,
            'sale_price_start_date_value' => Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d'),
            'sale_price_end_date_mode' => Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_VALUE,
            'sale_price_end_date_value' => Mage::helper('M2ePro')->getCurrentGmtDate(false, 'Y-m-d')
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['selling_format_'.$marketplaceId] = $tempModel;

        $attributesSets = Mage::helper('M2ePro/Magento_AttributeSet')->getAll();
        foreach ($attributesSets as $attributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT,
                'object_id' => (int)$tempModel->getId(),
                'attribute_set_id' => (int)$attributeSet['attribute_set_id']
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

        return $tempModel;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    //-----------------------------------------

    protected function setAccountByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (!is_null($this->account) && $this->account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->account = Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Account',$otherListing->getAccountId()
        );
    }

    // ########################################
}