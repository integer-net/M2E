<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Item extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Item');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product')
                ->setStoreId($this->getStoreId())
                ->setProductId($this->getProductId());
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // ########################################

    public function getItemId()
    {
        return (double)$this->getData('item_id');
    }

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    // ########################################
}