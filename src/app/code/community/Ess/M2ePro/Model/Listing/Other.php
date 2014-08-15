<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Other extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ########################################

    public $isCacheEnabled = false;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Other');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->accountModel = NULL;
        $temp && $this->marketplaceModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Account',$this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Marketplace',$this->getData('marketplace_id')
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     * @throws Exception
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        if (is_null($this->getProductId())) {
            throw new Exception('Product id is not set');
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache')
                                                    ->setStoreId($this->getChildObject()->getRelatedStoreId())
                                                    ->setProductId($this->getProductId());
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // ########################################

    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getProductId()
    {
        $temp = $this->getData('product_id');
        return is_null($temp) ? NULL : (int)$temp;
    }

    public function getAdditionalData()
    {
        $additionalData = $this->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        return is_array($additionalData) ? $additionalData : array();
    }

    //---------------------------------------

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getStatusChanger()
    {
        return (int)$this->getData('status_changer');
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
    }

    public function isUnknown()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
    }

    public function isBlocked()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
    }

    //-----------------------------------------

    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
    }

    //-----------------------------------------

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
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isStoppable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    // ########################################

    public function reviseAction(array $params = array())
    {
        return $this->getChildObject()->reviseAction($params);
    }

    public function relistAction(array $params = array())
    {
        return $this->getChildObject()->relistAction($params);
    }

    public function stopAction(array $params = array())
    {
        return $this->getChildObject()->stopAction($params);
    }

    // ########################################

    public function unmapDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        $listingsOther = Mage::getModel('M2ePro/Listing_Other')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        foreach ($listingsOther as $listingOther) {
            $listingOther->unmapProduct(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        }
    }

    //-----------------------------------------

    public function mapProduct($productId, $logsInitiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->addData(array('product_id'=>$productId))->save();
        $this->getChildObject()->afterMapProduct();

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage($this->getId(),
            $logsInitiator,
            NULL,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_MAP_LISTING,
            // M2ePro_TRANSLATIONS
            // Item was successfully mapped
            'Item was successfully mapped',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
    }

    public function unmapProduct($logsInitiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN)
    {
        $this->getChildObject()->beforeUnmapProduct();
        $this->setData('product_id', NULL)->save();

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage($this->getId(),
            $logsInitiator,
            NULL,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNMAP_LISTING,
            // M2ePro_TRANSLATIONS
            // Item was successfully unmapped
            'Item was successfully unmapped',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
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
}