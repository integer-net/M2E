<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing');
    }

    // ########################################

    public function getGeneralTemplate()
    {
        return $this->getParentObject()->getGeneralTemplate();
    }

    public function getSellingFormatTemplate()
    {
        return $this->getParentObject()->getSellingFormatTemplate();
    }

    public function getDescriptionTemplate()
    {
        return $this->getParentObject()->getDescriptionTemplate();
    }

    public function getSynchronizationTemplate()
    {
        return $this->getParentObject()->getSynchronizationTemplate();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Buy_Template_General
     */
    public function getBuyGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_SellingFormat
     */
    public function getBuySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Description
     */
    public function getBuyDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Synchronization
     */
    public function getBuySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects,$filters);
    }

    public function getCategories($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getCategories($asObjects,$filters);
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('listing');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('listing');
        return parent::delete();
    }

    // ########################################
}