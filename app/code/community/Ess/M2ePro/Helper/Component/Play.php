<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Play extends Mage_Core_Helper_Abstract
{
    // Parser hack -> Mage::helper('M2ePro')->__('Play.com (Beta)');

    const NICK  = 'play';
    const TITLE = 'Play.com (Beta)';

    const CURRENCY_GBP = 'GBP';
    const CURRENCY_EUR = 'EUR';

    const MARKETPLACE_VIRTUAL_ID = 34;

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

    public function isDefault()
    {
        return Mage::helper('M2ePro/Component')->getDefaultComponent() == self::NICK;
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

    public function getCollection($modelName)
    {
        return $this->getModel($modelName)->getCollection();
    }

    // ########################################

    public function getVirtualMarketplaceId()
    {
        return self::MARKETPLACE_VIRTUAL_ID;
    }

    // ########################################

    public function getItemUrl($playId, $categoryCode, $marketplaceId = NULL)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_VIRTUAL_ID;

        $domain = $this->getCachedObject('Marketplace',$marketplaceId)->getUrl();

        return 'http://'.$domain.'/Product.aspx?title='.$playId.'&r='.$categoryCode;
    }

    // ########################################

    public function clearAllCache()
    {
        Mage::helper('M2ePro')->removeTagCacheValues(self::NICK);
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

    public function getCachedObject($modelName, $value, $field = NULL, array $tags = array())
    {
        return Mage::helper('M2ePro/Component')->getCachedComponentObject(
            self::NICK, $modelName, $value, $field, $tags
        );
    }

    // ########################################
}