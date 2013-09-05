<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay extends Mage_Core_Helper_Abstract
{
    // Parser hack -> Mage::helper('M2ePro')->__('eBay');

    const NICK  = 'ebay';
    const TITLE = 'eBay';

    const MARKETPLACE_US     = 1;
    const MARKETPLACE_UK     = 3;
    const MARKETPLACE_DE     = 8;
    const MARKETPLACE_MOTORS = 9;

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

    public function getItemUrl($ebayItemId,
                               $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION,
                               $marketplaceId = NULL)
    {
        $marketplaceId = (int)$marketplaceId;
        if ($marketplaceId <= 0 || $marketplaceId == self::MARKETPLACE_MOTORS) {
            $marketplaceId = self::MARKETPLACE_US;
        }

        $domain = $this->getCachedObject('Marketplace',$marketplaceId)->getUrl();
        if ($accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX) {
            $domain = 'sandbox.'.$domain;
        }

        return 'http://cgi.'.$domain.'/ws/eBayISAPI.dll?ViewItem&item='.(double)$ebayItemId;
    }

    public function getMemberUrl($ebayMemberId, $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION)
    {
        $domain = 'ebay.com';
        if ($accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX) {
            $domain = 'sandbox.'.$domain;
        }
        return 'http://myworld.'.$domain.'/'.(string)$ebayMemberId;
    }

    // ########################################

    public function getCurrencies()
    {
        return array(
            'AUD' => 'Australian Dollar',
            'GBP' => 'British Pound',
            'CAD' => 'Canadian Dollar',
            'CNY' => 'Chinese Renminbi',
            'EUR' => 'Euro',
            'HKD' => 'Hong Kong Dollar',
            'INR' => 'Indian Rupees',
            'MYR' => 'Malaysian Ringgit',
            'PHP' => 'Philippines Peso',
            'PLN' => 'Polish Zloty',
            'SGD' => 'Singapore Dollar',
            'SEK' => 'Sweden Krona',
            'CHF' => 'Swiss Franc',
            'TWD' => 'Taiwanese Dollar',
            'USD' => 'US Dollar',
        );
    }

    public function getCarrierTitle($carrierCode, $title = null)
    {
        $carriers = $this->getCarriers();
        $carrierCode = strtolower($carrierCode);

        if (isset($carriers[$carrierCode])) {
            return $carriers[$carrierCode];
        }

        if ($title == '' || filter_var($title, FILTER_VALIDATE_URL) !== false) {
            return 'Other';
        }

        return $title;
    }

    public function getCarriers()
    {
        return array(
            'dhl'   => 'DHL',
            'fedex' => 'FedEx',
            'ups'   => 'UPS',
            'usps'  => 'USPS'
        );
    }

    public function isCharityMarketplace($marketplaceId)
    {
        return in_array($marketplaceId, array(
            self::MARKETPLACE_US,
            self::MARKETPLACE_UK,
            self::MARKETPLACE_MOTORS
        ));
    }

    // ########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues(self::NICK);
    }

    // ########################################
}