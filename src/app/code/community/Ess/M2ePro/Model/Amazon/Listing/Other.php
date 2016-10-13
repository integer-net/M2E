<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Other getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Listing_Other extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const EMPTY_TITLE_PLACEHOLDER = '--';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Other');
    }

    //########################################

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

    //########################################

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return mixed
     */
    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    /**
     * @return int
     */
    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAfnChannel()
    {
        return (int)$this->getData('is_afn_channel') ==
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_YES;
    }

    /**
     * @return bool
     */
    public function isIsbnGeneralId()
    {
        return (int)$this->getData('is_isbn_general_id') ==
            Ess_M2ePro_Model_Amazon_Listing_Product::IS_ISBN_GENERAL_ID_YES;
    }

    //########################################

    /**
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId();
    }

    //########################################

    public function afterMapProduct()
    {
        $dataForAdd = array(
            'account_id' => $this->getParentObject()->getAccountId(),
            'marketplace_id' => $this->getParentObject()->getMarketplaceId(),
            'sku' => $this->getSku(),
            'product_id' => $this->getParentObject()->getProductId(),
            'store_id' => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        $existedRelation = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(array('ai' => Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable()),
                array('alp.listing_product_id'))
            ->join(array('alp' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable()),
                '(`alp`.`sku` = `ai`.`sku`)', array())
            ->where('`ai`.`sku` = ?', $this->getSku())
            ->where('`ai`.`account_id` = ?', $this->getParentObject()->getAccountId())
            ->where('`ai`.`marketplace_id` = ?', $this->getParentObject()->getMarketplaceId())
            ->query()
            ->fetchColumn();

        if ($existedRelation) {
            return;
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable(),
                    array(
                        '`account_id` = ?' => $this->getParentObject()->getAccountId(),
                        '`marketplace_id` = ?' => $this->getParentObject()->getMarketplaceId(),
                        '`sku` = ?' => $this->getSku(),
                        '`product_id` = ?' => $this->getParentObject()->getProductId()
                    ));
    }

    //########################################
}