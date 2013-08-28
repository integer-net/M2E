<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
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

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
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

    //------------------------------------------

    public function getListings($asObjects = false, array $filters = array())
    {
        $listings = $this->getRelatedComponentItems('Listing','template_selling_format_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($listings as $listing) {
                /** @var $listing Ess_M2ePro_Model_Listing */
                $listing->setSellingFormatTemplate($this);
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

    // #######################################

    public function parsePrice($price, $coefficient = false)
    {
        if (is_string($coefficient)) {
            $coefficient = trim($coefficient);
        }

        if ($price <= 0) {
            return 0;
        }

        if (!$coefficient) {
            return round($price, 2);
        }

        if (strpos($coefficient, '%')) {
            $coefficient = str_replace('%', '', $coefficient);

            if (preg_match('/^[+-]/', $coefficient)) {
                return round($price + $price * (float)$coefficient / 100, 2);
            }
            return round($price * (float)$coefficient / 100, 2);
        }

        if (preg_match('/^[+-]/', $coefficient)) {
            return round($price + (float)$coefficient, 2);
        }

        return round($price * (float)$coefficient, 2);
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getChildObject()->getTrackingAttributes();
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_sellingformat');
        return parent::delete();
    }

    // #######################################
}