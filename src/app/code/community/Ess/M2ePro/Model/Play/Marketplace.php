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

    public function getPlayItems($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Play_Item','marketplace_id',$asObjects,$filters);
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('marketplace');
        return parent::delete();
    }

    // ########################################
}
