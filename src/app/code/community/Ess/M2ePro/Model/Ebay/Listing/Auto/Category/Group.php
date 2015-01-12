<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Auto_Category_Group');
    }

    // ########################################

    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    // #######################################

    public function deleteInstance()
    {
        $items = $this->getRelatedSimpleItems('Ebay_Listing_Auto_Category', 'group_id', true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        return parent::deleteInstance();
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_listing_auto_category_group');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_listing_auto_category_group');
        return parent::delete();
    }

    // #######################################
}