<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid
{
    // ########################################

    protected function getActionTitles()
    {
        return Mage::getModel('M2ePro/Listing_Log')->getActionsTitles();
    }

    // ########################################

    public function callbackColumnListingTitle($value, $row, $column, $isExport)
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
            $value = '<a target="_blank" href="'.$url.'">' . $value . '</a>';
        }

        return $value;
    }

    // ########################################
}