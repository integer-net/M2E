<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
 */
class Ess_M2ePro_Model_Buy_Template_Description extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_Description');
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getListings($asObjects,$filters);
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array();
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