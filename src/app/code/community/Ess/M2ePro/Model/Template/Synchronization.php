<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Template_Synchronization extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const REVISE_CHANGE_LISTING_NONE = 0;
    const REVISE_CHANGE_LISTING_YES  = 1;

    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES  = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_Synchronization');
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    //-----------------------------------------

    public function isReviseListing()
    {
        return (int)$this->getData('revise_change_listing') != self::REVISE_CHANGE_LISTING_NONE;
    }

    public function isReviseSellingFormatTemplate()
    {
        return (int)$this->getData('revise_change_selling_format_template') !=
            self::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE;
    }

    // #######################################

    public function setSynchStatusNeed($newData, $oldData, $settings)
    {
        $settings = $this->getFullReviseSettingWhichWereEnabled($newData, $oldData, $settings);

        if (!$settings) {
            return;
        }

        $listingProducts = $this->getChildObject()->getAffectedListingProducts();

        $idsByReasonDictionary = array();
        foreach ($listingProducts as $listingProduct) {

            if ($listingProduct['synch_status'] != Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP) {
                continue;
            }

            $listingProductSynchReasons = array_unique(array_filter(explode(',',$listingProduct['synch_reasons'])));
            foreach ($listingProductSynchReasons as $reason) {
                $idsByReasonDictionary[$reason][] = $listingProduct['id'];
            }
        }

        $idsForUpdate = array();
        foreach ($settings as $reason => $setting) {

            if (!isset($idsByReasonDictionary[$reason])) {
                continue;
            }

            $idsForUpdate = array_merge($idsForUpdate, $idsByReasonDictionary[$reason]);
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
            array('synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED),
            array('id IN (?)' => array_unique($idsForUpdate))
        );
    }

    public function getFullReviseSettingWhichWereEnabled($newData, $oldData, $settings)
    {
        foreach ($settings as $reason => $setting) {

            if (!isset($newData[$setting], $oldData[$setting])) {
                unset($settings[$reason]);
                continue;
            }

            // we need change from 0 to 1 only
            if ($oldData[$setting] || !$newData[$setting]) {
                unset($settings[$reason]);
                continue;
            }
        }

        return $settings;
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('template_synchronization');
        return parent::delete();
    }

    // ########################################
}