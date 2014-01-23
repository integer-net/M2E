<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_OtherCategory extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_OtherCategory');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->marketplaceModel = NULL;
        $this->accountModel = NULL;
        $this->magentoProductModel = NULL;

        $this->delete();
        return true;
    }

    // #######################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Marketplace', $this->getMarketplaceId()
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

    //---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account', $this->getAccountId()
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

    //---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // #######################################

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    //---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    // #######################################

    public function getCategorySecondarySource()
    {
        return array(
            'mode'      => $this->getData('category_secondary_mode'),
            'value'     => $this->getData('category_secondary_id'),
            'path'      => $this->getData('category_secondary_path'),
            'attribute' => $this->getData('category_secondary_attribute')
        );
    }

    public function getStoreCategoryMainSource()
    {
        return array(
            'mode'      => $this->getData('store_category_main_mode'),
            'value'     => $this->getData('store_category_main_id'),
            'path'      => $this->getData('store_category_main_path'),
            'attribute' => $this->getData('store_category_main_attribute')
        );
    }

    public function getStoreCategorySecondarySource()
    {
        return array(
            'mode'      => $this->getData('store_category_secondary_mode'),
            'value'     => $this->getData('store_category_secondary_id'),
            'path'      => $this->getData('store_category_secondary_path'),
            'attribute' => $this->getData('store_category_secondary_attribute')
        );
    }

    //----------------------------------------

    public function getSecondaryCategory()
    {
        $src = $this->getCategorySecondarySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getStoreCategoryMain()
    {
        $src = $this->getStoreCategoryMainSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getStoreCategorySecondary()
    {
        $src = $this->getStoreCategorySecondarySource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    // #######################################

    public function getDefaultSettings()
    {
        return array(

            'category_secondary_id' => 0,
            'category_secondary_path' => '',
            'category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'category_secondary_attribute' => '',

            'store_category_main_id' => 0,
            'store_category_main_path' => '',
            'store_category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'store_category_main_attribute' => '',

            'store_category_secondary_id' => 0,
            'store_category_secondary_path' => '',
            'store_category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'store_category_secondary_attribute' => ''
        );
    }

    // #######################################

    public function getAffectedListingProducts($asObjects = false, $key = NULL)
    {
        if (is_null($this->getId())) {
            throw new LogicException('Method require loaded instance first');
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('template_other_category_id', $this->getId());

        if (!is_null($key)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($key);
        }

        $listingProducts = $asObjects ? $collection->getItems() : $collection->getData();

        if (is_null($key)) {
            return $listingProducts;
        }

        $return = array();
        foreach ($listingProducts as $listingProduct) {
            isset($listingProduct[$key]) && $return[] = $listingProduct[$key];
        }

        return $return;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        if (!$this->getResource()->isDifferent($newData,$oldData)) {
            return;
        }

        $ids = $this->getAffectedListingProducts(false,'id');

        if (empty($ids)) {
            return;
        }

        $templates = array('categoryTemplate');

        Mage::getSingleton('core/resource')->getConnection('core_read')->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing_Product'),
            array(
                'synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED,
                'synch_reasons' => new Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',',$templates)."',
                        CONCAT(synch_reasons,'".','.implode(',',$templates)."')
                    )"
                )
            ),
            array('id IN ('.implode(',', $ids).')')
        );
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_othercategory');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_othercategory');
        return parent::delete();
    }

    // #######################################
}