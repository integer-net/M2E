<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Grid extends Ess_M2ePro_Block_Adminhtml_Account_Grid
{
    // ####################################

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'id',
            'filter_index' => 'main_table.id'
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('eBay User ID'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'title',
            'escape'    => true,
            'filter_index' => 'main_table.title',
        ));

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $this->addColumn('feedbacks', array(
                'header'         => Mage::helper('M2ePro')->__('Feedback'),
                'align'          => 'center',
                'width'          => '120px',
                'type'           => 'text',
                'sortable'       => false,
                'filter'         => false,
                'frame_callback' => array($this, 'callbackColumnFeedbacks')
            ));
        }

        return parent::_prepareColumns();
    }

    // ####################################

    public function callbackColumnFeedbacks($value, $row, $column, $isExport)
    {
        $url = $this->getUrl('*/adminhtml_ebay_feedback/index', array('account' => $row->getId()));
        return '<a href="' . $url . '" target="_blank">'. Mage::helper('M2ePro')->__("Feedback") . '</a>';
    }

    // ####################################
}