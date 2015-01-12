<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing_Other getParentObject()
 */
class Ess_M2ePro_Model_Play_Listing_Other extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Listing_Other');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    // ########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    //-----------------------------------------

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    public function getGeneralIdType()
    {
        return $this->getData('general_id_type');
    }

    //-----------------------------------------

    public function getPlayListingId()
    {
        return (int)$this->getData('play_listing_id');
    }

    //-----------------------------------------

    public function getLinkInfo()
    {
        return $this->getData('link_info');
    }

    //-----------------------------------------

    public function getDispatchTo()
    {
        return $this->getData('dispatch_to');
    }

    public function getDispatchFrom()
    {
        return $this->getData('dispatch_from');
    }

    //-----------------------------------------

    public function getOnlinePriceGbr()
    {
        return (float)$this->getData('online_price_gbr');
    }

    public function getOnlinePriceEuro()
    {
        return (float)$this->getData('online_price_euro');
    }

    //-----------------------------------------

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function getCondition()
    {
        return $this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    //-----------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    // ########################################

    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId();
    }

    // ########################################

    public function afterMapProduct()
    {
        $dataForAdd = array(
            'account_id' => $this->getParentObject()->getAccountId(),
            'marketplace_id' => $this->getParentObject()->getMarketplaceId(),
            'sku' => $this->getSku(),
            'product_id' => $this->getParentObject()->getProductId(),
            'store_id' => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Play_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(Mage::getResourceModel('M2ePro/Play_Item')->getMainTable(),
            array(
                '`account_id` = ?' => $this->getParentObject()->getAccountId(),
                '`marketplace_id` = ?' => $this->getParentObject()->getMarketplaceId(),
                '`sku` = ?' => $this->getSku(),
                '`product_id` = ?' => $this->getParentObject()->getProductId()
            ));
    }

    // ########################################
}