<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_View_ExternalTransaction extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrderViewExternalTransaction');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Order */
        $this->order = Mage::helper('M2ePro')->getGlobalValue('temp_data');
    }

    protected function _prepareCollection()
    {
        $collection = $this->order->getChildObject()->getExternalTransactionsCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('transaction_id', array(
            'header' => Mage::helper('M2ePro')->__('Transaction #'),
            'align' => 'left',
            'width' => '*',
            'index' => 'transaction_id',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnTransactionId')
        ));

        $this->addColumn('fee', array(
            'header' => Mage::helper('M2ePro')->__('Fee'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'fee',
            'type' => 'number',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnFee')
        ));

        $this->addColumn('sum', array(
            'header' => Mage::helper('M2ePro')->__('Amount'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'sum',
            'type' => 'number',
            'sortable' => false,
            'frame_callback' => array($this, 'callbackColumnAmount')
        ));

        $this->addColumn('transaction_date', array(
            'header'   => Mage::helper('M2ePro')->__('Date'),
            'align'    => 'left',
            'width'    => '150px',
            'index'    => 'transaction_date',
            'type'     => 'datetime',
            'format'   => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'sortable' => false
        ));

        return parent::_prepareColumns();
    }

    public function callbackColumnTransactionId($value, $row, $column, $isExport)
    {
        if (strtolower($this->order->getData('payment_method')) != 'paypal') {
            return $value;
        }

        $url = $this->getUrl('*/*/goToPaypal', array('transaction_id' => $value));

        return '<a href="'.$url.'" target="_blank">'.$value.'</a>';
    }

    public function callbackColumnFee($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice($this->order->getData('currency'), $value);
    }

    public function callbackColumnAmount($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice($this->order->getData('currency'), $value);
    }

    public function getRowUrl($row)
    {
        return '';
    }
}