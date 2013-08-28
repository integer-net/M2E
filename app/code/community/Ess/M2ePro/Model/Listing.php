<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const SYNCHRONIZATION_START_TYPE_NEVER       = 0;
    const SYNCHRONIZATION_START_TYPE_IMMEDIATELY = 1;
    const SYNCHRONIZATION_START_TYPE_THROUGH     = 2;
    const SYNCHRONIZATION_START_TYPE_DATE        = 3;

    const SYNCHRONIZATION_START_THROUGH_METRIC_NONE    = 0;
    const SYNCHRONIZATION_START_THROUGH_METRIC_MINUTES = 1;
    const SYNCHRONIZATION_START_THROUGH_METRIC_HOURS   = 2;
    const SYNCHRONIZATION_START_THROUGH_METRIC_DAYS    = 3;

    const SYNCHRONIZATION_STOP_TYPE_NEVER   = 0;
    const SYNCHRONIZATION_STOP_TYPE_THROUGH = 1;
    const SYNCHRONIZATION_STOP_TYPE_DATE    = 2;

    const SYNCHRONIZATION_STOP_THROUGH_METRIC_NONE    = 0;
    const SYNCHRONIZATION_STOP_THROUGH_METRIC_MINUTES = 1;
    const SYNCHRONIZATION_STOP_THROUGH_METRIC_HOURS   = 2;
    const SYNCHRONIZATION_STOP_THROUGH_METRIC_DAYS    = 3;

    const SYNCHRONIZATION_STATUS_INACTIVE = 0;
    const SYNCHRONIZATION_STATUS_ACTIVE   = 1;

    const SYNCHRONIZATION_ALREADY_START_NO  = 0;
    const SYNCHRONIZATION_ALREADY_START_YES = 1;

    const SYNCHRONIZATION_ALREADY_STOP_NO  = 0;
    const SYNCHRONIZATION_ALREADY_STOP_YES = 1;

    const SOURCE_PRODUCTS_CUSTOM     = 1;
    const SOURCE_PRODUCTS_CATEGORIES = 2;

    const CATEGORIES_ADD_ACTION_NONE     = 0;
    const CATEGORIES_ADD_ACTION_ADD      = 1;
    const CATEGORIES_ADD_ACTION_ADD_LIST = 2;

    const CATEGORIES_DELETE_ACTION_NONE        = 0;
    const CATEGORIES_DELETE_ACTION_STOP        = 1;
    const CATEGORIES_DELETE_ACTION_STOP_REMOVE = 2;

    const HIDE_PRODUCTS_OTHERS_LISTINGS_NO  = 0;
    const HIDE_PRODUCTS_OTHERS_LISTINGS_YES = 1;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Template_General
     */
    private $generalTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    private $descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('listing_id', $this->getId())
                            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $products = $this->getProducts(true);
        foreach ($products as $product) {
            $product->deleteInstance();
        }

        $categories = $this->getCategories(true);
        foreach ($categories as $category) {
            $category->deleteInstance();
        }

        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            $attributeSet->deleteInstance();
        }

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $tempLog->addListingMessage( $this->getId(),
                                     Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_LISTING,
                                     // Parser hack -> Mage::helper('M2ePro')->__('Listing was successfully deleted');
                                     'Listing was successfully deleted',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH );

        $this->generalTemplateModel = NULL;
        $this->sellingFormatTemplateModel = NULL;
        $this->descriptionTemplateModel = NULL;
        $this->synchronizationTemplateModel = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        if (is_null($this->generalTemplateModel)) {
            $this->generalTemplateModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Template_General',
                $this->getData('template_general_id'),NULL,
                array('template')
            );
        }

        return $this->generalTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_General $instance
     */
    public function setGeneralTemplate(Ess_M2ePro_Model_Template_General $instance)
    {
         $this->generalTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $this->sellingFormatTemplateModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Template_SellingFormat',
                $this->getData('template_selling_format_id'),NULL,
                array('template')
            );
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->sellingFormatTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {
            $this->descriptionTemplateModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Template_Description',
                $this->getData('template_description_id'),NULL,
                array('template')
            );
        }

        return $this->descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
         $this->descriptionTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->synchronizationTemplateModel)) {
            $this->synchronizationTemplateModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Template_Synchronization',
                $this->getData('template_synchronization_id'),NULL,
                array('template')
            );
        }

        return $this->synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->synchronizationTemplateModel = $instance;
    }

    // ########################################

    public function getAttributeSets()
    {
        $temp = $this->getData('cache_attribute_sets');

        if (!empty($temp)) {
            return $temp;
        }

        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_LISTING);
        $collection->addFieldToFilter('object_id',(int)$this->getId());

        $this->setData('cache_attribute_sets',$collection->getItems());

        return $this->getData('cache_attribute_sets');
    }

    public function getAttributeSetsIds()
    {
        $temp = $this->getData('cache_attribute_sets_ids');

        if (!empty($temp)) {
            return $temp;
        }

        $ids = array();
        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            /** @var $attributeSet Ess_M2ePro_Model_AttributeSet */
            $ids[] = $attributeSet->getAttributeSetId();
        }

        $this->setData('cache_attribute_sets_ids',$ids);

        return $this->getData('cache_attribute_sets_ids');
    }

    //------------------------------------------

    public function getProducts($asObjects = false, array $filters = array())
    {
        $products = $this->getRelatedComponentItems('Listing_Product','listing_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($products as $product) {
                /** @var $product Ess_M2ePro_Model_Listing_Product */
                $product->setListing($this);
            }
        }

        return $products;
    }

    public function getCategories($asObjects = false, array $filters = array())
    {
        $tempCollection = Mage::getModel('M2ePro/Listing_Category')->getCollection();
        $tempCollection->addFieldToFilter('listing_id', $this->getId());

        foreach ($filters as $field=>$filter) {
            $tempCollection->addFieldToFilter('`'.$field.'`', $filter);
        }

        $tempCollection->getSelect()
                       ->joinLeft(
                           array('cc' => Mage::getSingleton('core/resource')->getTableName('catalog/category')),
                           '`main_table`.`category_id` = `cc`.`entity_id`',
                           array('path_ids'=>'path')
                       );

        if ((bool)$asObjects) {
            return $tempCollection->getItems();
        }

        $tempArray = $tempCollection->toArray();
        return $tempArray['items'];
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    //-----------------------------------------

    public function isHideProductsOthersListings()
    {
        return (int)$this->getData('hide_products_others_listings') != self::HIDE_PRODUCTS_OTHERS_LISTINGS_NO;
    }

    //-----------------------------------------

    public function isSourceProducts()
    {
        return (int)$this->getData('source_products') == self::SOURCE_PRODUCTS_CUSTOM;
    }

    public function isSourceCategories()
    {
        return (int)$this->getData('source_products') == self::SOURCE_PRODUCTS_CATEGORIES;
    }

    // ########################################

    public function isSynchronizationNowRun()
    {
        return $this->isSynchronizationAlreadyStart() && !$this->isSynchronizationAlreadyStop();
    }

    //-----------------------------------------

    public function isSynchronizationAlreadyStart()
    {
        return (int)$this->getData('synchronization_already_start') == self::SYNCHRONIZATION_ALREADY_START_YES;
    }

    public function isSynchronizationAlreadyStop()
    {
        return (int)$this->getData('synchronization_already_stop') == self::SYNCHRONIZATION_ALREADY_STOP_YES;
    }

    //-----------------------------------------

    public function isSynchronizationOnlyStart()
    {
        if (is_null($this->getData('is_only_start')))
            return false;

        if (!$this->getData('is_only_start'))
            return false;

        return true;
    }

    public function isSynchronizationOnlyStop()
    {
        if (is_null($this->getData('is_only_stop')))
            return false;

        if (!$this->getData('is_only_stop'))
            return false;

        return true;
    }

    //-----------------------------------------

    public function getSynchronizationTimestampStart()
    {
        if ($this->getData('synchronization_start_type') == self::SYNCHRONIZATION_START_TYPE_IMMEDIATELY) {
            return strtotime($this->getData('create_date'));
        }

        if ($this->getData('synchronization_start_type') == self::SYNCHRONIZATION_START_TYPE_THROUGH) {

            $interval = 60;
            $temp = self::SYNCHRONIZATION_START_THROUGH_METRIC_DAYS;
            if ($this->getData('synchronization_start_through_metric') == $temp) {
                $interval = 60*60*24;
            }
            $temp = self::SYNCHRONIZATION_START_THROUGH_METRIC_HOURS;
            if ($this->getData('synchronization_start_through_metric') == $temp) {
                $interval = 60*60;
            }
            $temp = self::SYNCHRONIZATION_START_THROUGH_METRIC_MINUTES;
            if ($this->getData('synchronization_start_through_metric') == $temp) {
                $interval = 60;
            }
            $temp = ($interval * $this->getData('synchronization_start_through_value'));
            return strtotime($this->getData('create_date')) + $temp;
        }

        if ($this->getData('synchronization_start_type') == self::SYNCHRONIZATION_START_TYPE_DATE) {
            return strtotime($this->getData('synchronization_start_date'));
        }

        return Mage::helper('M2ePro')->getCurrentGmtDate(true) + 60*60*24*365*10;
    }

    public function getSynchronizationTimestampStop()
    {
        if ($this->getData('synchronization_stop_type') == self::SYNCHRONIZATION_STOP_TYPE_THROUGH) {
            $interval = 60*60*24;
            $temp = self::SYNCHRONIZATION_STOP_THROUGH_METRIC_HOURS;
            if ($this->getData('synchronization_stop_through_metric') == $temp) {
                $interval = 60*60;
            }
            if ($this->getData('synchronization_stop_through_metric') ==
                self::SYNCHRONIZATION_STOP_THROUGH_METRIC_MINUTES) {
                $interval = 60;
            }
            return $this->getSynchronizationTimestampStart() +
                   ($interval * $this->getData('synchronization_stop_through_value'));
        }

        if ($this->getData('synchronization_stop_type') == self::SYNCHRONIZATION_STOP_TYPE_DATE) {
            return strtotime($this->getData('synchronization_stop_date'));
        }

        return (Mage::helper('M2ePro')->getCurrentGmtDate(true)) + 60*60*24*365*10;
    }

    //-----------------------------------------

    public function setSynchronizationAlreadyStart($value = true)
    {
        if (!in_array(
            (int)$value,array(self::SYNCHRONIZATION_ALREADY_START_YES,self::SYNCHRONIZATION_ALREADY_START_NO)
        )) {
            return false;
        }

        $this->addData(array('synchronization_already_start'=>(int)$value))->save();
        return true;
    }

    public function setSynchronizationAlreadyStop($value = true)
    {
        if (!in_array((int)$value,array(self::SYNCHRONIZATION_ALREADY_STOP_YES,self::SYNCHRONIZATION_ALREADY_STOP_NO)))
            return false;

        $this->addData(array('synchronization_already_stop'=>(int)$value))->save();
        return true;
    }

    //-----------------------------------------

    public function setSynchronizationOnlyStart($value = true)
    {
        $this->addData(array('is_only_start'=>$value));
        return true;
    }

    public function setSynchronizationOnlyStop($value = true)
    {
        $this->addData(array('is_only_stop'=>$value));
        return true;
    }

    // ########################################

    public function hasProduct($productId)
    {
        if (count($this->getProducts(false,array('product_id'=>$productId))) > 0) {
            return true;
        }

        return false;
    }

    //-----------------------------------------

    public function addProduct($product,$checkingMode = false)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        //TODO buy&playtemporarily type simple filter
        //----------------------------------
        $magentoProduct = $product instanceof Mage_Catalog_Model_Product
            ? Mage::getModel('M2ePro/Magento_Product')->setProduct($product)
            : Mage::getModel('M2ePro/Magento_Product')->setProductId($productId);

        if (($this->getComponentMode() == Ess_M2ePro_Helper_Component_Buy::NICK  ||
             $this->getComponentMode() == Ess_M2ePro_Helper_Component_Play::NICK) &&
            $magentoProduct->isProductWithVariations()) {
            return false;
        }

        if ($this->getComponentMode() == Ess_M2ePro_Helper_Component_Ebay::NICK &&
            $this->hasProduct($productId)) {
            return false;
        }

        // Add attribute set filter
        //----------------------------
        if ($product instanceof Mage_Catalog_Model_Product) {

            if (!in_array((int)$product->getAttributeSetId(),$this->getAttributeSetsIds())) {
                return false;
            }

        } else {

            $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                                 ->select()
                                 ->from($table,new Zend_Db_Expr('DISTINCT `entity_id`'))
                                 ->where('`entity_id` = ?',(int)$productId)
                                 ->where('attribute_set_id IN('.implode(',',$this->getAttributeSetsIds()).')');

            $productArray = Mage::getResourceModel('core/config')
                                            ->getReadConnection()
                                            ->fetchCol($dbSelect);

            if (count($productArray) <= 0) {
                return false;
            }
        }
        //----------------------------

        // Hide products others listings
        //----------------------------
        if ($this->isHideProductsOthersListings()) {

            $table = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                                         ->select()
                                         ->from($table,new Zend_Db_Expr('DISTINCT `product_id`'))
                                         ->where('`product_id` = ?',(int)$productId)
                                         ->where('`component_mode` = ?',(string)$this->getComponentMode());

            $productArray = Mage::getResourceModel('core/config')
                                        ->getReadConnection()
                                        ->fetchCol($dbSelect);

            if (count($productArray) > 0) {
                return false;
            }
        }
        //----------------------------

        if ($checkingMode) {
            return true;
        }

        $data = array(
            'listing_id' => $this->getId(),
            'product_id' => $productId,
            'status'     => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );

        $listingProductTemp =
            Mage::helper('M2ePro/Component')->getComponentModel($this->getComponentMode(),'Listing_Product')
                                    ->setData($data)->save();

        $variationUpdaterModelPrefix = ucwords($this->getComponentMode()).'_';
        Mage::getModel('M2ePro/'.$variationUpdaterModelPrefix.'Listing_Product_Variation_Updater')
                ->updateVariations($listingProductTemp);

        // Add message for listing log
        //------------------------------
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $tempLog->addProductMessage( $this->getId(),
                                     $productId,
                                     $listingProductTemp->getId(),
                                     Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING,
                                     // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully added');
                                     'Item was successfully added',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
        //------------------------------

        return $listingProductTemp;
    }

    public function addProductsFromCategory($categoryId)
    {
        $categoryProductsArray = $this->getProductsFromCategory($categoryId);

        // Add categories products
        //----------------------------
        foreach ($categoryProductsArray as $productTemp) {
            $this->addProduct($productTemp);
        }
        //----------------------------
    }

    public function getProductsFromCategory($categoryId)
    {
        // Make collection
        //----------------------------
        $collection = Mage::getModel('catalog/product')->getCollection();
        //----------------------------

        // Add attribute set filter
        //----------------------------
        $collection->addFieldToFilter('attribute_set_id', array('in' => $this->getAttributeSetsIds()));
        //----------------------------

        // Hide products others listings
        //----------------------------
        if ($this->isHideProductsOthersListings()) {

            $table = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from($table,new Zend_Db_Expr('DISTINCT `product_id`'))
                ->where('`component_mode` = ?',(string)$this->getComponentMode());

            $collection->getSelect()->where('`e`.`entity_id` NOT IN ('.$dbSelect->__toString().')');
        }
        //----------------------------

        // Add categories filter
        //----------------------------
        $table = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($table,new Zend_Db_Expr('DISTINCT `product_id`'))
            ->where("`category_id` = ?",(int)$categoryId);

        $collection->getSelect()->where('`e`.`entity_id` IN ('.$dbSelect->__toString().')');
        //----------------------------

        // Get categories products
        //----------------------------
        $sqlQuery = $collection->getSelect()->__toString();

        $categoryProductsArray = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($sqlQuery);

        return (array)$categoryProductsArray;
    }

    // ########################################

    public static function removeDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        $processedListings = array();

        // Delete Options
        //------------------
        $variationOptions = Mage::getModel('M2ePro/Listing_Product_Variation_Option')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        $deletedVariationsIds = array();

        /** @var $variationOption Ess_M2ePro_Model_Listing_Product_Variation_Option */
        foreach ($variationOptions as $variationOption) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = $variationOption->getListingProduct();

            /** @var $listingVariation Ess_M2ePro_Model_Listing_Product_Variation */
            $listingVariation = $variationOption->getListingProductVariation();

            if (!in_array($listingVariation->getId(),$deletedVariationsIds)) {
                $listingVariation->setStatus(Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED)->save();
                $listingVariation->deleteInstance();
                $deletedVariationsIds[] = $listingVariation->getId();
            }

            $listingId = $listingProduct->getListingId();
            $componentMode = $listingProduct->getComponentMode();

            if (isset($processedListings[$listingId.'_'.$componentMode])) {
                continue;
            }

            $processedListings[$listingId.'_'.$componentMode] = 1;

            Mage::getModel('M2ePro/Listing_Log')
                ->setComponentMode($componentMode)
                ->addProductMessage($listingId,
                                    $productId,
                                    $listingProduct->getId(),
                                    Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                                    // ->__('Variation option was deleted. Item was reset.');
                                    'Variation option was deleted. Item was reset.',
                                    Ess_M2ePro_Model_Listing_Log::TYPE_WARNING,
                                    Ess_M2ePro_Model_Listing_Log::PRIORITY_HIGH);
        }

        //------------------

        $processedListings = array();

        // Delete Products
        //------------------
        $listingsProducts = Mage::getModel('M2ePro/Listing_Product')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        $deletedListingsProductsIds = array();

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $listingProduct) {

            if (!in_array($listingProduct->getId(),$deletedListingsProductsIds)) {
                $listingProduct->setStatus(Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED)->save();
                $listingProduct->deleteInstance();
                $deletedListingsProductsIds[] = $listingProduct->getId();
            }

            $listingId = $listingProduct->getListingId();
            $componentMode = $listingProduct->getComponentMode();

            if (isset($processedListings[$listingId.'_'.$componentMode])) {
                continue;
            }

            $processedListings[$listingId.'_'.$componentMode] = 1;

            Mage::getModel('M2ePro/Listing_Log')
                ->setComponentMode($componentMode)
                ->addProductMessage($listingId,
                                    $productId,
                                    $listingProduct->getId(),
                                    Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::TYPE_WARNING,
                                    Ess_M2ePro_Model_Listing_Log::PRIORITY_HIGH );
        }
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('listing');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('listing');
        return parent::delete();
    }

    // ########################################
}