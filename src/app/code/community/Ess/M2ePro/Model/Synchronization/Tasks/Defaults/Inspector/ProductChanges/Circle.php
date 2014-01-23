<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_Inspector_ProductChanges_Circle
    extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 65;
    const PERCENTS_END = 85;
    const PERCENTS_INTERVAL = 20;

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

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Product Changes');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Product Changes" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Product Changes" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->prepareBaseValues();

        $listingsProducts = $this->getNextListingsProducts();

        if (count($listingsProducts) <= 0) {

            $lastTime = strtotime($this->getLastTimeStartCircle());
            $interval = $this->getMinIntervalBetweenCircles();

            if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
                return;
            }

            $this->setLastListingProductId(0);
            $this->resetLastTimeStartCircle();

            $listingsProducts = $this->getNextListingsProducts();

            if (count($listingsProducts) <= 0) {
                return;
            }
        }

        $tempIndex = 0;
        $totalItems = count($listingsProducts);

        foreach ($listingsProducts as $listingProduct) {

            $this->updateListingsProductChange($listingProduct);

            if ((++$tempIndex)%20 == 0) {
                $percentsPerOneItem = self::PERCENTS_INTERVAL/$totalItems;
                $this->_lockItem->setPercents($percentsPerOneItem*$tempIndex);
                $this->_lockItem->activate();
            }
        }

        $listingProduct = array_pop($listingsProducts);
        $this->setLastListingProductId($listingProduct->getId());
    }

    //####################################

    private function prepareBaseValues()
    {
        if (is_null($this->getLastListingProductId())) {
            $this->setLastListingProductId(0);
        }

        if (is_null($this->getLastTimeStartCircle())) {
            $this->resetLastTimeStartCircle();
        }
    }

    // ------------------------------------

    private function getMinIntervalBetweenCircles()
    {
        return (int)Mage::helper('M2ePro/Module')
                    ->getSynchronizationConfig()->getGroupValue('/defaults/inspector/product_changes/circle',
                                                                'min_interval_between_circles');
    }

    private function getMaxCountTimesForFullCircle()
    {
        return (int)Mage::helper('M2ePro/Module')
                    ->getSynchronizationConfig()->getGroupValue('/defaults/inspector/product_changes/circle',
                                                                'max_count_times_for_full_circle');
    }

    // ------------------------------------

    private function getMinCountItemsPerOneTime()
    {
        return (int)Mage::helper('M2ePro/Module')
                    ->getSynchronizationConfig()->getGroupValue('/defaults/inspector/product_changes/circle',
                                                                'min_count_items_per_one_time');
    }

    private function getMaxCountItemsPerOneTime()
    {
        return (int)Mage::helper('M2ePro/Module')
                    ->getSynchronizationConfig()->getGroupValue('/defaults/inspector/product_changes/circle',
                                                                'max_count_items_per_one_time');
    }

    // ------------------------------------

    private function getLastListingProductId()
    {
        return Mage::helper('M2ePro/Module')
                ->getSynchronizationConfig()->getGroupValue('/defaults/inspector/product_changes/circle',
                                                            'last_listing_product_id');
    }

    private function setLastListingProductId($listingProductId)
    {
        Mage::helper('M2ePro/Module')
            ->getSynchronizationConfig()->setGroupValue('/defaults/inspector/product_changes/circle',
                                                        'last_listing_product_id',
                                                        (int)$listingProductId);
    }

    // ------------------------------------

    private function getLastTimeStartCircle()
    {
        return Mage::helper('M2ePro/Module')
                   ->getSynchronizationConfig()->getGroupValue('/defaults/inspector/product_changes/circle',
                                                               'last_time_start_circle');
    }

    private function resetLastTimeStartCircle()
    {
        Mage::helper('M2ePro/Module')
            ->getSynchronizationConfig()->setGroupValue('/defaults/inspector/product_changes/circle',
                                                        'last_time_start_circle',
                                                        Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    //####################################

    private function getCountItemsPerOneTime()
    {
        $totalCount = (int)Mage::getModel('M2ePro/Listing_Product')->getCollection()->getSize();
        $perOneTime = (int)($totalCount / $this->getMaxCountTimesForFullCircle());

        if ($perOneTime < $this->getMinCountItemsPerOneTime()) {
            $perOneTime = $this->getMinCountItemsPerOneTime();
        }

        if ($perOneTime > $this->getMaxCountItemsPerOneTime()) {
            $perOneTime = $this->getMaxCountItemsPerOneTime();
        }

        return $perOneTime;
    }

    private function getNextListingsProducts()
    {
        $countOfProductChanges = Mage::getModel('M2ePro/ProductChange')->getCollection()->getSize();

        $productChangeMaxPerOneTime = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $limit = min(array($this->getCountItemsPerOneTime(),$productChangeMaxPerOneTime)) - $countOfProductChanges;

        if ($limit <= 0) {
            return array();
        }

        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->getSelect()
                   ->where("`id` > ".(int)$this->getLastListingProductId())
                   ->order(array('id ASC'))
                   ->limit($limit);

        return $collection->getItems();
    }

    //####################################

    private function updateListingsProductChange(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        Mage::getModel('M2ePro/ProductChange')
                    ->addUpdateAction( $listingProduct->getProductId(),
                                        Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION );

        $variations = $listingProduct->getVariations(true);
        foreach ($variations as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $options = $variation->getOptions(true);
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                Mage::getModel('M2ePro/ProductChange')
                        ->addUpdateAction( $option->getProductId(),
                                            Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION );
            }
        }
    }

    //####################################
}