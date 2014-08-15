<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Play extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Play.com (Beta)
    const NICK  = 'play';
    const TITLE = 'Play.com (Beta)';

    const CURRENCY_GBP = 'GBP';
    const CURRENCY_EUR = 'EUR';

    const MARKETPLACE_ID = 34;

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
        return $this->getCachedObject('Marketplace',$this->getMarketplaceId());
    }

    public function getMarketplaceId()
    {
        return self::MARKETPLACE_ID;
    }

    public function getItemUrl($playId, $categoryCode)
    {
        return 'http://'.$this->getMarketplace()->getUrl().'/Product.aspx?title='.$playId.'&r='.$categoryCode;
    }

    // ########################################

    public function getCarrierTitle($carrierCode, $title)
    {
        $carriers = $this->getCarriers();

        foreach ($carriers as $carrier) {
            if (strtolower($carrierCode) == strtolower($carrier)) {
                return $carrier;
            }

            if (strtolower($title) == strtolower($carrier)) {
                return $carrier;
            }
        }

        if ($title == '' || filter_var($title, FILTER_VALIDATE_URL) !== false) {
            return '';
        }

        return $title;
    }

    public function getCarriers()
    {
        return array(
            'Royal Mail',
            'Parcelforce',
            'TNT',
            'DHL',
            'FedEx'
        );
    }

    // ########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues(self::NICK);
    }

    // ########################################
}