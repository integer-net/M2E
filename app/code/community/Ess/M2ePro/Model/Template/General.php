<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Template_General extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_General');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_general_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $attributeSets = $this->getAttributeSets();
        foreach ($attributeSets as $attributeSet) {
            $attributeSet->deleteInstance();
        }

        $this->accountModel = NULL;
        $this->marketplaceModel = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Account',$this->getAccountId()
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
                $this->getComponentMode(),'Marketplace',$this->getMarketplaceId()
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

    // ########################################

    public function getAttributeSets()
    {
        $temp = $this->getData('cache_attribute_sets');

        if (!empty($temp)) {
            return $temp;
        }

        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL);
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

    public function getListings($asObjects = false, array $filters = array())
    {
        $listings = $this->getRelatedComponentItems('Listing','template_general_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($listings as $listing) {
                /** @var $listing Ess_M2ePro_Model_Listing */
                $listing->setGeneralTemplate($this);
            }
        }

        return $listings;
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getSynchDate()
    {
        return $this->getData('synch_date');
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getChildObject()->getTrackingAttributes();
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_general');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_general');
        return parent::delete();
    }

    // ########################################
}