<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_List
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 10;
    const PERCENTS_END = 20;
    const PERCENTS_INTERVAL = 10;

    private $_checkedListingsProductsIds = array();

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'List Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "List" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "List" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $tasks = array(
            'immediatelyChangedProducts',
            'immediatelyNotCheckedProducts',
            'executeScheduled',
        );

        foreach ($tasks as $i => $task) {
            $this->$task();

            $this->_lockItem->setPercents(self::PERCENTS_START + ($i+1)*self::PERCENTS_INTERVAL/count($tasks));
            $this->_lockItem->activate();
        }
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was changed');

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$this->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_LIST,
                array()
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function immediatelyNotCheckedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was not checked');

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('tried_to_list',0);
        $collection->getSelect()->limit(100);

        $listingsProducts = $collection->getItems();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->enableCache();
            $listingProduct->setData('tried_to_list',1)->save();

            if (!$this->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_LIST,
                array()
            );
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeScheduled()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Execute scheduled');

        //------------------------------------
        $synchTemplates = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_Synchronization')->getItems();
        //------------------------------------

        foreach ($synchTemplates as $synchTemplate) {

            /* @var $ebaySynchTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
            $ebaySynchTemplate = $synchTemplate->getChildObject();

            if (!$ebaySynchTemplate->isScheduleEnabled()) {
                continue;
            }

            if (!$ebaySynchTemplate->isScheduleIntervalNow() ||
                !$ebaySynchTemplate->isScheduleWeekNow()) {
                continue;
            }

            $listingsProducts = array();
            $affectedListingsProductIds = NULL;

            do {

                $tempListingsProducts = $this->getNextScheduledListingsProducts($synchTemplate->getId());

                if (count($tempListingsProducts) <= 0) {
                    break;
                }

                if (is_null($affectedListingsProductIds)) {
                    $affectedListingsProductIds = $ebaySynchTemplate->getAffectedListingProducts(false,'id');
                    $affectedListingsProductIds = array_map('intval',$affectedListingsProductIds);
                    $affectedListingsProductIds = array_flip(array_unique($affectedListingsProductIds));
                }

                if (count($affectedListingsProductIds) <= 0) {
                    break;
                }

                foreach ($tempListingsProducts as $tempListingProduct) {
                    if (!isset($affectedListingsProductIds[(int)$tempListingProduct->getId()])) {
                        continue;
                    }
                    $listingsProducts[] = $tempListingProduct;
                }

            } while (count($listingsProducts) < 100);

            foreach ($listingsProducts as $listingProduct) {

                /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                $listingProduct->enableCache();

                if (!$this->isMeetListRequirements($listingProduct)) {
                    continue;
                }

                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_LIST,
                    array()
                );
            }
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function getNextScheduledListingsProducts($synchTemplateId)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/template/synchronization/'.$synchTemplateId.'/schedule/list/';

        $yearMonthDay = Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d');
        $configData = $cacheConfig->getGroupValue($cacheConfigGroup,'last_listing_product_id');

        if (is_null($configData)) {
            $configData = array();
        } else {
            $configData = json_decode($configData,true);
        }

        $lastListingProductId = 0;
        if (isset($configData[$yearMonthDay])) {
            $lastListingProductId = (int)$configData[$yearMonthDay];
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('main_table.id',array('gt'=>$lastListingProductId));
        $collection->addFieldToFilter('main_table.status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->order('main_table.id', Zend_Db_Select::SQL_ASC);
        $collection->getSelect()->limit(100);

        $lastItem = $collection->getLastItem();
        if (!$lastItem->getId()) {
            return array();
        }

        $configData = array($yearMonthDay=>$lastItem->getId());
        $cacheConfig->setGroupValue($cacheConfigGroup,'last_listing_product_id',json_encode($configData));

        return $collection->getItems();
    }

    //####################################

    private function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // eBay available status
        //--------------------
        if (!$listingProduct->isNotListed()) {
            return false;
        }

        if (!$listingProduct->isListable()) {
            return false;
        }

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_LIST,
                        array())
        ) {
            return false;
        }
        //--------------------

        /* @var $ebaySynchronizationTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
        $ebaySynchronizationTemplate = $listingProduct->getChildObject()->getEbaySynchronizationTemplate();

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getChildObject()->isSetCategoryTemplate()) {
            return false;
        }
        if (!$ebaySynchronizationTemplate->isListMode()) {
            return false;
        }

        if ($ebaySynchronizationTemplate->isScheduleEnabled()) {
            if (!$ebaySynchronizationTemplate->isScheduleIntervalNow() ||
                !$ebaySynchronizationTemplate->isScheduleWeekNow()) {
                return false;
            }
        }
        //--------------------

        $productsIdsForEachVariation = NULL;

        // Check filters
        //--------------------
        if($ebaySynchronizationTemplate->isListStatusEnabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() !=
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return false;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStatuses = $listingProduct->getVariationsStatuses($productsIdsForEachVariation);

                    // all variations are disabled
                    if ((int)min($tempStatuses) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return false;
                    }
                }
            }
        }

        if($ebaySynchronizationTemplate->isListIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return false;
            } else {

                if (is_null($productsIdsForEachVariation)) {
                    $productsIdsForEachVariation = $listingProduct->getProductsIdsForEachVariation();
                }

                if (count($productsIdsForEachVariation) > 0) {

                    $tempStocks = $listingProduct->getVariationsStockAvailabilities($productsIdsForEachVariation);

                    // all variations are out of stock
                    if (!(int)max($tempStocks)) {
                        return false;
                    }
                }
            }
        }

        if($ebaySynchronizationTemplate->isListWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQtyTotal(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN &&
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

    //####################################
}