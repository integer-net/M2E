<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_Category getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Category extends Ess_M2ePro_Model_Component_Abstract
{
    const CATEGORY_MODE_NONE       = 0;
    const CATEGORY_MODE_EBAY       = 1;
    const CATEGORY_MODE_ATTRIBUTE  = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }

        $this->marketplaceModel = NULL;
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

    public function getSpecifics($asObjects = false, array $filters = array())
    {
        $specifics = $this->getRelatedSimpleItems('Ebay_Template_Category_Specific','template_category_id',
                                                  $asObjects, $filters);

        if ($asObjects) {
            foreach ($specifics as $specific) {

                /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */
                $specific->setCategoryTemplate($this);

                /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */
                if (!is_null($this->getMagentoProduct())) {
                    $specific->setMagentoProduct($this->getMagentoProduct());
                }
            }
        }

        return $specifics;
    }

    // #######################################

    public function getCategoryMainId()
    {
        return (int)$this->getData('category_main_id');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
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

    public function getCategoryMainSource()
    {
        return array(
            'mode'      => $this->getData('category_main_mode'),
            'value'     => $this->getData('category_main_id'),
            'path'      => $this->getData('category_main_path'),
            'attribute' => $this->getData('category_main_attribute')
        );
    }

    //----------------------------------------

    public function getMainCategory()
    {
        $src = $this->getCategoryMainSource();

        if ($src['mode'] == self::CATEGORY_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // #######################################

    public function getCategoryPath(Ess_M2ePro_Model_Listing $listing, $withId = true)
    {
        $data = array(
            'category_main_id' => $this->getData('category_main_id'),
            'category_main_mode' => $this->getData('category_main_mode'),
            'category_main_path' => $this->getData('category_main_path'),
            'category_main_attribute' => $this->getData('category_main_attribute'),
        );

        Mage::helper('M2ePro/Component_Ebay_Category')->fillCategoriesPaths($data,$listing);

        if ($withId && $this->getData('category_main_mode') == self::CATEGORY_MODE_EBAY) {
            $data['category_main_path'] .= ' ('.$data['category_main_id'].')';
        }

        return $data['category_main_path'];
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array();
    }

    public function getUsedAttributes()
    {
        $usedAttributes = array();

        if ($this->getData('category_main_mode') == self::CATEGORY_MODE_ATTRIBUTE) {
            $usedAttributes[] = $this->getData('category_main_attribute');
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Specific $specificModel */
        foreach($this->getSpecifics(true) as $specificModel) {
            $usedAttributes = array_merge($usedAttributes, $specificModel->getUsedAttributes());
        }

        return array_values(array_unique($usedAttributes));
    }

    // #######################################

    public function getDataSnapshot()
    {
        $data = parent::getDataSnapshot();
        $data['specifics'] = $this->getSpecifics();

        foreach ($data['specifics'] as &$specificData) {
            foreach ($specificData as &$value) {
                !is_null($value) && !is_array($value) && $value = (string)$value;
            }
        }

        return $data;
    }

    public function getDefaultSettings()
    {
        return array(

            'category_main_id' => 0,
            'category_main_path' => '',
            'category_main_mode' => self::CATEGORY_MODE_EBAY,
            'category_main_attribute' => ''
        );
    }

    // #######################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('template_category_id', $this->getId());

        if (is_array($columns) && !empty($columns)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$collection->getData() : (array)$collection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_category');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_category');
        return parent::delete();
    }

    // #######################################
}