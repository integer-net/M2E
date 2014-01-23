<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Product extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const STATUS_NOT_LISTED = 0;
    const STATUS_SOLD = 1;
    const STATUS_LISTED = 2;
    const STATUS_STOPPED = 3;
    const STATUS_FINISHED = 4;
    const STATUS_UNKNOWN = 5;
    const STATUS_BLOCKED = 6;
    const STATUS_HIDDEN = 7;

    const STATUS_CHANGER_UNKNOWN = 0;
    const STATUS_CHANGER_SYNCH = 1;
    const STATUS_CHANGER_USER = 2;
    const STATUS_CHANGER_COMPONENT = 3;
    const STATUS_CHANGER_OBSERVER = 4;

    const SYNCH_STATUS_OK    = 0;
    const SYNCH_STATUS_NEED  = 1;
    const SYNCH_STATUS_SKIP  = 2;

    // ########################################

    public $isCacheEnabled = false;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Listing
     */
    protected $listingModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        if ($this->getStatus() == self::STATUS_LISTED) {
            return true;
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $variations = $this->getVariations(true);
        foreach ($variations as $variation) {
            $variation->deleteInstance();
        }

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $tempLog->addProductMessage($this->getListingId(),
                                    $this->getProductId(),
                                    $this->getId(),
                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
                                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully deleted');
                                    'Item was successfully deleted',
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        $this->listingModel = NULL;
        $this->magentoProductModel = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        if (is_null($this->listingModel)) {
            $this->listingModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Listing',$this->getData('listing_id')
            );
        }

        return $this->listingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing $instance
     */
    public function setListing(Ess_M2ePro_Model_Listing $instance)
    {
         $this->listingModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache')
                ->setStoreId($this->getListing()->getStoreId())
                ->setProductId($this->getData('product_id'));
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
    }

    // ########################################

    public function getVariations($asObjects = false, array $filters = array())
    {
        $variations = $this->getRelatedComponentItems(
            'Listing_Product_Variation','listing_product_id',$asObjects,$filters
        );

        if ($asObjects) {
            foreach ($variations as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $variation->setListingProduct($this);
            }
        }

        return $variations;
    }

    // ########################################

    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    public function isTriedToList()
    {
        return (bool)$this->getData('tried_to_list');
    }

    //----------------------------------------

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getStatusChanger()
    {
        return (int)$this->getData('status_changer');
    }

    //----------------------------------------

    public function getAdditionalData()
    {
        $additionalData = $this->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        return is_array($additionalData) ? $additionalData : array();
    }

    //----------------------------------------

    public function getSynchStatus()
    {
        return (int)$this->getData('synch_status');
    }

    public function isSynchStatusOk()
    {
        return $this->getSynchStatus() == self::SYNCH_STATUS_OK;
    }

    public function isSynchStatusNeed()
    {
        return $this->getSynchStatus() == self::SYNCH_STATUS_NEED;
    }

    public function isSynchStatusSkip()
    {
        return $this->getSynchStatus() == self::SYNCH_STATUS_SKIP;
    }

    //----------------------------------------

    public function getSynchReasons()
    {
        $reasons = $this->getData('synch_reasons');
        $reasons = explode(',',$reasons);

        return array_unique(array_filter($reasons));
    }

    // ########################################

    public function getQty()
    {
        $qty = 0;

        if ($this->getMagentoProduct()->isStrictVariationProduct()) {
            $qty = $this->getVariationProductQty();
        } else {
            $qty = $this->getMagentoProduct()->getQty();
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    //-----------------------------------------

    protected function getVariationProductQty()
    {
        $qty = 0;

        if ($this->getMagentoProduct()->isBundleType()) {
            $qty = $this->getBundleProductQty();
        }
        if ($this->getMagentoProduct()->isGroupedType()) {
            $qty = $this->getGroupedProductQty();
        }
        if ($this->getMagentoProduct()->isConfigurableType()) {
            $qty = $this->getConfigurableProductQty();
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    protected function getConfigurableProductQty()
    {
        $totalQty = 0;
        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);

            $qty = $stockItem->getQty();

            if ($stockItem->getIsInStock() != 1 || $qty <= 0) {
                continue;
            }

            $totalQty += $qty;
        }

        $totalQty < 0 && $totalQty = 0;

        return (int)floor($totalQty);
    }

    protected function getGroupedProductQty()
    {
        $totalQty = 0;
        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getAssociatedProducts() as $childProduct) {

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);

            $qty = $stockItem->getQty();

            if ($stockItem->getIsInStock() != 1 || $qty <= 0) {
                continue;
            }

            $totalQty += $qty;
        }

        $totalQty < 0 && $totalQty = 0;

        return (int)floor($totalQty);
    }

    protected function getBundleProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();

        // Prepare bundle options format usable for search
        $productInstance = $product->getTypeInstance();
        $optionCollection = $productInstance->getOptionsCollection($product);
        $optionsData = $optionCollection->getData();

        foreach ($optionsData as $singleOption) {
            // Save QTY, before calculate = 0
            $bundleOptionsArray[$singleOption['option_id']] = 0;
        }

        $selectionsCollection = $productInstance->getSelectionsCollection($optionCollection->getAllIds(), $product);
        $_items = $selectionsCollection->getItems();

        foreach ($_items as $_item) {
            $itemInfoAsArray = $_item->toArray();
            if (isset($bundleOptionsArray[$itemInfoAsArray['option_id']])) {
                // For each option item inc total QTY
                if ($itemInfoAsArray['stock_item']['is_in_stock'] != 1) {
                    // Skip get qty for options product that not in stock
                    continue;
                }
                $addQty = $itemInfoAsArray['stock_item']['qty'];
                // Only positive
                $bundleOptionsArray[$itemInfoAsArray['option_id']] += (($addQty < 0) ? 0 : $addQty);
            }
        }

        // Get min of qty product for all options
        $minQty = -1;
        foreach ($bundleOptionsArray as $singleBundle) {
            if ($singleBundle < $minQty || $minQty == -1) {
                $minQty = $singleBundle;
            }
        }

        $minQty < 0 && $minQty = 0;

        return (int)floor($minQty);
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == self::STATUS_NOT_LISTED;
    }

    public function isUnknown()
    {
        return $this->getStatus() == self::STATUS_UNKNOWN;
    }

    public function isBlocked()
    {
        return $this->getStatus() == self::STATUS_BLOCKED;
    }

    //----------------------------------------

    public function isListed()
    {
        return $this->getStatus() == self::STATUS_LISTED;
    }

    public function isHidden()
    {
        return $this->getStatus() == self::STATUS_HIDDEN;
    }

    public function isSold()
    {
        return $this->getStatus() == self::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == self::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == self::STATUS_FINISHED;
    }

    //----------------------------------------

    public function isListable()
    {
        return ($this->isNotListed() || $this->isSold() ||
                $this->isStopped() || $this->isFinished() ||
                $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isRelistable()
    {
        return ($this->isSold() || $this->isStopped() ||
                $this->isFinished() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isRevisable()
    {
        return ($this->isListed() || $this->isHidden() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isStoppable()
    {
        return ($this->isListed() || $this->isHidden() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->getChildObject()->listAction($params);
    }

    public function relistAction(array $params = array())
    {
        return $this->getChildObject()->relistAction($params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->getChildObject()->reviseAction($params);
    }

    public function stopAction(array $params = array())
    {
        return $this->getChildObject()->stopAction($params);
    }

    public function deleteAction(array $params = array())
    {
        return $this->getChildObject()->deleteAction($params);
    }

    // ########################################

    public function getChangedItems(array $attributes,
                                    $componentMode = NULL,
                                    $withStoreFilter = false,
                                    $dbSelectModifier = NULL)
    {
        $resultsByListingProduct = $this->getChangedItemsByListingProduct($attributes,
                                                                          $componentMode,
                                                                          $withStoreFilter,
                                                                          $dbSelectModifier);
        $resultsByVariationOption = $this->getChangedItemsByVariationOption($attributes,
                                                                            $componentMode,
                                                                            $withStoreFilter,
                                                                            $dbSelectModifier);

        $finalResults = array();

        foreach ($resultsByListingProduct as $item) {
            if (isset($finalResults[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $finalResults[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        foreach ($resultsByVariationOption as $item) {
            if (isset($finalResults[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $finalResults[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($finalResults);
    }

    public function getChangedItemsByListingProduct(array $attributes,
                                                    $componentMode = NULL,
                                                    $withStoreFilter = false,
                                                    $dbSelectModifier = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $listingsTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingsProductsTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $fields = array(
            'changed_attribute'=>'attribute',
            'changed_to_value'=>'value_new',
        );

        $limit = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($productsChangesTable,'*')
                             ->order(array('id ASC'))
                             ->limit($limit);

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from(array('pc' => $dbSelect),$fields)
                             ->join(array('lp' => $listingsProductsTable),'`pc`.`product_id` = `lp`.`product_id`','id')
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        $withStoreFilter && $dbSelect->join(array('l' => $listingsTable),'`lp`.`listing_id` = `l`.`id`',array());
        $withStoreFilter && $dbSelect->where("`l`.`store_id` = `pc`.`store_id`");

        !is_null($componentMode) && $dbSelect->where("`lp`.`component_mode` = ?",(string)$componentMode);

        is_callable($dbSelectModifier) && call_user_func($dbSelectModifier,$dbSelect);

        $tempResult = Mage::getResourceModel('core/config')
                                ->getReadConnection()
                                ->fetchAll($dbSelect);

        $finalResults = array();
        foreach ($tempResult as $item) {
            if (isset($finalResults[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $finalResults[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($finalResults);
    }

    public function getChangedItemsByVariationOption(array $attributes,
                                                     $componentMode = NULL,
                                                     $withStoreFilter = false,
                                                     $dbSelectModifier = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();

        $listingsTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingsProductsTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
        $variationsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable();
        $optionsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();

        $fields = array(
            'changed_attribute'=>'attribute',
            'changed_to_value'=>'value_new',
        );

        $limit = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($productsChangesTable,'*')
                             ->order(array('id ASC'))
                             ->limit($limit);

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from(array('pc' => $dbSelect),$fields)
                             ->join(array('lpvo' => $optionsTable),'`pc`.`product_id` = `lpvo`.`product_id`',array())
                             ->join(array('lpv' => $variationsTable),
                                          '`lpvo`.`listing_product_variation_id` = `lpv`.`id`',array())
                             ->join(array('lp' => $listingsProductsTable),
                                          '`lpv`.`listing_product_id` = `lp`.`id`',array('id'))
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        $withStoreFilter && $dbSelect->join(array('l' => $listingsTable),'`lp`.`listing_id` = `l`.`id`',array());
        $withStoreFilter && $dbSelect->where("`l`.`store_id` = `pc`.`store_id`");

        !is_null($componentMode) && $dbSelect->where("`lpvo`.`component_mode` = ?",(string)$componentMode);

        is_callable($dbSelectModifier) && call_user_func($dbSelectModifier,$dbSelect);

        $tempResult = Mage::getResourceModel('core/config')
                                ->getReadConnection()
                                ->fetchAll($dbSelect);

        $finalResults = array();
        foreach ($tempResult as $item) {
            if (isset($finalResults[$item['id'].'_'.$item['changed_attribute']])) {
                continue;
            }
            $finalResults[$item['id'].'_'.$item['changed_attribute']] = $item;
        }

        return array_values($finalResults);
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getChildObject()->getTrackingAttributes();
    }

    // ########################################

    public function clearCache()
    {
        $this->getMagentoProduct()->clearCache();
        return $this;
    }

    public function enableCache()
    {
        $this->isCacheEnabled = true;
        $this->getMagentoProduct()->enableCache();
        return $this;
    }

    public function disableCache()
    {
        $this->isCacheEnabled = false;
        $this->getMagentoProduct()->disableCache();
        return $this;
    }

    // ########################################

    public function duplicate()
    {
        $duplicatedListingProduct = $this->getListing()->addProduct($this->getProductId(),false,false);

        //not for eBay hack
        if ($this->getComponentMode() == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return $duplicatedListingProduct;
        }

        if (!$this->getChildObject()->isVariationProduct() ||
            !$this->getChildObject()->isVariationMatched()) {
            return $duplicatedListingProduct;
        }

        $variations = $this->getVariations(true);
        $variation = reset($variations);

        $duplicatedListingProduct->getChildObject()->setMatchedVariation($variation->getOptions());

        return $duplicatedListingProduct;
    }

    // ########################################

    public function getProductsIdsForEachVariation()
    {
        $listingProductVariationTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')
            ->getMainTable();
        $listingProductVariationOptionTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')
            ->getMainTable();

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
                ->from(array('lpv' => $listingProductVariationTable),array('variation_id' => 'id'))
                ->join(
                    array('lpvo' => $listingProductVariationOptionTable),
                    '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                    array('product_id')
                )
                ->where('`lpv`.`listing_product_id` = ?',(int)$this->getId());

        $variationData = (array)Mage::getResourceModel('core/config')
                                            ->getReadConnection()
                                            ->fetchAll($dbSelect);

        foreach ($variationData as $key => $value) {
            $variationData[$value['variation_id']][] = $value['product_id'];
            unset($variationData[$key]);
        }

        return $variationData;
    }

    public function getVariationsStatuses($productsIdsForEachVariation = NULL)
    {
        if (is_null($productsIdsForEachVariation)) {
            $productsIdsForEachVariation = $this->getProductsIdsForEachVariation();
        }

        if (count($productsIdsForEachVariation) <= 0) {
            return array();
        }

        $ids = array();
        foreach ($productsIdsForEachVariation as $productsIds) {
            foreach ($productsIds as $id) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        $statuses = Mage::getSingleton('M2ePro/Magento_Product_Status')->getProductStatus(
            $ids, $this->getListing()->getStoreId()
        );

        $productsStatusesForEachVariation = array();
        foreach ($productsIdsForEachVariation as $key => $productsIds) {
            foreach ($productsIds as $id) {
                $productsStatusesForEachVariation[$key][] = $statuses[$id];
            }
        }

        $variationsStatuses = array();
        foreach ($productsStatusesForEachVariation as $key => $optionsStatuses) {
            $variationsStatuses[$key] = max($optionsStatuses);
        }

        return $variationsStatuses;
    }

    public function getVariationsStockAvailabilities($productsIdsForEachVariation = NULL)
    {
        if (is_null($productsIdsForEachVariation)) {
            $productsIdsForEachVariation = $this->getProductsIdsForEachVariation();
        }

        if (count($productsIdsForEachVariation) <= 0) {
            return array();
        }

        $ids = array();
        foreach ($productsIdsForEachVariation as $productsIds) {
            foreach ($productsIds as $id) {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));

        $catalogInventoryTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from(array('cisi' => $catalogInventoryTable),array('product_id','is_in_stock'))
                             ->where('cisi.product_id IN ('.implode(',',$ids).')');

        $stocks = Mage::getResourceModel('core/config')
                                            ->getReadConnection()
                                            ->fetchPairs($dbSelect);

        $productsStocksForEachVariation = array();
        foreach ($productsIdsForEachVariation as $key => $productsIds) {
            foreach ($productsIds as $id) {
                $productsStocksForEachVariation[$key][] = $stocks[$id];
            }
        }

        $variationsStocks = array();
        foreach ($productsStocksForEachVariation as $key => $optionsStatuses) {
            $variationsStocks[$key] = min($optionsStatuses);
        }

        return $variationsStocks;
    }

    // ########################################
}