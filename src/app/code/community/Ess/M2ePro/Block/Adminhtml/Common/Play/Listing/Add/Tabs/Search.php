<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_Add_Tabs_Search
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_Search
{
    // #############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->sessionKey = 'play_listing_create';
        $this->setId('playListingAddTabsGeneral');
        $this->setTemplate('M2ePro/common/play/listing/add/tabs/search.phtml');
        //------------------------------
    }

    // #############################################

    protected function getDefaults()
    {
        return self::getDefaultFieldsValues();
    }

    static function getDefaultFieldsValues()
    {
        return array(
            'general_id_mode' => Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_NOT_SET,
            'general_id_custom_attribute' => '',

            'search_by_magento_title_mode' => Ess_M2ePro_Model_Play_Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE
        );
    }

    // #############################################
}