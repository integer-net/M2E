<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_General getParentObject()
 */
class Ess_M2ePro_Model_Buy_Template_General extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    const SKU_MODE_NOT_SET          = 0;
    const SKU_MODE_PRODUCT_ID       = 3;
    const SKU_MODE_DEFAULT          = 1;
    const SKU_MODE_CUSTOM_ATTRIBUTE = 2;

    const GENERATE_SKU_MODE_NO  = 0;
    const GENERATE_SKU_MODE_YES = 1;

    const GENERAL_ID_MODE_NOT_SET       = 0;
    const GENERAL_ID_MODE_GENERAL_ID    = 1;
    const GENERAL_ID_MODE_ISBN          = 2;
    const GENERAL_ID_MODE_WORLDWIDE     = 3;
    const GENERAL_ID_MODE_SELLER_SKU    = 4;

    const SEARCH_BY_MAGENTO_TITLE_MODE_NONE = 0;
    const SEARCH_BY_MAGENTO_TITLE_MODE_YES  = 1;

    const CONDITION_MODE_NOT_SET          = 0;
    const CONDITION_MODE_DEFAULT          = 1;
    const CONDITION_MODE_CUSTOM_ATTRIBUTE = 2;

    const CONDITION_NEW                    = 1;
    const CONDITION_USED_LIKE_NEW          = 2;
    const CONDITION_USED_VERY_GOOD         = 3;
    const CONDITION_USED_GOOD              = 4;
    const CONDITION_USED_ACCEPTABLE        = 5;
    const CONDITION_REFURBISHED            = 10;

    const CONDITION_NOTE_MODE_NOT_SET          = 0;
    const CONDITION_NOTE_MODE_NONE             = 3;
    const CONDITION_NOTE_MODE_CUSTOM_VALUE     = 1;
    const CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE = 2;

    const SHIPPING_MODE_NOT_SET            = 0;
    const SHIPPING_MODE_DISABLED           = 1;
    const SHIPPING_MODE_FREE               = 2;
    const SHIPPING_MODE_DEFAULT            = 3;
    const SHIPPING_MODE_VALUE              = 4;
    const SHIPPING_MODE_CUSTOM_ATTRIBUTE   = 5;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_General');
    }

    // ########################################

    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getListings($asObjects,$filters);
    }

    // ########################################

    public function getSkuMode()
    {
        return (int)$this->getData('sku_mode');
    }

    public function isSkuNotSetMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_NOT_SET;
    }

    public function isSkuProductIdMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_PRODUCT_ID;
    }

    public function isSkuDefaultMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_DEFAULT;
    }

    public function isSkuAttributeMode()
    {
        return $this->getSkuMode() == self::SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getSkuSource()
    {
        return array(
            'mode'      => $this->getSkuMode(),
            'attribute' => $this->getData('sku_custom_attribute')
        );
    }

    //-------------------------

    public function isGenerateSkuModeNo()
    {
        return (int)$this->getData('generate_sku_mode') == self::GENERATE_SKU_MODE_NO;
    }

    public function isGenerateSkuModeYes()
    {
        return (int)$this->getData('generate_sku_mode') == self::GENERATE_SKU_MODE_YES;
    }

    //-------------------------

    public function getGeneralIdMode()
    {
        return (int)$this->getData('general_id_mode');
    }

    public function isGeneralIdNotSetMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_NOT_SET;
    }

    public function isGeneralIdWorldwideMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_WORLDWIDE;
    }

    public function isGeneralIdGeneralIdMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_GENERAL_ID;
    }

    public function isGeneralIdSellerSkuMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_SELLER_SKU;
    }

    public function isGeneralIdIsbnMode()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_ISBN;
    }

    public function getGeneralIdSource()
    {
        return array(
            'mode'      => $this->getGeneralIdMode(),
            'attribute' => $this->getData('general_id_custom_attribute')
        );
    }

    //-------------------------

    public function getSearchByMagentoTitleMode()
    {
        return (int)$this->getData('search_by_magento_title_mode');
    }

    public function isSearchByMagentoTitleModeEnabled()
    {
        return $this->getSearchByMagentoTitleMode() == self::SEARCH_BY_MAGENTO_TITLE_MODE_YES;
    }

    //-------------------------

    public function getConditionMode()
    {
        return (int)$this->getData('condition_mode');
    }

    public function isConditionNotSetMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_NOT_SET;
    }

    public function isConditionDefaultMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_DEFAULT;
    }

    public function isConditionAttributeMode()
    {
        return $this->getConditionMode() == self::CONDITION_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getConditionSource()
    {
        return array(
            'mode'      => $this->getConditionMode(),
            'value'     => (int)$this->getData('condition_value'),
            'attribute' => $this->getData('condition_custom_attribute')
        );
    }

    public function getConditionValues()
    {
        $temp = $this->getData('cache_condition_values');

        if (!empty($temp)) {
            return $temp;
        }

        $reflectionClass = new ReflectionClass (__CLASS__);
        $tempConstants = $reflectionClass->getConstants();

        $values = array();
        foreach ($tempConstants as $key => $value) {
            $prefixKey = strtolower(substr($key,0,14));
            if (substr($prefixKey,0,10) != 'condition_' ||
                in_array($prefixKey,array('condition_mode','condition_note'))) {
                continue;
            }
            $values[] = $value;
        }

        $this->setData('cache_condition_values',$values);

        return $values;
    }

    //-------------------------

    public function getConditionNoteMode()
    {
        return (int)$this->getData('condition_note_mode');
    }

    public function isConditionNoteNotSetMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_NOT_SET;
    }

    public function isConditionNoteNoneMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_NONE;
    }

    public function isConditionNoteValueMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_CUSTOM_VALUE;
    }

    public function isConditionNoteAttributeMode()
    {
        return $this->getConditionNoteMode() == self::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getConditionNoteSource()
    {
        return array(
            'mode'      => $this->getConditionNoteMode(),
            'value'     => $this->getData('condition_note_value'),
            'attribute' => $this->getData('condition_note_custom_attribute')
        );
    }

    // ########################################

    public function getShippingStandardMode()
    {
        return (int)$this->getData('shipping_standard_mode');
    }

    public function isShippingStandardNotSetMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_NOT_SET;
    }

    public function isShippingStandardFreeMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingStandardDefaultMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingStandardValueMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingStandardCustomAttributeMode()
    {
        return $this->getShippingStandardMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingStandardModeSource()
    {
        return array(
            'mode'      => $this->getShippingStandardMode(),
            'value'     => (float)$this->getData('shipping_standard_value'),
            'attribute' => $this->getData('shipping_standard_custom_attribute')
        );
    }

    //----------------------------------------

    public function getShippingExpeditedMode()
    {
        return (int)$this->getData('shipping_expedited_mode');
    }

    public function isShippingExpeditedNotSetMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_NOT_SET;
    }

    public function isShippingExpeditedDisabledMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_DISABLED;
    }

    public function isShippingExpeditedFreeMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingExpeditedDefaultMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingExpeditedValueMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingExpeditedCustomAttributeMode()
    {
        return $this->getShippingExpeditedMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingExpeditedModeSource()
    {
        return array(
            'mode'      => $this->getShippingExpeditedMode(),
            'value'     => (float)$this->getData('shipping_expedited_value'),
            'attribute' => $this->getData('shipping_expedited_custom_attribute')
        );
    }

    //----------------------------------------

    public function getShippingOneDayMode()
    {
        return (int)$this->getData('shipping_one_day_mode');
    }

    public function isShippingOneDayNotSetMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_NOT_SET;
    }

    public function isShippingOneDayDisabledMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_DISABLED;
    }

    public function isShippingOneDayFreeMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingOneDayDefaultMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingOneDayValueMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingOneDayCustomAttributeMode()
    {
        return $this->getShippingOneDayMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingOneDayModeSource()
    {
        return array(
            'mode'      => $this->getShippingOneDayMode(),
            'value'     => (float)$this->getData('shipping_one_day_value'),
            'attribute' => $this->getData('shipping_one_day_custom_attribute')
        );
    }

    //----------------------------------------

    public function getShippingTwoDayMode()
    {
        return (int)$this->getData('shipping_two_day_mode');
    }

    public function isShippingTwoDayNotSetMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_NOT_SET;
    }

    public function isShippingTwoDayDisabledMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_DISABLED;
    }

    public function isShippingTwoDayFreeMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_FREE;
    }

    public function isShippingTwoDayDefaultMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_DEFAULT;
    }

    public function isShippingTwoDayValueMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_VALUE;
    }

    public function isShippingTwoDayCustomAttributeMode()
    {
        return $this->getShippingTwoDayMode() == self::SHIPPING_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingTwoDayModeSource()
    {
        return array(
            'mode'      => $this->getShippingTwoDayMode(),
            'value'     => (float)$this->getData('shipping_two_day_value'),
            'attribute' => $this->getData('shipping_two_day_custom_attribute')
        );
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array();
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_general');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_general');
        return parent::delete();
    }

    // ########################################
}