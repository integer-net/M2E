<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Add_Tabs_Selling
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Selling
{
    // #############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->component = Ess_M2ePro_Helper_Component_Play::NICK;
        $this->sessionKey = 'play_listing_create';
        $this->setId('playListingAddTabsSelling');
        $this->setTemplate('M2ePro/common/play/listing/add/tabs/selling.phtml');
        //------------------------------
    }

    // #############################################

    protected function _beforeToHtml()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                    'label' => Mage::helper('M2ePro')->__('Insert'),
                    'onclick' => "PlayListingChannelSettingsHandlerObj.appendToText"
                        ."('condition_note_custom_attribute', 'condition_note_value');",
                    'class' => 'condition_note_value_insert_button'
                ) );
        $this->setChild('condition_note_value_insert_button',$buttonBlock);

        return parent::_beforeToHtml();
    }

    // #############################################

    protected function getDefaults()
    {
        return self::getDefaultFieldsValues();
    }

    static function getDefaultFieldsValues()
    {
        return array(
            'sku_mode' => Ess_M2ePro_Model_Play_Listing::SKU_MODE_DEFAULT,
            'sku_custom_attribute' => '',
            'generate_sku_mode' => Ess_M2ePro_Model_Play_Listing::GENERATE_SKU_MODE_NO,

            'template_selling_format_id' => '',
            'template_synchronization_id' => '',

            'dispatch_to_mode' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_NOT_SET,
            'dispatch_to_value' => '',
            'dispatch_to_custom_attribute' => '',

            'dispatch_from_mode' => Ess_M2ePro_Model_Play_Listing::DISPATCH_FROM_MODE_NOT_SET,
            'dispatch_from_value' => '',

            'shipping_price_gbr_mode' => Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_NONE,
            'shipping_price_gbr_value' => '',
            'shipping_price_gbr_custom_attribute' => '',

            'shipping_price_euro_mode' => Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_NONE,
            'shipping_price_euro_value' => '',
            'shipping_price_euro_custom_attribute' => '',

            'condition_mode' => Ess_M2ePro_Model_Play_Listing::CONDITION_MODE_DEFAULT,
            'condition_value' => Ess_M2ePro_Model_Play_Listing::CONDITION_NEW,
            'condition_custom_attribute' => '',
            'condition_note_mode' => Ess_M2ePro_Model_Play_Listing::CONDITION_NOTE_MODE_NONE,
            'condition_note_value' => ''
        );
    }

    // ####################################
}