<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Amazon extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Amazon (Beta)
    const NICK  = 'amazon';
    const TITLE = 'Amazon (Beta)';

    const MARKETPLACE_CA = 24;
    const MARKETPLACE_DE = 25;
    const MARKETPLACE_US = 29;
    const MARKETPLACE_JP = 27;
    const MARKETPLACE_CN = 32;

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

    public function getRegisterUrl($marketplaceId = NULL)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace',$marketplaceId)->getUrl();
        $applicationName = Mage::helper('M2ePro/Component_Amazon')->getApplicationName();

        return 'https://sellercentral.'.
                $domain.
                '/gp/mws/registration/register.html?ie=UTF8&*Version*=1&*entries*=0&applicationName='.
                rawurlencode($applicationName).'&appDevMWSAccountId='.
                $this->getCachedObject('Marketplace',$marketplaceId)->getDeveloperKey();
    }

    public function getItemUrl($productId, $marketplaceId = NULL)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace',$marketplaceId)->getUrl();

        return 'http://'.$domain.'/gp/product/'.$productId;
    }

    public function getOrderUrl($orderId, $marketplaceId = NULL)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getCachedObject('Marketplace',$marketplaceId)->getUrl();

        return 'https://sellercentral.'.$domain.'/gp/orders-v2/details/?orderID='.$orderId;
    }

    // ########################################

    public function getApplicationName()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/amazon/', 'application_name');
    }

    // ########################################

    public function getCurrencies()
    {
        return array (
            'GBP' => 'British Pound',
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
        );
    }

    public function getCarriers()
    {
        return array(
            'usps'  => 'USPS',
            'ups'   => 'UPS',
            'fedex' => 'FedEx',
            'dhl'   => 'DHL',
            'Fastway',
            'GLS',
            'GO!',
            'Hermes Logistik Gruppe',
            'Royal Mail',
            'Parcelforce',
            'City Link',
            'TNT',
            'Target',
            'SagawaExpress',
            'NipponExpress',
            'YamatoTransport'
        );
    }

    // ########################################

    public function getMarketplacesAvailableForApiCreation()
    {
        return $this->getCollection('Marketplace')
                    ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                    ->addFieldToFilter('developer_key', array('notnull' => true))
                    ->setOrder('sorder', 'ASC');
    }

    public function getMarketplacesAvailableForAsinCreation()
    {
        $collection = $this->getMarketplacesAvailableForApiCreation();
        return $collection->addFieldToFilter('is_asin_available', 1);
    }

    // ########################################

    public function isASIN($string)
    {
        return !empty($string) &&
               $string{0} == 'B' &&
               strlen($string) == 10;
    }

    // ########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues(self::NICK);
    }

    // ########################################
}