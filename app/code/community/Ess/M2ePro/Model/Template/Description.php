<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Template_Description extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_Description');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_description_id', $this->getId())
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

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    public function getAttributeSets()
    {
        $temp = $this->getData('cache_attribute_sets');

        if (!empty($temp)) {
            return $temp;
        }

        $collection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $collection->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION);
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
        $listings = $this->getRelatedComponentItems('Listing','template_description_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($listings as $listing) {
                /** @var $listing Ess_M2ePro_Model_Listing */
                $listing->setDescriptionTemplate($this);
            }
        }

        return $listings;
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
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
        Mage::helper('M2ePro')->removeTagCacheValues('template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_description');
        return parent::delete();
    }

    // ########################################
}