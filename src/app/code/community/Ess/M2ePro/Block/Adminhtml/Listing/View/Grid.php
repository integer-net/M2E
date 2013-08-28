<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _prepareColumns()
    {
        if (Mage::helper('M2ePro/Server')->isDeveloper()) {
            $this->addColumn('developer_action', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'text',
                'index'     => 'value',
                'filter'    => false,
                'sortable'  => false,
                'filter_index' => 'cpev.value',
                'frame_callback' => array($this, 'callbackColumnDeveloperAction')
            ));
        }

        return parent::_prepareColumns();
    }

    public function callbackColumnDeveloperAction($value, $row, $column, $isExport)
    {
        $actions = array();

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
            $row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('List'),
                'handler' => 'ListingActionHandlerObj.runListProducts();'
            );
        }

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Revise'),
                'handler' => 'ListingActionHandlerObj.runReviseProducts();'
            );
        }

        if ($row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            $row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Relist'),
                'handler' => 'ListingActionHandlerObj.runRelistProducts();'
            );
        }

        if ($row->getData('status') == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Stop'),
                'handler' => 'ListingActionHandlerObj.runStopProducts();'
            );
        }

        $actions[] = array(
            'title' => Mage::helper('M2ePro')->__('Stop And Remove'),
            'handler' => 'ListingActionHandlerObj.runStopAndRemoveProducts();'
        );

        if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Delete And Remove'),
                'handler' => 'ListingActionHandlerObj.runDeleteAndRemoveProducts();'
            );
        }

        $id = (int)$row->getData('id');
        $html = '';

        foreach ($actions as $action) {
            if ($html != '') {
                $html .= '<br />';
            }

            $onclick = 'ListingItemGridHandlerObj.selectByRowId(\''.$id.'\'); ' . $action['handler'];
            $html .= '<a href="javascript: void(0);" onclick="'.$onclick.'">'.$action['title'].'</a>';
        }

        $html .= '<br>' . $row->getId();

        return $html;
    }
}