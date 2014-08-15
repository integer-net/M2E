<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Cache extends Ess_M2ePro_Model_Magento_Product
{
    private $cache = NULL;
    private $isCacheEnabled = false;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Cache_Session_Object
     * */
    private function getCache()
    {
        if (is_null($this->cache)) {
            $this->cache = Mage::getSingleton('M2ePro/Cache_Session_Dispatcher')->getCache(array(
                'class' => __CLASS__,
                'store_id' => $this->getStoreId(),
                'product_id' => $this->getProductId()
            ));
        }

        return $this->cache;
    }

    // ########################################

    public function clearCache()
    {
        Mage::getSingleton('M2ePro/Cache_Session_Dispatcher')->clearCache(array(
            'class' => __CLASS__
        ));
        return $this;
    }

    public function enableCache()
    {
        $this->isCacheEnabled = true;
        return $this;
    }

    public function disableCache()
    {
        $this->isCacheEnabled = false;
        return $this;
    }

    // ########################################

    /**
     * @param int|null $productId
     * @param int|null $storeId
     * @throws Exception
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function loadProduct($productId = NULL, $storeId = NULL)
    {
        $this->cache = NULL;
        return parent::loadProduct($productId,$storeId);
    }

    // ########################################

    /**
     * @param int $productId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setProductId($productId)
    {
        $this->cache = NULL;
        return parent::setProductId($productId);
    }

    // ########################################

    /**
     * @param int $storeId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setStoreId($storeId)
    {
        $this->cache = NULL;
        return parent::setStoreId($storeId);
    }

    // ########################################

    /**
     * @param Mage_Catalog_Model_Product $productModel
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setProduct(Mage_Catalog_Model_Product $productModel)
    {
        $this->cache = NULL;
        return parent::setProduct($productModel);
    }

    // ########################################

    public function exists()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::exists());
    }

    // ########################################

    /**
     * @return Mage_Catalog_Model_Product_Type_Abstract
     * @throws Exception
     */
    public function getTypeInstance()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getTypeInstance());
    }

    /**
     * @return Mage_CatalogInventory_Model_Stock_Item
     * @throws Exception
     */
    public function getStockItem()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getStockItem());
    }

    // ########################################

    public function getTypeId()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getTypeId());
    }

    // ########################################

    public function isSimpleTypeWithCustomOptions()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::isSimpleTypeWithCustomOptions());
    }

    // ########################################

    public function getSku()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getSku());
    }

    // ########################################

    public function getName()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getName());
    }

    // ########################################

    public function isStatusEnabled()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::isStatusEnabled());
    }

    // ########################################

    public function isStockAvailability()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::isStockAvailability());
    }

    // ########################################

    public function getPrice()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        $parent = parent::getPrice();

        return $this->getCache()->setData($cacheKey,$parent);
    }

    // ########################################

    public function getSpecialPrice()
    {
        $cacheKey = array(
            __METHOD__
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getSpecialPrice());
    }

    // ########################################

    public function getQty($lifeMode = false)
    {
        $cacheKey = array(
            __METHOD__,
            func_get_args()
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getQty($lifeMode));
    }

    // ########################################

    public function getAttributeValue($attributeCode)
    {
        $cacheKey = array(
            __METHOD__,
            func_get_args()
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getAttributeValue($attributeCode));
    }

    // ########################################

    public function getThumbnailImageLink()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getThumbnailImageLink());
    }

    public function getImageLink($attribute = 'image')
    {
        $cacheKey = array(
            __METHOD__,
            func_get_args()
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getImageLink($attribute));
    }

    public function getGalleryImagesLinks($limitImages = 0)
    {
        $cacheKey = array(
            __METHOD__,
            func_get_args()
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getGalleryImagesLinks($limitImages));
    }

    // ########################################

    public function hasRequiredOptions()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::hasRequiredOptions());
    }

    // ########################################

    public function getProductVariations()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::getProductVariations());
    }

    // ----------------------------------------

    protected function _getCustomOptionsForVariation()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getCustomOptionsForVariation());
    }

    // ----------------------------------------

    protected function _getBundleOptionsForVariation()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getBundleOptionsForVariation());
    }

    // ----------------------------------------

    protected function _getGroupedOptionsForVariation()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getGroupedOptionsForVariation());
    }

    // ----------------------------------------

    protected function _getConfigurableOptionsForVariation()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getConfigurableOptionsForVariation());
    }

    // ########################################

    protected function _getCustomOptionsForOrder()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getCustomOptionsForOrder());
    }

    protected function _getBundleOptionsForOrder()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getBundleOptionsForOrder());
    }

    protected function _getGroupedOptionsForOrder()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getGroupedOptionsForOrder());
    }

    protected function _getConfigurableOptionsForOrder()
    {
        $cacheKey = array(
            __METHOD__,
        );

        if ($this->isCacheEnabled && !is_null($cacheResult = $this->getCache()->getData($cacheKey))) {
            return $cacheResult;
        }

        return $this->getCache()->setData($cacheKey,parent::_getConfigurableOptionsForOrder());
    }

    // ########################################
}