<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_Synchronization getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Buy_Template_Synchronization getResource()
 */
class Ess_M2ePro_Model_Buy_Template_Synchronization extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    const LIST_MODE_NONE = 0;
    const LIST_MODE_YES = 1;

    const LIST_STATUS_ENABLED_NONE = 0;
    const LIST_STATUS_ENABLED_YES  = 1;

    const LIST_IS_IN_STOCK_NONE = 0;
    const LIST_IS_IN_STOCK_YES  = 1;

    const LIST_QTY_NONE    = 0;
    const LIST_QTY_LESS    = 1;
    const LIST_QTY_BETWEEN = 2;
    const LIST_QTY_MORE    = 3;

    const REVISE_UPDATE_QTY_NONE = 0;
    const REVISE_UPDATE_QTY_YES  = 1;

    const REVISE_MAX_AFFECTED_QTY_MODE_OFF = 0;
    const REVISE_MAX_AFFECTED_QTY_MODE_ON = 1;

    const REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT = 5;

    const REVISE_UPDATE_PRICE_NONE = 0;
    const REVISE_UPDATE_PRICE_YES  = 1;

    const REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF = 0;
    const REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON  = 1;

    const REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT = 3;

    const REVISE_UPDATE_TITLE_NONE = 0;
    const REVISE_UPDATE_TITLE_YES  = 1;

    const REVISE_UPDATE_DESCRIPTION_NONE = 0;
    const REVISE_UPDATE_DESCRIPTION_YES  = 1;

    const REVISE_UPDATE_SUB_TITLE_NONE = 0;
    const REVISE_UPDATE_SUB_TITLE_YES  = 1;

    const RELIST_FILTER_USER_LOCK_NONE = 0;
    const RELIST_FILTER_USER_LOCK_YES  = 1;

    const RELIST_MODE_NONE = 0;
    const RELIST_MODE_YES  = 1;

    const RELIST_STATUS_ENABLED_NONE = 0;
    const RELIST_STATUS_ENABLED_YES  = 1;

    const RELIST_IS_IN_STOCK_NONE = 0;
    const RELIST_IS_IN_STOCK_YES  = 1;

    const RELIST_QTY_NONE    = 0;
    const RELIST_QTY_LESS    = 1;
    const RELIST_QTY_BETWEEN = 2;
    const RELIST_QTY_MORE    = 3;

    const STOP_STATUS_DISABLED_NONE = 0;
    const STOP_STATUS_DISABLED_YES  = 1;

    const STOP_OUT_OFF_STOCK_NONE = 0;
    const STOP_OUT_OFF_STOCK_YES  = 1;

    const STOP_QTY_NONE    = 0;
    const STOP_QTY_LESS    = 1;
    const STOP_QTY_BETWEEN = 2;
    const STOP_QTY_MORE    = 3;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_Synchronization');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Buy_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize();
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getRelatedComponentItems('Listing','template_synchronization_id',$asObjects,$filters);
    }

    // ########################################

    public function isListMode()
    {
        return $this->getData('list_mode') != self::LIST_MODE_NONE;
    }

    public function isListStatusEnabled()
    {
        return $this->getData('list_status_enabled') != self::LIST_STATUS_ENABLED_NONE;
    }

    public function isListIsInStock()
    {
        return $this->getData('list_is_in_stock') != self::LIST_IS_IN_STOCK_NONE;
    }

    public function isListWhenQtyMagentoHasValue()
    {
        return $this->getData('list_qty_magento') != self::LIST_QTY_NONE;
    }

    public function isListWhenQtyCalculatedHasValue()
    {
        return $this->getData('list_qty_calculated') != self::LIST_QTY_NONE;
    }

    //------------------------

    public function getReviseUpdateQtyMaxAppliedValueMode()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value_mode');
    }

    public function isReviseUpdateQtyMaxAppliedValueModeOn()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == self::REVISE_MAX_AFFECTED_QTY_MODE_ON;
    }

    public function isReviseUpdateQtyMaxAppliedValueModeOff()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == self::REVISE_MAX_AFFECTED_QTY_MODE_OFF;
    }

    //------------------------

    public function getReviseUpdateQtyMaxAppliedValue()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value');
    }

    //------------------------

    public function getReviseUpdatePriceMaxAllowedDeviationMode()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation_mode');
    }

    public function isReviseUpdatePriceMaxAllowedDeviationModeOn()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode() == self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON;
    }

    public function isReviseUpdatePriceMaxAllowedDeviationModeOff()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode()
                    == self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF;
    }

    //------------------------

    public function getReviseUpdatePriceMaxAllowedDeviation()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation');
    }

    //------------------------

    public function isReviseWhenChangeQty()
    {
        return $this->getData('revise_update_qty') != self::REVISE_UPDATE_QTY_NONE;
    }

    public function isReviseWhenChangePrice()
    {
        return $this->getData('revise_update_price') != self::REVISE_UPDATE_PRICE_NONE;
    }

    //------------------------

    public function isRelistMode()
    {
        return $this->getData('relist_mode') != self::RELIST_MODE_NONE;
    }

    public function isRelistFilterUserLock()
    {
        return $this->getData('relist_filter_user_lock') != self::RELIST_FILTER_USER_LOCK_NONE;
    }

    public function isRelistStatusEnabled()
    {
        return $this->getData('relist_status_enabled') != self::RELIST_STATUS_ENABLED_NONE;
    }

    public function isRelistIsInStock()
    {
        return $this->getData('relist_is_in_stock') != self::RELIST_IS_IN_STOCK_NONE;
    }

    public function isRelistWhenQtyMagentoHasValue()
    {
        return $this->getData('relist_qty_magento') != self::RELIST_QTY_NONE;
    }

    public function isRelistWhenQtyCalculatedHasValue()
    {
        return $this->getData('relist_qty_calculated') != self::RELIST_QTY_NONE;
    }

    //------------------------

    public function isStopStatusDisabled()
    {
        return $this->getData('stop_status_disabled') != self::STOP_STATUS_DISABLED_NONE;
    }

    public function isStopOutOfStock()
    {
        return $this->getData('stop_out_off_stock') != self::STOP_OUT_OFF_STOCK_NONE;
    }

    public function isStopWhenQtyMagentoHasValue()
    {
        return $this->getData('stop_qty_magento') != self::STOP_QTY_NONE;
    }

    public function isStopWhenQtyCalculatedHasValue()
    {
        return $this->getData('stop_qty_calculated') != self::STOP_QTY_NONE;
    }

    // ########################################

    public function getListWhenQtyMagentoHasValueType()
    {
        return $this->getData('list_qty_magento');
    }

    public function getListWhenQtyMagentoHasValueMin()
    {
        return $this->getData('list_qty_magento_value');
    }

    public function getListWhenQtyMagentoHasValueMax()
    {
        return $this->getData('list_qty_magento_value_max');
    }

    // ---------------------

    public function getListWhenQtyCalculatedHasValueType()
    {
        return $this->getData('list_qty_calculated');
    }

    public function getListWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('list_qty_calculated_value');
    }

    public function getListWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('list_qty_calculated_value_max');
    }

    // ---------------------

    public function getRelistWhenQtyMagentoHasValueType()
    {
        return $this->getData('relist_qty_magento');
    }

    public function getRelistWhenQtyMagentoHasValueMin()
    {
        return $this->getData('relist_qty_magento_value');
    }

    public function getRelistWhenQtyMagentoHasValueMax()
    {
        return $this->getData('relist_qty_magento_value_max');
    }

    //------------------------

    public function getRelistWhenQtyCalculatedHasValueType()
    {
        return $this->getData('relist_qty_calculated');
    }

    public function getRelistWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('relist_qty_calculated_value');
    }

    public function getRelistWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('relist_qty_calculated_value_max');
    }

    //------------------------

    public function getStopWhenQtyMagentoHasValueType()
    {
        return $this->getData('stop_qty_magento');
    }

    public function getStopWhenQtyMagentoHasValueMin()
    {
        return $this->getData('stop_qty_magento_value');
    }

    public function getStopWhenQtyMagentoHasValueMax()
    {
        return $this->getData('stop_qty_magento_value_max');
    }

    //------------------------

    public function getStopWhenQtyCalculatedHasValueType()
    {
        return $this->getData('stop_qty_calculated');
    }

    public function getStopWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('stop_qty_calculated_value');
    }

    public function getStopWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('stop_qty_calculated_value_max');
    }

    // #######################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Collection $listingCollection */
        $listingCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing');
        $listingCollection->addFieldToFilter('template_synchronization_id', $this->getId());
        $listingCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingCollection->getSelect()->columns('id');

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id',array('in' => $listingCollection->getSelect()));

        if (is_array($columns) && !empty($columns)) {
            $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $listingProductCollection->getSelect()->columns($columns);
        }

        return $asArrays ? (array)$listingProductCollection->getData() : (array)$listingProductCollection->getItems();
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id', 'synch_status', 'synch_reasons'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::delete();
    }

    // ########################################
}