<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Template_General_Grid extends Ess_M2ePro_Block_Adminhtml_Component_Grid
{
    private $attributeSets = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateGeneralGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------

        $this->attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                                    ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                                    ->load()->toOptionHash();
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection of general templates
        $collection = Mage::getModel('M2ePro/Template_General')->getCollection();
        $collection->addFieldToFilter('component_mode', array('neq' => Ess_M2ePro_Helper_Component_Amazon::NICK));
        $collection->addFieldToFilter('component_mode', array('neq' => Ess_M2ePro_Helper_Component_Buy::NICK));
        $collection->addFieldToFilter('component_mode', array('neq' => Ess_M2ePro_Helper_Component_Play::NICK));

//        exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

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
            'header'    => Mage::helper('M2ePro')->__('Title'),
            'align'     => 'left',
            //'width'     => '200px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('attribute_sets', array(
            'header' => Mage::helper('M2ePro')->__('Attribute Sets'),
            'align'  => 'left',
            'width'  => '200px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnAttributeSets')
        ));

        $accountFilter = $this->getFilterOptionsByModel(
            'Account', 'id', 'title', array('component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK)
        );

        $this->addColumn('account_id', array(
            'header'         => Mage::helper('M2ePro')->__('Account'),
            'align'          => 'left',
            'width'          => '130px',
            'type'           => 'options',
            'index'          => 'account_id',
            'options'        => $accountFilter['options'],
            'frame_callback' => array($this, 'callbackColumnAccount')
        ));

        $marketplaceFilter = $this->getFilterOptionsByModel(
            'Marketplace', 'id', 'title', array('component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK)
        );

        $this->addColumn('marketplace_id', array(
            'header'         => Mage::helper('M2ePro')->__('Marketplace'),
            'align'          => 'left',
            'width'          => '130px',
            'type'           => 'options',
            'index'          => 'marketplace_id',
            'options'        => $marketplaceFilter['options'],
            'frame_callback' => array($this, 'callbackColumnMarketplace')
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '75px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array('base'=> '*/*/edit'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array('base'=> '*/*/delete'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set delete action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => $this->getUrl('*/*/delete'),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnAccount($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    public function callbackColumnAttributeSets($value, $row, $column, $isExport)
    {
        $attributeSets = Mage::getModel('M2ePro/AttributeSet')->getCollection()
            ->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL)
            ->addFieldToFilter('object_id',(int)$row->getId());

        $value = '';
        foreach ($attributeSets as $attributeSet) {
            if (strlen($value) > 100) {
                $value .= ', <strong>...</strong>';
                break;
            }
            if (isset($this->attributeSets[$attributeSet->getData('attribute_set_id')])) {
                $value != '' && $value .= ', ';
                $value .= $this->attributeSets[$attributeSet['attribute_set_id']];
            }
        }

        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getComponentRowUrl($row, 'template_general', 'edit', array('id' => $row->getData('id')));
    }

    // ####################################
}