<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Specific_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorSpecificGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('make');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Ebay_Motor_Specific')->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('epid', array(
            'header' => Mage::helper('M2ePro')->__('ePID'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'epid',
            'width'  => '100px'
        ));

        $this->addColumn('product_type', array(
            'header' => Mage::helper('M2ePro')->__('Type'),
            'align'  => 'left',
            'type'   => 'options',
            'index'  => 'product_type',
            'options'  => array(
                Ess_M2ePro_Model_Ebay_Motor_Specific::TYPE_VEHICLE => Mage::helper('M2ePro')->__('Car / Truck'),
                Ess_M2ePro_Model_Ebay_Motor_Specific::TYPE_MOTORCYCLE => Mage::helper('M2ePro')->__('Motorcycle'),
            )
        ));

        $this->addColumn('make', array(
            'header' => Mage::helper('M2ePro')->__('Make'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'make',
            'width'  => '150px'
        ));

        $this->addColumn('model', array(
            'header' => Mage::helper('M2ePro')->__('Model'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'model',
            'width'  => '150px'
        ));

        $this->addColumn('submodel', array(
            'header' => Mage::helper('M2ePro')->__('Submodel'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'submodel',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnSubmodel')
        ));

        $this->addColumn('year', array(
            'header' => Mage::helper('M2ePro')->__('Year'),
            'align'  => 'left',
            'type'   => 'number',
            'index'  => 'year',
            'width'  => '100px'
        ));

        $this->addColumn('trim', array(
            'header' => Mage::helper('M2ePro')->__('Trim'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'trim',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnTrim')
        ));

        $this->addColumn('engine', array(
            'header' => Mage::helper('M2ePro')->__('Engine'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'engine',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnEngine')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('main_table.epid');
        $this->getMassactionBlock()->setFormFieldName('epid');
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('overwrite_attribute', array(
            'label'   => Mage::helper('M2ePro')->__('Overwrite ePIDs in Compatibility Attribute'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('add_to_attribute', array(
            'label'   => Mage::helper('M2ePro')->__('Add ePIDs to Compatibility Attribute'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        // this is required for correct work of massaction js
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //##############################################################

    public function callbackColumnSubmodel($value, $row, $column, $isExport)
    {
        return $value ? $value : '--';
    }

    public function callbackColumnTrim($value, $row, $column, $isExport)
    {
        return $value ? $value : '--';
    }

    public function callbackColumnEngine($value, $row, $column, $isExport)
    {
        return $value ? $value : '--';
    }

    //##############################################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/motorSpecificGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //##############################################################
}