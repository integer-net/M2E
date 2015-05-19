<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Observer_Product_Abstract extends Ess_M2ePro_Model_Observer_Abstract
{
    /**
     * @var null|Mage_Catalog_Model_Product
     */
    private $product = NULL;

    /**
     * @var null|int
     */
    private $productId = NULL;
    /**
     * @var null|int
     */
    private $storeId = NULL;

    /**
     * @var null|Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = NULL;

    //####################################

    public function beforeProcess()
    {
        $product = $this->getEvent()->getProduct();

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            throw new Exception('Product event doesn\'t have correct Product instance.');
        }

        $this->product = $product;

        $this->productId = (int)$this->product->getId();
        $this->storeId = (int)$this->product->getData('store_id');
    }

    //####################################

    /**
     * @return Mage_Catalog_Model_Product
     * @throws LogicException
     */
    protected function getProduct()
    {
        if (!($this->product instanceof Mage_Catalog_Model_Product)) {
            throw new LogicException('Property "Product" should be set first.');
        }

        return $this->product;
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws LogicException
     */
    protected function reloadProduct()
    {
        if ($this->getProductId() <= 0) {
            throw new LogicException('To reload Product instance product_id should be greater than 0.');
        }

        $this->product = Mage::getModel('catalog/product')->setStoreId($this->getStoreId())
                                                          ->load($this->getProductId());

        return $this->getProduct();
    }

    // -----------------------------------

    /**
     * @return int
     */
    protected function getProductId()
    {
        return (int)$this->productId;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->storeId;
    }

    //####################################

    protected function isAdminDefaultStoreId()
    {
        return $this->getStoreId() == Mage_Core_Model_App::ADMIN_STORE_ID;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     * @throws LogicException
     */
    protected function getMagentoProduct()
    {
        if (!empty($this->magentoProduct)) {
            return $this->magentoProduct;
        }

        if ($this->getProductId() <= 0) {
            throw new LogicException('To load Magento Product instance product_id should be greater than 0.');
        }

        return $this->magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($this->getProduct());
    }

    //####################################
}