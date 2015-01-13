<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product
{
    const TYPE_SIMPLE       = 'simple';
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_BUNDLE       = 'bundle';
    const TYPE_GROUPED      = 'grouped';
    const TYPE_DOWNLOADABLE = 'downloadable';
    const TYPE_VIRTUAL      = 'virtual';

    const GROUPED_PRODUCT_ATTRIBUTE_LABEL = 'Option';
    const THUMBNAIL_IMAGE_CACHE_TIME = 604800;

    const TAX_CLASS_ID_NONE = 0;

    const FORCING_QTY_TYPE_MANAGE_STOCK_NO = 1;
    const FORCING_QTY_TYPE_BACKORDERS = 2;

    /**
     *  $statistics = array(
     *      'id' => array(
     *         'store_id' => array(
     *              'product_id' => array(
     *                  'qty' => array(
     *                      '1' => $qty,
     *                      '2' => $qty,
     *                  ),
     *              ),
     *              ...
     *          ),
     *          ...
     *      ),
     *      ...
     *  )
     */

    public static $statistics = array();

    private $statisticId;

    // ########################################

    private $_productId = 0;

    private $_storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $_productModel = NULL;

    // ########################################

    public $notFoundAttributes = array();

    // ########################################

    public function exists()
    {
        if (is_null($this->_productId)) {
            return false;
        }

        $table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
             ->select()
             ->from($table, new Zend_Db_Expr('COUNT(*)'))
             ->where('`entity_id` = ?', (int)$this->_productId);

        $count = Mage::getResourceModel('core/config')->getReadConnection()->fetchOne($dbSelect);

        return $count == 1;
    }

    /**
     * @param int|null $productId
     * @param int|null $storeId
     * @throws Exception
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function loadProduct($productId = NULL, $storeId = NULL)
    {
        $productId = (is_null($productId)) ? $this->_productId : $productId;
        $storeId = (is_null($storeId)) ? $this->_storeId : $storeId;

        if ($productId <= 0) {
            throw new Exception('The Product ID is not set.');
        }

        try {

            $this->_productModel = Mage::getModel('catalog/product')
                 ->setStoreId($storeId)
                 ->load($productId);

        } catch(Mage_Core_Model_Store_Exception $e) {
            throw new Exception(Mage::helper('M2ePro')->__("Store ID '%store_id%' doesn't exist.", $storeId));
        }

        $this->setProductId($productId);
        $this->setStoreId($storeId);

        return $this;
    }

    // ########################################

    /**
     * @param int $productId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;
        return $this;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    // ----------------------------------------

    /**
     * @param int $storeId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    // ########################################

    public function getStoreIds()
    {
        $storeIds = array();
        foreach ($this->getWebsiteIds() as $websiteId) {
            try {
                $websiteStores = Mage::app()->getWebsite($websiteId)->getStoreIds();
                $storeIds = array_merge($storeIds, $websiteStores);
            } catch (Exception $e) {
                continue;
            }
        }
        return $storeIds;
    }

    public function getWebsiteIds()
    {
        $resource = Mage::getSingleton('core/resource');
        $select = $resource->getConnection('core_read')
            ->select()
            ->from($resource->getTableName('catalog/product_website'), 'website_id')
            ->where('product_id = ?', (int)$this->getProductId());

        $websiteIds = $resource->getConnection('core_read')->fetchCol($select);
        return $websiteIds ? $websiteIds : array();
    }

    // ########################################

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_productModel) {
            return $this->_productModel;
        }

        if ($this->_productId > 0) {
            $this->loadProduct();
            return $this->_productModel;
        }

        throw new Exception('Load instance first');
    }

    /**
     * @param Mage_Catalog_Model_Product $productModel
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setProduct(Mage_Catalog_Model_Product $productModel)
    {
        $this->_productModel = $productModel;

        $this->setProductId($this->_productModel->getId());
        $this->setStoreId($this->_productModel->getStoreId());

        return $this;
    }

    // --------------------------------------

    /**
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Exception
     */
    public function getTypeInstance()
    {
        if (is_null($this->_productModel) && $this->_productId < 0) {
            throw new Exception('Load instance first');
        }

        $typeInstance = $this->getProduct()->getTypeInstance();
        $typeInstance->setStoreFilter($this->getStoreId());

        return $typeInstance;
    }

    /**
     * @return Mage_CatalogInventory_Model_Stock_Item
     * @throws Exception
     */
    public function getStockItem()
    {
        if (is_null($this->_productModel) && $this->_productId < 0) {
            throw new Exception('Load instance first');
        }

        $productId = !is_null($this->_productModel) ?
                              $this->_productModel->getId() :
                              $this->_productId;

        return Mage::getModel('cataloginventory/stock_item')
                    ->loadByProduct($productId);
    }

    // ########################################

    public static function getTypeIdByProductId($productId)
    {
        $tempKey = 'product_id_' . (int)$productId . '_type';

        if (!is_null($typeId = Mage::helper('M2ePro/Data_Global')->getValue($tempKey))) {
            return $typeId;
        }

        $productId = (int)$productId;
        $table  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($table,'type_id')
                             ->where('`entity_id` = ?',(int)$productId);

        $typeId = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);

        Mage::helper('M2ePro/Data_Global')->setValue($tempKey,$typeId);

        return $typeId;
    }

    public static function getNameByProductId($productId, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $tempKey = 'product_id_' . (int)$productId . '_' . (int)$storeId . '_name';

        if (!is_null($name = Mage::helper('M2ePro/Data_Global')->getValue($tempKey))) {
            return $name;
        }

        // Prepare tables names
        //-----------------------------
        $catalogProductEntityVarCharTable  = Mage::getSingleton('core/resource')->getTableName(
            'catalog_product_entity_varchar'
        );
        $eavAttributeTable  = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        //-----------------------------

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()->from(array('cpev'=>$catalogProductEntityVarCharTable),array('name'=>'value'))
            ->join(
                array('ea'=>$eavAttributeTable),
                '`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\'',
                array()
            )
            ->where('`cpev`.`entity_id` = ?',(int)$productId)->where('`cpev`.`store_id` = ?',(int)$storeId);
        //-----------------------------

        // Get row of product name
        //-----------------------------
        $name = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        if ($name) {
            Mage::helper('M2ePro/Data_Global')->setValue($tempKey,$name);
            return $name;
        }

        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            Mage::helper('M2ePro/Data_Global')->setValue($tempKey,'');
            return '';
        }

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(array('cpev'=>$catalogProductEntityVarCharTable),array('name'=>'value'))
            ->join(
                array('ea'=>$eavAttributeTable),
                '`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\'',
                array()
            )
            ->where('`cpev`.`entity_id` = ?',(int)$productId)
            ->where('`cpev`.`store_id` = '.Mage_Core_Model_App::ADMIN_STORE_ID);
        //-----------------------------

        // Get row of product name
        //-----------------------------
        $name = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        if ($name) {
            Mage::helper('M2ePro/Data_Global')->setValue($tempKey,$name);
            return $name;
        }

        Mage::helper('M2ePro/Data_Global')->setValue($tempKey,'');

        return '';
    }

    public static function getSkuByProductId($productId)
    {
        $tempKey = 'product_id_' . (int)$productId . '_name';

        if (!is_null($sku = Mage::helper('M2ePro/Data_Global')->getValue($tempKey))) {
            return $sku;
        }

        // Prepare tables names
        //-----------------------------
        $catalogProductEntityTable  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        //-----------------------------

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($catalogProductEntityTable,'sku')
                             ->where('`entity_id` = ?',(int)$productId);
        //-----------------------------

        // Get row of product sku
        //-----------------------------
        $sku = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        Mage::helper('M2ePro/Data_Global')->setValue($tempKey,$sku);

        return $sku;
    }

    // ########################################

    public function getTypeId()
    {
        $typeId = NULL;
        if (!$this->_productModel && $this->_productId > 0) {
            $typeId = self::getTypeIdByProductId($this->_productId);
        } else {
            $typeId = $this->getProduct()->getTypeId();
        }

        return $typeId;
    }

    //-----------------------------------------

    public function isSimpleType()
    {
        return $this->getTypeId() == self::TYPE_SIMPLE;
    }

    public function isSimpleTypeWithCustomOptions()
    {
        if (!$this->isSimpleType()) {
            return false;
        }

        $hasOptions = false;
        foreach ($this->getProduct()->getOptions() as $option) {
            if ((int)$option->getData('is_require')) {
                $hasOptions = true;
                break;
            }
        }

        return $hasOptions;
    }

    public function isSimpleTypeWithoutCustomOptions()
    {
        if (!$this->isSimpleType()) {
            return false;
        }

        return !$this->isSimpleTypeWithCustomOptions();
    }

    //-----------------------------------------

    public function isConfigurableType()
    {
        return $this->getTypeId() == self::TYPE_CONFIGURABLE;
    }

    public function isBundleType()
    {
        return $this->getTypeId() == self::TYPE_BUNDLE;
    }

    public function isGroupedType()
    {
        return $this->getTypeId() == self::TYPE_GROUPED;
    }

    public function isDownloadableType()
    {
        return $this->getTypeId() == self::TYPE_DOWNLOADABLE;
    }

    public function isVirtualType()
    {
        return $this->getTypeId() == self::TYPE_VIRTUAL;
    }

    // ########################################

    public function isProductWithVariations()
    {
        return !$this->isProductWithoutVariations();
    }

    public function isProductWithoutVariations()
    {
        return $this->isSimpleTypeWithoutCustomOptions();
    }

    public function isStrictVariationProduct()
    {
        return $this->isConfigurableType() || $this->isBundleType() || $this->isGroupedType();
    }

    // ########################################

    public function getSku()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getSkuByProductId($this->_productId);
            if (!is_null($temp) && $temp != '') {
                return $temp;
            }
        }
        return $this->getProduct()->getSku();
    }

    public function getName()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            return self::getNameByProductId($this->_productId, $this->_storeId);
        }
        return $this->getProduct()->getName();
    }

    //-----------------------------------------

    public function isStatusEnabled()
    {
        if (!$this->_productModel && $this->_productId > 0) {

            $status = Mage::getSingleton('M2ePro/Magento_Product_Status')
                            ->getProductStatus($this->_productId, $this->_storeId);

            if (is_array($status) && isset($status[$this->_productId])) {

                $status = (int)$status[$this->_productId];
                if ($status == Mage_Catalog_Model_Product_Status::STATUS_DISABLED ||
                    $status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    return $status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
                }
            }
        }

        return (int)$this->getProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    public function isStockAvailability()
    {
        return self::calculateStockAvailability(
            $this->getStockItem()->getData('is_in_stock'),
            $this->getStockItem()->getData('manage_stock'),
            $this->getStockItem()->getData('use_config_manage_stock')
        );
    }

    public static function calculateStockAvailability($isInStock, $manageStock, $useConfigManageStock)
    {
        $manageStockGlobal = Mage::getStoreConfigFlag('cataloginventory/item_options/manage_stock');
        if(($useConfigManageStock && !$manageStockGlobal) || (!$useConfigManageStock && !$manageStock)){
            return true;
        }
        return (bool)$isInStock;
    }

    // ########################################

    public function getPrice()
    {
        return (double)$this->getProduct()->getPrice();
    }

    public function setPrice($value)
    {
        return $this->getProduct()->setPrice($value);
    }

    //-----------------------------------------

    public function getSpecialPrice()
    {
        $fromDate = strtotime($this->getSpecialPriceFromDate());
        $toDate = strtotime($this->getSpecialPriceToDate());

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if ($currentTimeStamp < $fromDate || $currentTimeStamp > $toDate) {
            return 0;
        }

        return (double)$this->getProduct()->getSpecialPrice();
    }

    public function setSpecialPrice($value)
    {
        return $this->getProduct()->setSpecialPrice($value);
    }

    //-----------------------------------------

    public function getSpecialPriceFromDate()
    {
        $fromDate = $this->getProduct()->getSpecialFromDate();

        if (is_null($fromDate) || $fromDate === false || $fromDate == '') {
            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            $fromDate = Mage::helper('M2ePro')->getDate($currentTimeStamp - 60*60*24*30*12);
        }

        return $fromDate;
    }

    public function getSpecialPriceToDate()
    {
        $toDate = $this->getProduct()->getSpecialToDate();

        if (is_null($toDate) || $toDate === false || $toDate == '') {
            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            $toDate = Mage::helper('M2ePro')->getDate($currentTimeStamp + 60*60*24*30*12);
        } else {
            $toDate = new DateTime($toDate, new DateTimeZone('UTC'));
            $toDate->modify('+1 day');
            $toDate = Mage::helper('M2ePro')->getDate($toDate->format('U'));
        }

        return $toDate;
    }

    // ########################################

    public function getQty($lifeMode = false)
    {
        if ($lifeMode && (!$this->isStatusEnabled() || !$this->isStockAvailability())) {
            return 0;
        }

        if ($this->isStrictVariationProduct()) {

            if ($this->isBundleType()) {
                return $this->getBundleQty($lifeMode);
            }
            if ($this->isGroupedType()) {
                return $this->getGroupedQty($lifeMode);
            }
            if ($this->isConfigurableType()) {
                return $this->getConfigurableQty($lifeMode);
            }
        }

        return $this->calculateQty(
            $this->getStockItem()->getQty(),
            $this->getStockItem()->getData('manage_stock'),
            $this->getStockItem()->getUseConfigManageStock(),
            $this->getStockItem()->getData('backorders'),
            $this->getStockItem()->getUseConfigBackorders()
        );
    }

    public function setQty($value)
    {
        $this->getStockItem()->setQty($value)->save();
    }

    //-----------------------------------------

    protected function calculateQty($qty,
                                    $manageStock, $useConfigManageStock,
                                    $backorders, $useConfigBackorders)
    {
        $forceQtyMode = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/product/force_qty/','mode'
        );

        if ($forceQtyMode == 0) {
            return $qty;
        }

        $forceQtyValue = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/product/force_qty/','value'
        );

        $manageStockGlobal = Mage::getStoreConfigFlag('cataloginventory/item_options/manage_stock');
        if (($useConfigManageStock && !$manageStockGlobal) || (!$useConfigManageStock && !$manageStock)) {
            self::$statistics[$this->getStatisticId()]
                             [$this->getProductId()]
                             [$this->getStoreId()]
                             ['qty']
                             [self::FORCING_QTY_TYPE_MANAGE_STOCK_NO] = $forceQtyValue;
            return $forceQtyValue;
        }

        $backOrdersGlobal = Mage::getStoreConfig('cataloginventory/item_options/backorders');
        if (($useConfigBackorders && $backOrdersGlobal != Mage_CatalogInventory_Model_Stock::BACKORDERS_NO) ||
           (!$useConfigBackorders && $backorders != Mage_CatalogInventory_Model_Stock::BACKORDERS_NO)) {
            if($forceQtyValue > $qty){
                self::$statistics[$this->getStatisticId()]
                                 [$this->getProductId()]
                                 [$this->getStoreId()]
                                 ['qty']
                                 [self::FORCING_QTY_TYPE_BACKORDERS] = $forceQtyValue;
                return $forceQtyValue;
            }
        }

        return $qty;
    }

    //-----------------------------------------

    protected function getConfigurableQty($lifeMode = false)
    {
        $totalQty = 0;

        foreach ($this->getTypeInstance()->getUsedProducts() as $childProduct) {

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);

            $isInStock = self::calculateStockAvailability(
                $stockItem->getData('is_in_stock'),
                $stockItem->getData('manage_stock'),
                $stockItem->getData('use_config_manage_stock')
            );

            $qty = $this->calculateQty(
                $stockItem->getQty(),
                $stockItem->getData('manage_stock'),
                $stockItem->getUseConfigManageStock(),
                $stockItem->getData('backorders'),
                $stockItem->getUseConfigBackorders()
            );

            if ($lifeMode &&
                (!$isInStock || $childProduct->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) {
                continue;
            }

            $totalQty += $qty;
        }

        return $totalQty;
    }

    protected function getGroupedQty($lifeMode = false)
    {
        $totalQty = 0;

        foreach ($this->getTypeInstance()->getAssociatedProducts() as $childProduct) {

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);

            $isInStock = self::calculateStockAvailability(
                $stockItem->getData('is_in_stock'),
                $stockItem->getData('manage_stock'),
                $stockItem->getData('use_config_manage_stock')
            );

            $qty = $this->calculateQty(
                $stockItem->getQty(),
                $stockItem->getData('manage_stock'),
                $stockItem->getUseConfigManageStock(),
                $stockItem->getData('backorders'),
                $stockItem->getUseConfigBackorders()
            );

            if ($lifeMode &&
                (!$isInStock || $childProduct->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) {
                continue;
            }

            $totalQty += $qty;
        }

        return $totalQty;
    }

    protected function getBundleQty($lifeMode = false)
    {
        $product = $this->getProduct();

        // Prepare bundle options format usable for search
        $productInstance = $this->getTypeInstance();

        $optionCollection = $productInstance->getOptionsCollection($product);
        $optionsData = $optionCollection->getData();

        foreach ($optionsData as $singleOption) {
            // Save QTY, before calculate = 0
            $bundleOptionsArray[$singleOption['option_id']] = 0;
        }

        $selectionsCollection = $productInstance->getSelectionsCollection($optionCollection->getAllIds(), $product);
        $_items = $selectionsCollection->getItems();

        $bundleOptionsQtyArray = array();
        foreach ($_items as $_item) {

            $itemInfoAsArray = $_item->toArray();

            if (!isset($bundleOptionsArray[$itemInfoAsArray['option_id']])) {
                continue;
            }

            $isInStock = self::calculateStockAvailability(
                $itemInfoAsArray['stock_item']['is_in_stock'],
                $itemInfoAsArray['stock_item']['manage_stock'],
                $itemInfoAsArray['stock_item']['use_config_manage_stock']
            );

            $qty = $this->calculateQty(
                $itemInfoAsArray['stock_item']['qty'],
                $itemInfoAsArray['stock_item']['manage_stock'],
                $itemInfoAsArray['stock_item']['use_config_manage_stock'],
                $itemInfoAsArray['stock_item']['backorders'],
                $itemInfoAsArray['stock_item']['use_config_backorders']
            );

            if ($lifeMode &&
                (!$isInStock || $itemInfoAsArray['status'] != Mage_Catalog_Model_Product_Status::STATUS_ENABLED)) {
                continue;
            }

            // Only positive
            // grouping qty by product id
            $bundleOptionsQtyArray[$itemInfoAsArray['product_id']][$itemInfoAsArray['option_id']] = $qty;
        }

        foreach($bundleOptionsQtyArray as $optionQty) {
            foreach($optionQty as $optionId => $val) {
                $bundleOptionsArray[$optionId] += floor($val/count($optionQty));
            }
        }

        // Get min of qty product for all options
        $minQty = -1;
        foreach ($bundleOptionsArray as $singleBundle) {
            if ($singleBundle < $minQty || $minQty == -1) {
                $minQty = $singleBundle;
            }
        }

        return $minQty;
    }

    //-----------------------------------------

    public function setStatisticId($id)
    {
        $this->statisticId = $id;
        return $this;
    }

    public function getStatisticId()
    {
        return $this->statisticId;
    }

    // ########################################

    public function getAttributeFrontendInput($attributeCode)
    {
        $productObject = $this->getProduct();

        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = $productObject->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            $this->addNotFoundAttributes($attributeCode);
            return '';
        }

        if (!$productObject->hasData($attributeCode)) {
            $this->addNotFoundAttributes($attributeCode);
            return '';
        }

        return $attribute->getFrontendInput();
    }

    public function getAttributeValue($attributeCode)
    {
        $productObject = $this->getProduct();

        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = $productObject->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            $this->addNotFoundAttributes($attributeCode);
            return '';
        }

        if (!$productObject->hasData($attributeCode)) {
            $this->addNotFoundAttributes($attributeCode);
            return '';
        }

        $value = $productObject->getData($attributeCode);

        if ($attributeCode == 'media_gallery') {
            return implode(',',$this->getGalleryImagesLinks(100));
        }

        if (is_null($value) || is_bool($value) || is_array($value) || $value === '') {
            return '';
        }

        // SELECT and MULTISELECT
        if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {

            // User Attribute
            if ((int)$attribute->getData('is_user_defined') == 1) {

                $valueNew = '';
                $optionIds = (array)explode(',',$value);

                foreach ($optionIds as $optionId) {
                    $attributeOption = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                                    ->addFieldToFilter('main_table.option_id', array('in' => $optionId))
                                                    //->setIdFilter($optionId)
                                                    ->setStoreFilter($this->getStoreId())
                                                    ->load()->getFirstItem();
                    $valueNew != '' && $valueNew .= ', ';
                    $valueNew .= $attributeOption->getData('value');
                }

                $value = (string)$valueNew;
            }

        // DATE
        } else if ($attribute->getFrontendInput() == 'date') {
            $temp = explode(' ',$value);
            isset($temp[0]) && $value = (string)$temp[0];

        // YES NO
        }  else if ($attribute->getFrontendInput() == 'boolean') {
            (bool)$value ? $value = Mage::helper('M2ePro')->__('Yes') :
                           $value = Mage::helper('M2ePro')->__('No');

        // PRICE
        }  else if ($attribute->getFrontendInput() == 'price') {
            $value = (string)round($value, 2);

        // MEDIA IMAGE
        }  else if ($attribute->getFrontendInput() == 'media_image') {
            if ($value == 'no_selection') {
                $value = '';
            } else {
                if (!preg_match('((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)',$value)) {
                    $value = Mage::app()->getStore($this->getStoreId())
                                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                                        'catalog/product/'.ltrim($value,'/');
                    $value = str_replace('https://','http://',$value);
                }
            }
        }

        return is_string($value) ? $value : '';
    }

    public function setAttributeValue($attributeCode, $value)
    {
        //supports only string values
        if (is_string($value)) {
            $productObject = $this->getProduct();

            $productObject->setData($attributeCode, $value)
                ->getResource()
                ->saveAttribute($productObject, $attributeCode);
        }

        return $this;
    }

    // ########################################

    public function getThumbnailImageLink()
    {
        $eaTable = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        $cpevTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                              ->select()
                              ->from(array('cpev'=>$cpevTable),'value')
                              ->joinInner(array('ea'=>$eaTable),'`ea`.`attribute_id` = `cpev`.`attribute_id`',array())
                              ->where('`cpev`.`store_id` = ?',(int)$this->getStoreId())
                              ->where('`cpev`.`entity_id` = ?',(int)$this->getProductId())
                              ->where('`ea`.`attribute_code` = \'thumbnail\'');

        $tempPath = (string)Mage::getResourceModel('core/config')->getReadConnection()->fetchOne($dbSelect);

        if ($tempPath == '' || $tempPath == 'no_selection' || $tempPath == '/') {

            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from(array('cpev'=>$cpevTable),'value')
                ->joinInner(array('ea'=>$eaTable),'`ea`.`attribute_id` = `cpev`.`attribute_id`',array())
                ->where('`cpev`.`store_id` = ?',Mage_Core_Model_App::ADMIN_STORE_ID)
                ->where('`cpev`.`entity_id` = ?',(int)$this->getProductId())
                ->where('`ea`.`attribute_code` = \'thumbnail\'');

            $tempPath = (string)Mage::getResourceModel('core/config')->getReadConnection()->fetchOne($dbSelect);

            if ($tempPath == '' || $tempPath == 'no_selection' || $tempPath == '/') {
                return NULL;
            }
        }

        $imagePathOriginal = Mage::getBaseDir('media').DS.'catalog/product'.$tempPath;

        if (!is_file($imagePathOriginal)) {
            return NULL;
        }

        $width = 100;
        $height = 100;

        $prefixResizedImage = 'resized-'.$width.'px-'.$height.'px-';
        $imagePathResized = dirname($imagePathOriginal).DS.$prefixResizedImage.basename($imagePathOriginal);

        if (is_file($imagePathResized)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($imagePathResized) + self::THUMBNAIL_IMAGE_CACHE_TIME > $currentTime) {
                $tempValue = str_replace(basename($imagePathOriginal),$prefixResizedImage.basename($imagePathOriginal),
                                         $tempPath);
                return Mage::app()->getStore($this->getStoreId())
                                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                                        'catalog/product/'.ltrim($tempValue,'/');
            }
            @unlink($imagePathResized);
        }

        try {

            $imageObj = new Varien_Image($imagePathOriginal);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize($width, $height);
            $imageObj->save($imagePathResized);

        } catch (Exception $exception) {
            return NULL;
        }

        if (!is_file($imagePathResized)) {
            return NULL;
        }

        $tempValue = str_replace(basename($imagePathOriginal),$prefixResizedImage.basename($imagePathOriginal),
                                 $tempPath);

        return Mage::app()->getStore($this->getStoreId())
                                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                                        'catalog/product/'.ltrim($tempValue,'/');
    }

    public function getImageLink($attribute = 'image')
    {
        if ($attribute == '') {
            return '';
        }

        $imageUrl = $this->getAttributeValue($attribute);
        return $this->prepareImageUrl($imageUrl);
    }

    public function getGalleryImageLink($position = 1)
    {
        $position = (int)$position;

        if ($position <= 0) {
            return '';
        }

        // need for correct sampling of the array
        $position--;

        $galleryImages = $this->getProduct()->getData('media_gallery');

        if (!isset($galleryImages['images']) || !is_array($galleryImages['images'])) {
            return '';
        }

        if (!isset($galleryImages['images'][$position])) {
            return '';
        }

        $galleryImage = $galleryImages['images'][$position];

        if (isset($galleryImage['disabled']) && (bool)$galleryImage['disabled']) {
            return '';
        }

        if (!isset($galleryImage['file'])) {
            return '';
        }

        $imageUrl = Mage::app()->getStore($this->getStoreId())
                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                        'catalog/product/'.ltrim($galleryImage['file'],'/');

        return $this->prepareImageUrl($imageUrl);
    }

    public function getGalleryImagesLinks($limitImages = 0)
    {
        $limitImages = (int)$limitImages;

        if ($limitImages <= 0) {
            return array();
        }

        $galleryImages = $this->getProduct()->getData('media_gallery');

        if (!isset($galleryImages['images']) || !is_array($galleryImages['images'])) {
            return array();
        }

        $images = array();

        $i = 0;
        foreach ($galleryImages['images'] as $galleryImage) {

            if ($i >= $limitImages) {
                break;
            }

            if (isset($galleryImage['disabled']) && (bool)$galleryImage['disabled']) {
                continue;
            }

            if (!isset($galleryImage['file'])) {
                continue;
            }

            $imageUrl = Mage::app()->getStore($this->getStoreId())
                            ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                            'catalog/product/'.ltrim($galleryImage['file'],'/');

            $imageUrl = $this->prepareImageUrl($imageUrl);

            if (empty($imageUrl)) {
                continue;
            }

            $images[] = $imageUrl;
            $i++;
        }

        return $images;
    }

    private function prepareImageUrl($url)
    {
        if (!is_string($url) || $url == '') {
            return '';
        }

        return str_replace(array('https://', ' '), array('http://', '%20'), $url);
    }

    // ########################################

    public function hasRequiredOptions()
    {
        if ($this->isDownloadableType() || $this->isVirtualType()) {
            return false;
        }

        if ($this->isGroupedType()) {
            return true;
        }

        $product = $this->getProduct();

        return $this->getTypeInstance()->hasRequiredOptions($product);
    }

    // ########################################

    public function getProductVariations()
    {
        $variations = array();
        $variationsSet = array();

        if ($this->isConfigurableType()) {

            $tempInfo = $this->_getConfigurableOptionsForVariation();
            isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
            isset($tempInfo['variations']) && $variations = $tempInfo['variations'];

        } else {

            if ($this->isSimpleType()) {

                $tempInfo = $this->_getCustomOptionsForVariation();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];

            } else if ($this->isBundleType()) {

                $tempInfo = $this->_getBundleOptionsForVariation();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];

            } elseif ($this->isGroupedType()) {

                $tempInfo = $this->_getGroupedOptionsForVariation();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            }

            $countOfCombinations = 1;

            foreach ($variationsSet as $set) {
                $countOfCombinations *= count($set);
            }

            if ($countOfCombinations > 100000) {
                $variationsSet = array();
                $variations = array();
            } else {
                $this->prepareVariationsScope($variations);
                $variations = $this->prepareRawVariations($variations, $variationsSet);
            }
        }

        return array(
            'set' => $variationsSet,
            'variations' => $variations,
        );
    }

    //-----------------------------------------

    protected function _getCustomOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_SIMPLE) {
            return array();
        }

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($product->getOptions() as $productCustomOptions) {

            if (!(bool)(int)$productCustomOptions->getData('is_require')) {
                continue;
            }

            if (in_array($productCustomOptions->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {

                $optionCombinationTitle = array();
                $possibleVariationProductOptions = array();

                $optionTitle = $productCustomOptions->getTitle();
                if ($optionTitle == '') {
                    $optionTitle = $productCustomOptions->getDefaultTitle();
                }

                foreach ($productCustomOptions->getValues() as $option) {

                    $optionCombinationTitle[] = $option->getTitle();

                    $possibleVariationProductOptions[] = array(
                        'product_id' => $product->getId(),
                        'product_type' => $product->getTypeId(),
                        'attribute' => $optionTitle,
                        'option' => $option->getTitle()
                    );
                }

                $variationOptionsTitle[$optionTitle] = $optionCombinationTitle;
                $variationOptionsList[] = $possibleVariationProductOptions;
            }
        }

        return array(
            'set' => $variationOptionsTitle,
            'variations' => $variationOptionsList
        );
    }

    protected function _getBundleOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_BUNDLE) {
            return array();
        }

        $productInstance = $this->getTypeInstance();
        $optionCollection = $productInstance->getOptionsCollection($product);

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($optionCollection as $singleOption) {

            if (!(bool)(int)$singleOption->getData('required')) {
                continue;
            }

            $optionTitle = $singleOption->getTitle();
            $optionTitle == '' && $optionTitle = $singleOption->getDefaultTitle();

            if (isset($variationOptionsTitle[$optionTitle])) {
                continue;
            }

            $optionCombinationTitle = array();
            $possibleVariationProductOptions = array();

            $selectionsCollectionItems = $productInstance->getSelectionsCollection(
                array(0 => $singleOption->getId()), $product
            )->getItems();

            foreach ($selectionsCollectionItems as $item) {
                $optionCombinationTitle[] = $item->getName();
                $possibleVariationProductOptions[] = array(
                    'product_id' => $item->getProductId(),
                    'product_type' => $product->getTypeId(),
                    'attribute' => $optionTitle,
                    'option' => $item->getName()
                );
            }

            $variationOptionsTitle[$optionTitle] = $optionCombinationTitle;
            $variationOptionsList[] = $possibleVariationProductOptions;
        }

        return array(
            'set' => $variationOptionsTitle,
            'variations' => $variationOptionsList
        );
    }

    protected function _getGroupedOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_GROUPED) {
            return array();
        }

        $optionCombinationTitle = array();

        $possibleVariationProductOptions = array();
        $associatedProducts = $this->getTypeInstance()->getAssociatedProducts();

        foreach ($associatedProducts as $singleProduct) {

            $optionCombinationTitle[] = $singleProduct->getName();

            $possibleVariationProductOptions[] = array(
                'product_id' => $singleProduct->getId(),
                'product_type' => $product->getTypeId(),
                'attribute' => self::GROUPED_PRODUCT_ATTRIBUTE_LABEL,
                'option' => $singleProduct->getName()
            );
        }

        $variationOptionsTitle[self::GROUPED_PRODUCT_ATTRIBUTE_LABEL] = $optionCombinationTitle;
        $variationOptionsList[] = $possibleVariationProductOptions;

        return array(
            'set' => $variationOptionsTitle,
            'variations' => $variationOptionsList
        );
    }

    protected function _getConfigurableOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_CONFIGURABLE) {
            return array();
        }

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getTypeInstance();

        $attributes = array();

        foreach ($productTypeInstance->getConfigurableAttributes($product) as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($this->getStoreId());

            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = $configurableAttribute->getProductAttribute();
            $attribute->setStoreId($this->getStoreId());

            $attributeLabel = '';

            if (!(int)$configurableAttribute->getData('use_default')) {
                $attributeLabel = $configurableAttribute->getData('label');
            }

            if ($attributeLabel == '') {

                if ($this->getStoreId() && $tempStoreLabels = $attribute->getStoreLabels()) {
                    if (isset($tempStoreLabels[$this->getStoreId()])) {
                        $attributeLabel = $tempStoreLabels[$this->getStoreId()];
                    }
                }

                $attributeLabel == '' && $attributeLabel = $attribute->getFrontendLabel();
            }

            $attributes[$attribute->getAttributeCode()] = $attributeLabel;
        }

        $variations = array();

        foreach ($productTypeInstance->getUsedProducts(null, $product) as $childProduct) {

            $variation = array();
            $childProduct->setStoreId($this->getStoreId());

            foreach ($attributes as $attributeCode => $attributeLabel) {

                $attributeValue = Mage::getModel('M2ePro/Magento_Product')
                                            ->setProduct($childProduct)
                                            ->getAttributeValue($attributeCode);

                if (empty($attributeValue)) {
                    break;
                }

                $variation[] = array(
                    'product_id' => $childProduct->getId(),
                    'product_type' => self::TYPE_CONFIGURABLE,
                    'attribute' => $attributeLabel,
                    'attribute_code' => $attributeCode,
                    'option' => $attributeValue,
                );
            }

            if (count($attributes) == count($variation)) {
                $variations[] = $variation;
            }
        }

        $set = array();

        foreach ($variations as $variation) {
            foreach ($variation as $option) {
                if (empty($set[$option['attribute_code']])) {
                    $set[$option['attribute_code']] = array(
                        'label' => $option['attribute'],
                        'options' => array(),
                    );
                }

                $set[$option['attribute_code']]['options'][] = $option['option'];
            }
        }

        $resultSet = array();
        foreach ($set as $code => $data) {
            $options = $this->sortAttributeOptions($code, array_values(array_unique($data['options'])));
            $resultSet[$data['label']] = $options;
        }

        return array(
            'set'        => $resultSet,
            'variations' => $variations
        );
    }

    protected function sortAttributeOptions($attributeCode, $options)
    {
        $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        /** @var Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection $optionCollection */
        $optionCollection = Mage::getModel('eav/entity_attribute_option')->getCollection();
        $optionCollection->setAttributeFilter($attribute->getId());
        $optionCollection->setPositionOrder();
        $optionCollection->setStoreFilter($this->getStoreId());

        $sortedOptions = array();
        foreach ($optionCollection as $option) {
            if (!in_array($option->getValue(), $options) ||
                in_array($option->getValue(), $sortedOptions)) {
                continue;
            }

            $sortedOptions[] = $option->getValue();
        }

        return $sortedOptions;
    }

    //-----------------------------------------

    protected function prepareRawVariations(&$optionsScope, &$set,
                                            $optionScopeIndex = 0)
    {
        $resultVariations = array();

        if (!isset($optionsScope[$optionScopeIndex])) {
            return $resultVariations;
        }

        $subVariations = $this->prepareRawVariations($optionsScope,$set,
                                                     $optionScopeIndex+1);

        if (count($subVariations) <= 0) {

            foreach ($optionsScope[$optionScopeIndex] as $option) {
                $resultVariations[] = array($option);
            }

            return $resultVariations;
        }

        foreach ($optionsScope[$optionScopeIndex] as $option) {

            if (!isset($set[$option['attribute']]) ||
                !in_array($option['option'],$set[$option['attribute']])) {
                continue;
            }

            foreach ($subVariations as $subVariation) {
                $subVariation[] = $option;
                $resultVariations[] = $subVariation;
            }
        }

        return $resultVariations;
    }

    protected function prepareVariationsScope(&$optionsScope)
    {
        $tempArray = array();

        foreach ($optionsScope as $key => $optionScope) {

            $temp = reset($optionScope);
            $attribute = $temp['attribute'];

            if (isset($tempArray[$attribute])) {
                unset($optionsScope[$key]);
                continue;
            }

            $tempArray[$attribute] = 1;
        }
    }

    // ########################################

    public function getProductVariationsForOrder()
    {
        if ($this->isSimpleType()) {
            return $this->_getCustomOptionsForOrder();
        }

        if ($this->isBundleType()) {
            return $this->_getBundleOptionsForOrder();
        }

        if ($this->isGroupedType()) {
            return $this->_getGroupedOptionsForOrder();
        }

        if ($this->isConfigurableType()) {
            return $this->_getConfigurableOptionsForOrder();
        }

        return array();
    }

    //-----------------------------------------

    protected function _getCustomOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_SIMPLE) {
            return array();
        }

        $customOptions = array();

        $productOptions = $product->getOptions();

        foreach ($productOptions as $option) {

            if (!(bool)(int)$option->getData('is_require')) {
                continue;
            }

            $customOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array_filter(array(
                    trim($option->getData('store_title')),
                    trim($option->getData('title')),
                    trim($option->getData('default_title'))
                ))
            );

            $values = $option->getValues();

            foreach ($values as $value) {
                $customOption['values'][] = array(
                    'product_ids' => array($this->getProductId()),
                    'value_id' => $value->getData('option_type_id'),
                    'labels'   => array_filter(array(
                        trim($value->getData('store_title')),
                        trim($value->getData('title')),
                        trim($value->getData('default_title'))
                        //trim($value->getData('sku'))
                    ))
                );
            }

            if (count($customOption['values']) == 0) {
                continue;
            }

            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    protected function _getBundleOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_BUNDLE) {
            return array();
        }

        $bundleOptions = array();

        $productInstance = $this->getTypeInstance();

        $optionsCollection = $productInstance->getOptionsCollection($product);
        $selectionsCollection = $productInstance->getSelectionsCollection($optionsCollection->getAllIds(), $product);

        foreach ($optionsCollection as $option) {
            if (!$option->getData('required')) {
                continue;
            }

            $bundleOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array_filter(array(
                    trim($option->getData('default_title')),
                    trim($option->getData('title'))
                ))
            );

            foreach ($selectionsCollection as $selection) {
                if ($option->getData('option_id') != $selection->getData('option_id')) {
                    continue;
                }

                $bundleOption['values'][] = array(
                    'product_ids' => array($selection->getData('product_id')),
                    'value_id'   => $selection->getData('selection_id'),
                    'labels'     => array(
                        trim($selection->getData('name'))
                    )
                );
            }

            if (count($bundleOption['values']) == 0) {
                continue;
            }

            $bundleOptions[] = $bundleOption;
        }

        return $bundleOptions;
    }

    protected function _getGroupedOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_GROUPED) {
            return array();
        }

        return $this->getTypeInstance()->getAssociatedProducts();
    }

    protected function _getConfigurableOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_CONFIGURABLE) {
            return array();
        }

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getTypeInstance();

        $configurableOptions = array();

        foreach ($productTypeInstance->getConfigurableAttributes($product) as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($this->getStoreId());

            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = $configurableAttribute->getProductAttribute();
            $attribute->setStoreId($this->getStoreId());

            $configurableOption = array(
                'option_id' => $configurableAttribute->getAttributeId(),
                'labels' => array_filter(array_merge(
                    array_map('trim',array_values($attribute->getStoreLabels())),
                    array(
                        trim($configurableAttribute->getData('label')),
                        trim($attribute->getFrontendLabel())
                    )
                )),
                'values' => $this->getConfigurableAttributeValuesForOrder($configurableAttribute)
            );

            if (count($configurableOption['values']) == 0) {
                continue;
            }

            $configurableOptions[] = $configurableOption;
        }

        return $configurableOptions;
    }

    //-----------------------------------------

    private function getConfigurableAttributeValuesForOrder($configurableAttribute)
    {
        $product = $this->getProduct();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getTypeInstance();

        /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $configurableAttribute->getProductAttribute();
        $attribute->setStoreId($this->getStoreId());

        $values = array();
        $options = $this->getConfigurableAttributeOptionsForOrder($attribute);

        foreach ($options as $option) {

            foreach ($productTypeInstance->getUsedProducts(null, $product) as $associatedProduct) {

                if ($option['value_id'] != $associatedProduct->getData($attribute->getAttributeCode())) {
                    continue;
                }

                $attributeOptionKey = $configurableAttribute->getAttributeId() . ':' . $option['value_id'];

                if (!isset($values[$attributeOptionKey])) {
                    $values[$attributeOptionKey] = $option;
                }

                $values[$attributeOptionKey]['product_ids'][] = $associatedProduct->getId();
            }
        }

        return array_values($values);
    }

    private function getConfigurableAttributeOptionsForOrder($attribute)
    {
        $options = $attribute->getSource()->getAllOptions(false, false);
        $defaultOptions = $attribute->getSource()->getAllOptions(false, true);

        $mergedOptions = array();

        foreach ($options as $option) {

            $mergedOption = array(
                'product_ids' => array(),
                'value_id' => $option['value'],
                'labels' => array(
                    trim($option['label'])
                )
            );

            foreach ($defaultOptions as $defaultOption) {
                if ($defaultOption['value'] == $option['value']) {
                    $mergedOption['labels'][] = trim($defaultOption['label']);
                    break;
                }
            }

            $mergedOptions[] = $mergedOption;
        }

        return $mergedOptions;
    }

    // ########################################

    private function addNotFoundAttributes($attributeCode)
    {
        $this->notFoundAttributes[] = $attributeCode;
        $this->notFoundAttributes = array_unique($this->notFoundAttributes);
    }

    // ----------------------------------------

    public function getNotFoundAttributes()
    {
        return $this->notFoundAttributes;
    }

    public function clearNotFoundAttributes()
    {
        $this->notFoundAttributes = array();
    }

    // ########################################
}