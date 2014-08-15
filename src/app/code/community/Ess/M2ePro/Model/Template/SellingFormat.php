<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Template_SellingFormat extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_SellingFormat');
    }

    // ########################################

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
        $collection->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT);
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

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    //-----------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getChildObject()->getTrackingAttributes();
    }

    public function getUsedAttributes()
    {
        return $this->getChildObject()->getUsedAttributes();
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('template_sellingformat');
        return parent::delete();
    }

    // #######################################
}