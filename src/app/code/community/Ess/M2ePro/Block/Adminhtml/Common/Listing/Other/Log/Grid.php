<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Other_Log_Grid
{
    // ########################################

    protected function getColumnTitles()
    {
        return array(
            'create_date' => Mage::helper('M2ePro')->__('Creation Date'),
            'identifier' => Mage::helper('M2ePro')->__('Identifier'),
            'title' => Mage::helper('M2ePro')->__('Product Name'),
            'action' => Mage::helper('M2ePro')->__('Action'),
            'description' => Mage::helper('M2ePro')->__('Description'),
            'initiator' => Mage::helper('M2ePro')->__('Run Mode'),
            'type' => Mage::helper('M2ePro')->__('Type'),
            'priority' => Mage::helper('M2ePro')->__('Priority'),
        );
    }

    // ########################################

    protected function getActionTitles()
    {
        $allActions = Mage::getModel('M2ePro/Listing_Other_Log')->getActionsTitles();
        $excludeActions = array(
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_REVISE_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_RELIST_PRODUCT => '',
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_STOP_PRODUCT => '',
        );

        return array_diff_key($allActions, $excludeActions);
    }

    // ########################################
}