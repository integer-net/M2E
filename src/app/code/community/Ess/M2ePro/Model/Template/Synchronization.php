<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Template_Synchronization extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const REVISE_CHANGE_GENERAL_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_GENERAL_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_DESCRIPTION_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES  = 1;

    const REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT = 10;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_Synchronization');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize();
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        $listings = $this->getRelatedComponentItems('Listing','template_synchronization_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($listings as $listing) {
                /** @var $listing Ess_M2ePro_Model_Listing */
                $listing->setSynchronizationTemplate($this);
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

    //-----------------------------------------

    public function isReviseGeneralTemplate()
    {
        return (int)$this->getData('revise_change_general_template') != self::REVISE_CHANGE_GENERAL_TEMPLATE_NONE;
    }

    public function isReviseDescriptionTemplate()
    {
        return (int)$this->getData('revise_change_description_template') !=
            self::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE;
    }

    public function isReviseSellingFormatTemplate()
    {
        return (int)$this->getData('revise_change_selling_format_template') !=
            self::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_synchronization');
        return parent::delete();
    }

    // ########################################
}