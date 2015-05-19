<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Magento_Product_Rule_Condition_Product
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Product
{
    protected function getCustomFilters()
    {
        $playFilters = array(
            'play_general_id'        => 'PlayGeneralId',
            'play_sku'               => 'PlaySku',
            'play_online_qty'        => 'PlayOnlineQty',
            'play_online_price_gbr'  => 'PlayOnlinePriceGbr',
            'play_online_price_euro' => 'PlayOnlinePriceEuro',
            'play_status'            => 'PlayStatus'
        );

        return array_merge_recursive(
            parent::getCustomFilters(),
            $playFilters
        );
    }

    /**
     * @param $filterId
     * @return Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
     */
    protected function getCustomFilterInstance($filterId)
    {
        $parentFilters = parent::getCustomFilters();
        if (isset($parentFilters[$filterId])) {
            return parent::getCustomFilterInstance($filterId);
        }

        $customFilters = $this->getCustomFilters();
        $this->_customFiltersCache[$filterId] = Mage::getModel(
            'M2ePro/Play_Magento_Product_Rule_Custom_'.$customFilters[$filterId]
        );

        return $this->_customFiltersCache[$filterId];
    }
}