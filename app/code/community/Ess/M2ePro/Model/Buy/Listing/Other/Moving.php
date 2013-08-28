<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Other_Moving
{
    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    protected $tempObjectsCache = array();

    // ########################################

    public function initialize(Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->marketplace = $marketplace;
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

        $accountsMarketplaces = array();

        foreach ($otherListingsFiltered as $otherListing) {

            /** @var $otherListing Ess_M2ePro_Model_Listing_Other */

            $identifier = $otherListing->getAccountId().'_'.$otherListing->getMarketplaceId();

            if (!isset($accountsMarketplaces[$identifier])) {
                $accountsMarketplaces[$identifier] = array();
            }

            $accountsMarketplaces[$identifier][] = $otherListing;
        }

        $result = true;

        foreach ($accountsMarketplaces as $otherListings) {
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
        $this->setMarketplaceByOtherListingProduct($otherListing);

        if (!$this->getAccount()->getChildObject()->isOtherListingsMoveToListingsEnabled()) {
            return false;
        }

        $listing = $this->getDefaultListing($otherListing);

        if (!($listing instanceof Ess_M2ePro_Model_Listing)) {
            return false;
        }

        // todo
        if ($listing->hasProduct($otherListing->getProductId())) {
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
            'condition' => $otherListing->getChildObject()->getCondition(),
            'condition_note' => $otherListing->getChildObject()->getConditionNote(),
            'shipping_standard_rate' => $otherListing->getChildObject()->getShippingStandardRate(),
            'shipping_expedited_mode' => $otherListing->getChildObject()->getShippingExpeditedMode(),
            'shipping_expedited_rate' => $otherListing->getChildObject()->getShippingExpeditedRate(),
            'start_date' => NULL,
            'end_date' => $otherListing->getChildObject()->getEndDate(),
            'status' => $otherListing->getStatus(),
            'status_changer' => $otherListing->getStatusChanger()
        );

        $dataForUpdate['condition_note'] == '' && $dataForUpdate['condition_note'] = new Zend_Db_Expr("''");

        $listingProduct->addData($dataForUpdate)->save();

        // Set listing store id to Buy Item
        //---------------------------------
        $itemsCollection = Mage::getModel('M2ePro/Buy_Item')->getCollection();

        $itemsCollection->addFieldToFilter(
            'account_id', $otherListing->getChildObject()->getAccountId()
        );
        $itemsCollection->addFieldToFilter(
            'marketplace_id', $otherListing->getChildObject()->getMarketplaceId()
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
                'account_id' => $otherListing->getChildObject()->getAccountId(),
                'marketplace_id' => $otherListing->getChildObject()->getMarketplaceId(),
                'sku' => $otherListing->getChildObject()->getSku(),
                'product_id' => $otherListing->getProductId(),
                'store_id' => $listing->getStoreId()
            );
            Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save();
        }
        //---------------------------------

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $logModel->addProductMessage($otherListing->getId(),
                                     Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Other_Log::ACTION_MOVE_LISTING,
                                     // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully moved');
                                     'Item was successfully moved',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $tempLog->addProductMessage( $listingProduct->getListingId(),
                                     $otherListing->getProductId(),
                                     $listingProduct->getId(),
                                     Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_FROM_OTHER_LISTING,
                                     // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully moved');
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
        $marketplaceId = $this->getMarketplace()->getId();

        if (isset($this->tempObjectsCache['listing_'.$accountId.'_'.$marketplaceId])) {
            return $this->tempObjectsCache['listing_'.$accountId.'_'.$marketplaceId];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing');
        $tempCollection->addFieldToFilter('main_table.title',
                                          'Default ('.$this->getAccount()->getTitle().')');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['listing_'.$accountId.'_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Listing');

        $dataForAdd = array(
            'title' => 'Default ('.$this->getAccount()->getTitle().')',
            'store_id' => $otherListing->getChildObject()->getRelatedStoreId(),

            'template_general_id'         => $this->createDefaultGeneralTemplate($otherListing)->getId(),
            'template_selling_format_id'  => $this->getDefaultSellingFormatTemplate($otherListing)->getId(),
            'template_description_id'     => $this->getDefaultDescriptionTemplate($otherListing)->getId(),
            'template_synchronization_id' => $this->getDefaultSynchronizationTemplate($otherListing)->getId(),

            'synchronization_start_type' => Ess_M2ePro_Model_Listing::SYNCHRONIZATION_START_TYPE_IMMEDIATELY,
            'synchronization_start_through_metric'=>Ess_M2ePro_Model_Listing::SYNCHRONIZATION_START_THROUGH_METRIC_DAYS,
            'synchronization_start_through_value' => 1,
            'synchronization_start_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),

            'synchronization_stop_type' => Ess_M2ePro_Model_Listing::SYNCHRONIZATION_STOP_TYPE_NEVER,
            'synchronization_stop_through_metric' => Ess_M2ePro_Model_Listing::SYNCHRONIZATION_STOP_THROUGH_METRIC_DAYS,
            'synchronization_stop_through_value' => 1,
            'synchronization_stop_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),

            'source_products' => Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CUSTOM,
            'categories_add_action' => Ess_M2ePro_Model_Listing::CATEGORIES_ADD_ACTION_NONE,
            'categories_delete_action' => Ess_M2ePro_Model_Listing::CATEGORIES_DELETE_ACTION_NONE,
            'hide_products_others_listings' => Ess_M2ePro_Model_Listing::HIDE_PRODUCTS_OTHERS_LISTINGS_NO
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['listing_'.$accountId.'_'.$marketplaceId] = $tempModel;

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
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
     * @return Ess_M2ePro_Model_Template_General
     */
    protected function createDefaultGeneralTemplate(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        $accountId = $this->getAccount()->getId();
        $marketplaceId = $this->getMarketplace()->getId();

        if (isset($this->tempObjectsCache['general_'.$accountId.'_'.$marketplaceId])) {
            return $this->tempObjectsCache['general_'.$accountId.'_'.$marketplaceId];
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Template_General');

        $dataForAdd = array(
            'title' => 'Default ('.$this->getAccount()->getTitle().')',
            'marketplace_id' => $marketplaceId,
            'account_id' => $accountId,
            'sku_mode' => Ess_M2ePro_Model_Buy_Template_General::SKU_MODE_NOT_SET,
            'generate_sku_mode' => Ess_M2ePro_Model_Buy_Template_General::GENERATE_SKU_MODE_NO,
            'general_id_mode' => Ess_M2ePro_Model_Buy_Template_General::GENERAL_ID_MODE_NOT_SET,
            'search_by_magento_title_mode' =>
                Ess_M2ePro_Model_Buy_Template_General::SEARCH_BY_MAGENTO_TITLE_MODE_YES,
            'condition_mode' => Ess_M2ePro_Model_Buy_Template_General::CONDITION_MODE_NOT_SET,
            'condition_note_mode' => Ess_M2ePro_Model_Buy_Template_General::CONDITION_NOTE_MODE_NOT_SET,
            'shipping_standard_mode' =>
                                    Ess_M2ePro_Model_Buy_Template_General::SHIPPING_MODE_NOT_SET,
            'shipping_expedited_mode' =>
                                    Ess_M2ePro_Model_Buy_Template_General::SHIPPING_MODE_NOT_SET,
            'shipping_one_day_mode' =>
                                    Ess_M2ePro_Model_Buy_Template_General::SHIPPING_MODE_NOT_SET,
            'shipping_two_day_mode' =>
                                    Ess_M2ePro_Model_Buy_Template_General::SHIPPING_MODE_NOT_SET
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['general_'.$accountId.'_'.$marketplaceId] = $tempModel;

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        foreach ($attributesSets as $attributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL,
                'object_id' => (int)$tempModel->getId(),
                'attribute_set_id' => (int)$attributeSet['attribute_set_id']
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

        return $tempModel;
    }

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

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Template_Synchronization');
        $tempCollection->addFieldToFilter('main_table.title','Default');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['synchronization_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Template_Synchronization');

        $dataForAdd = array(
            'title' => 'Default',
            'list_mode' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_MODE_NONE,
            'list_status_enabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_STATUS_ENABLED_YES,
            'list_is_in_stock' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_IS_IN_STOCK_YES,
            'list_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::LIST_QTY_NONE,
            'list_qty_value' => 1,
            'list_qty_value_max' => 10,
            'relist_mode' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_MODE_NONE,
            'relist_filter_user_lock' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_FILTER_USER_LOCK_YES,
            'relist_status_enabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_STATUS_ENABLED_YES,
            'relist_is_in_stock' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_IS_IN_STOCK_YES,
            'relist_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_QTY_NONE,
            'relist_qty_value' => 1,
            'relist_qty_value_max' => 10,
            'relist_schedule_type'=>Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_SCHEDULE_TYPE_IMMEDIATELY,
            'relist_schedule_through_value' => 0,
            'relist_schedule_through_metric' =>
                                Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_SCHEDULE_THROUGH_METRIC_DAYS,
            'relist_schedule_week_mo' => 0,
            'relist_schedule_week_tu' => 0,
            'relist_schedule_week_we' => 0,
            'relist_schedule_week_th' => 0,
            'relist_schedule_week_fr' => 0,
            'relist_schedule_week_sa' => 0,
            'relist_schedule_week_su' => 0,
            'relist_schedule_week_start_time' => NULL,
            'relist_schedule_week_end_time' => NULL,
            'revise_update_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_QTY_NONE,
            'revise_update_price' => Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_PRICE_NONE,
            'revise_change_selling_format_template' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE,
            'revise_change_description_template' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE,
            'revise_change_general_template' =>
                                Ess_M2ePro_Model_Template_Synchronization::REVISE_CHANGE_GENERAL_TEMPLATE_NONE,
            'stop_status_disabled' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_STATUS_DISABLED_NONE,
            'stop_out_off_stock' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_OUT_OFF_STOCK_NONE,
            'stop_qty' => Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_QTY_NONE,
            'stop_qty_value' => 0,
            'stop_qty_value_max' => 10
        );

        if ($this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModePrice() ||
            $this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeAll()
        ) {
            $dataForAdd['revise_update_price'] =
                Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_PRICE_YES;
        }

        if ($this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeQty() ||
            $this->getAccount()->getChildObject()->isOtherListingsMoveToListingsSynchModeAll()
        ) {
            $dataForAdd['revise_update_qty'] = Ess_M2ePro_Model_Buy_Template_Synchronization::REVISE_UPDATE_QTY_YES;
            $dataForAdd['relist_mode'] = Ess_M2ePro_Model_Buy_Template_Synchronization::RELIST_MODE_YES;
            $dataForAdd['stop_status_disabled'] =
                Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_STATUS_DISABLED_YES;
            $dataForAdd['stop_out_off_stock'] =
                Ess_M2ePro_Model_Buy_Template_Synchronization::STOP_OUT_OFF_STOCK_YES;
        }

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['synchronization_'.$marketplaceId] = $tempModel;

        return $tempModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Other $otherListing
     * @return Ess_M2ePro_Model_Template_Description
     */
    protected function getDefaultDescriptionTemplate(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (isset($this->tempObjectsCache['description'])) {
            return $this->tempObjectsCache['description'];
        }

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Template_Description');
        $tempCollection->addFieldToFilter('main_table.title','Default');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['description'] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Template_Description');

        $dataForAdd = array(
            'title' => 'Default'
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['description'] = $tempModel;

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        foreach ($attributesSets as $attributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION,
                'object_id' => (int)$tempModel->getId(),
                'attribute_set_id' => (int)$attributeSet['attribute_set_id']
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

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

        $tempCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Template_SellingFormat');
        $tempCollection->addFieldToFilter('main_table.title','Default');
        $tempItem = $tempCollection->getFirstItem();

        if (!is_null($tempItem->getId())) {
            $this->tempObjectsCache['selling_format_'.$marketplaceId] = $tempItem;
            return $tempItem;
        }

        $tempModel = Mage::helper('M2ePro/Component_Buy')->getModel('Template_SellingFormat');

        $dataForAdd = array(
            'title' => 'Default',

            'qty_mode' => Ess_M2ePro_Model_Buy_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,

            'price_mode' => Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_PRODUCT,
            'price_variation_mode' => Ess_M2ePro_Model_Buy_Template_SellingFormat::PRICE_VARIATION_MODE_PARENT
        );

        $tempModel->addData($dataForAdd)->save();
        $this->tempObjectsCache['selling_format_'.$marketplaceId] = $tempModel;

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
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
        return $this->marketplace;
    }

    //-----------------------------------------

    protected function setAccountByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (!is_null($this->account) && $this->account->getId() == $otherListing->getAccountId()) {
            return;
        }

        $this->account = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Account',$otherListing->getAccountId()
        );
    }

    protected function setMarketplaceByOtherListingProduct(Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (!is_null($this->marketplace) && $this->marketplace->getId() == $otherListing->getMarketplaceId()) {
            return;
        }

        $this->marketplace = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
            'Marketplace', $otherListing->getMarketplaceId()
        );
    }

    // ########################################
}