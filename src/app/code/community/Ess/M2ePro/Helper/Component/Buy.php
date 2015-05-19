<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Buy extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Rakuten.com (Beta)
    const NICK  = 'buy';
    const TITLE = 'Rakuten.com (Beta)';

    const DEFAULT_CURRENCY = 'USD';

    const MARKETPLACE_ID = 33;

    // ########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')->__(self::TITLE);
    }

    // ########################################

    public function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'mode');
    }

    public function isAllowed()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'allowed');
    }

    public function isActive()
    {
        return $this->isEnabled() && $this->isAllowed();
    }

    public function isObject($modelName, $value, $field = NULL)
    {
        $mode = Mage::helper('M2ePro/Component')->getComponentMode($modelName, $value, $field);
        return !is_null($mode) && $mode == self::NICK;
    }

    //-----------------------------------------

    public function getModel($modelName)
    {
        return Mage::helper('M2ePro/Component')->getComponentModel(self::NICK,$modelName);
    }

    public function getObject($modelName, $value, $field = NULL)
    {
        return Mage::helper('M2ePro/Component')->getComponentObject(self::NICK, $modelName, $value, $field);
    }

    public function getCachedObject($modelName, $value, $field = NULL, array $tags = array())
    {
        return Mage::helper('M2ePro/Component')->getCachedComponentObject(
            self::NICK, $modelName, $value, $field, $tags
        );
    }

    public function getCollection($modelName)
    {
        return $this->getModel($modelName)->getCollection();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getCachedObject('Marketplace', $this->getMarketplaceId());
    }

    public function getMarketplaceId()
    {
        return self::MARKETPLACE_ID;
    }

    public function getItemUrl($productId)
    {
        return 'http://'.$this->getMarketplace()->getUrl().'/prod/'.$productId.'.html';
    }

    // ########################################

    public function getCarrierTitle($carrierCode, $title)
    {
        $carriers = $this->getCarriers();
        $carrierCode = strtolower($carrierCode);

        foreach ($carriers as $carrier) {
            if ($carrierCode == strtolower($carrier)) {
                return $carrier;
            }
        }

        if ($title == '' || filter_var($title, FILTER_VALIDATE_URL) !== false) {
            return 'Other';
        }

        return $title;
    }

    public function getCarriers()
    {
        return array(
            'UPS',
            'FedEx',
            'USPS',
            'DHL',
            'Other',
            'UPS-MI',
            'FedEx SmartPost',
            'DHL Global Mail',
            'LTL_A. Duie Pyle',
            'LTL_ABF',
            'LTL_AIM Trans',
            'LTL_AIT',
            'LTL_CEVA Logistics',
            'LTL_Conway',
            'LTL_Ensenda',
            'LTL_Estes',
            'LTL_FedEx Freight',
            'LTL_FedEx LTL Freight East',
            'LTL_Fox Brother',
            'LTL_Home Direct',
            'LTL_Lakeville Motor',
            'LTL_Manna',
            'LTL_New England Motor Freight',
            'LTL_Old Dominion',
            'LTL_Pilot',
            'LTL_Pitt Ohio',
            'LTL_R&L Global',
            'LTL_S&J Transportation',
            'LTL_SAIA',
            'LTL_UPS Freight',
            'LTL_USF Holland',
            'LTL_USF Reddaway',
            'LTL_Vitran Express',
            'LTL_Watkins Motor Line Freight Standard',
            'LTL_Wilson Trucking',
            'LTL_Yellow Freight'
        );
    }

    // ########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    // ########################################
}