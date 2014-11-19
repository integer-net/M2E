<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility extends Mage_Core_Helper_Abstract
{
    // ##########################################################

    const TYPE_SPECIFIC   = 'specific';
    const TYPE_KTYPE      = 'ktype';

    const PRODUCT_TYPE_VEHICLE    = 0;
    const PRODUCT_TYPE_MOTORCYCLE = 1;

    // ##########################################################

    public function getSpecificSupportedMarketplaces()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS,
        );
    }

    public function isMarketplaceSupportsSpecific($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getSpecificSupportedMarketplaces());
    }

    // ----------------------------------------------------------

    public function getKtypeSupportedMarketplaces()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_AU,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_UK,
            Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_DE,
        );
    }

    public function isMarketplaceSupportsKtype($marketplaceId)
    {
        return in_array((int)$marketplaceId, $this->getKtypeSupportedMarketplaces());
    }

    // ##########################################################

    public function getAttribute($type)
    {
        switch ($type) {
            case self::TYPE_SPECIFIC:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motor/','motors_specifics_attribute'
                );

            case self::TYPE_KTYPE:
                return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/ebay/motor/','motors_ktypes_attribute'
                );
        }

        return '';
    }

    public function parseAttributeValue($value)
    {
        if (empty($value)) {
            return array();
        }

        $value = (array)explode(',', $value);
        if (empty($value)) {
            return array();
        }

        $parsedData = array();
        foreach ($value as $identifierData) {
            $identifier = $identifierData;
            $note = '';

            if (strpos($identifierData, '|') !== false) {
                $identifierData = explode('|', $identifierData);

                $identifier = $identifierData[0];
                $note = trim($identifierData[1], '"');
            }

            $parsedData[$identifier] = array(
                'id'   => $identifier,
                'note' => $note,
            );
        }

        return $parsedData;
    }

    // ##########################################################

    public function getDictionaryTable($type)
    {
        switch ($type) {
            case self::TYPE_SPECIFIC:
                return Mage::getSingleton('core/resource')->getTableName(
                    'm2epro_ebay_dictionary_motor_specific'
                );

            case self::TYPE_KTYPE:
                return Mage::getSingleton('core/resource')->getTableName(
                    'm2epro_ebay_dictionary_motor_ktype'
                );
        }

        return '';
    }

    public function getIdentifierKey($type)
    {
        switch ($type) {
            case self::TYPE_SPECIFIC:
                return 'epid';

            case self::TYPE_KTYPE:
                return 'ktype';
        }

        return '';
    }

    // ##########################################################
}