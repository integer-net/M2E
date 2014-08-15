<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // eBay
    const NICK  = 'ebay';
    const TITLE = 'eBay';

    const MARKETPLACE_US     = 1;
    const MARKETPLACE_MOTORS = 9;
    const MARKETPLACE_AU = 4;
    const MARKETPLACE_UK = 3;
    const MARKETPLACE_DE = 8;

    const LISTING_DURATION_GTC = 100;

    const MAX_LENGTH_FOR_OPTION_VALUE = 50;

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

    public function getListingProductByEbayItem($ebayItem, $accountId)
    {
        // Get listing product
        //-----------------------------
        $readConnection = Mage::getResourceModel('core/config')->getReadConnection();

        $ebayItem  = $readConnection->quoteInto('?', $ebayItem);
        $accountId = $readConnection->quoteInto('?', $accountId);

        /** @var $collection Ess_M2ePro_Model_Mysql4_Listing_Product_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('mei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
            "(second_table.ebay_item_id = mei.id AND mei.item_id = {$ebayItem}
                                                 AND mei.account_id = {$accountId})",
            array()
        );
        //-----------------------------

        if ($collection->getSize() == 0) {
            return NULL;
        }

        return $collection->getFirstItem();
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

    // ########################################

    public function isShowTaxCategory()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/template/selling_format/', 'show_tax_category'
        );
    }

    public function getAvailableDurations()
    {
        $helper = Mage::helper('M2ePro');

        return array(
            '1' => $helper->__('1 day'),
            '3' => $helper->__('3 days'),
            '5' => $helper->__('5 days'),
            '7' => $helper->__('7 days'),
            '10' => $helper->__('10 days'),
            '30' => $helper->__('30 days'),
            self::LISTING_DURATION_GTC => $helper->__('Good Till Cancelled'),
        );
    }

    // ########################################

    public function reduceOptionsForVariations(array $options)
    {
        foreach ($options['set'] as &$optionsSet) {
            foreach ($optionsSet as &$singleOption) {
                $singleOption = Mage::helper('M2ePro')->reduceWordsInString(
                    $singleOption, self::MAX_LENGTH_FOR_OPTION_VALUE
                );
            }
        }

        foreach ($options['variations'] as &$variation) {
            foreach ($variation as &$singleOption) {
                $singleOption['option'] = Mage::helper('M2ePro')->reduceWordsInString(
                    $singleOption['option'], self::MAX_LENGTH_FOR_OPTION_VALUE
                );
            }
        }

        return $options;
    }

    public function reduceOptionsForOrders(array $options)
    {
        foreach ($options as &$singleOption) {
            foreach ($singleOption['values'] as &$singleOptionValue) {
                foreach ($singleOptionValue['labels'] as &$singleOptionLabel) {
                    $singleOptionLabel = Mage::helper('M2ePro')->reduceWordsInString(
                        $singleOptionLabel, self::MAX_LENGTH_FOR_OPTION_VALUE
                    );
                }
            }
        }

        return $options;
    }

    // ########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues(self::NICK);
    }

    // ########################################
}