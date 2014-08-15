<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Marketplace extends Ess_M2ePro_Model_Component_Child_Play_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Play_Marketplace');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Play_Account')->getCollection()->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $items = $this->getRelatedSimpleItems('Play_Item','marketplace_id',true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $this->delete();
        return true;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('marketplace');
        return parent::delete();
    }

    // ########################################
}
