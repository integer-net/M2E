<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    // ########################################

    protected function getActionTitles()
    {
        $allActions = Mage::getModel('M2ePro/Listing_Log')->getActionsTitles();

        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT => ''
        );

        if ($this->getChannel() != Ess_M2ePro_Helper_Component_Buy::NICK) {
            $excludeActions[Ess_M2ePro_Model_Listing_Log::ACTION_NEW_SKU_PRODUCT_ON_COMPONENT] = '';
        }

        return array_diff_key($allActions, $excludeActions);
    }

    // ########################################

    public function callbackColumnListingTitleID($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50) . '...';
        }

        $value = Mage::helper('M2ePro')->escapeHtml($value);

        if ($row->getData('listing_id')) {
            $url = $this->getUrl(
                '*/adminhtml_common_'.$row->getData('component_mode').'_listing/view',
                array('id' => $row->getData('listing_id'))
            );

            $value = '<a target="_blank" href="'.$url.'">' .
                Mage::helper('M2ePro')->escapeHtml($value) .
                '</a><br/>ID: '.$row->getData('listing_id');
        }

        return $value;
    }

    // ########################################
}