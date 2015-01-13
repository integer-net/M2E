<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Template_SellingFormat_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $attributeSets = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateSellingFormatGrid');
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
        // Get collection of prices templates
        $collection = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();

        $components = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();
        $collection->addFieldToFilter('main_table.component_mode', array('in'=>$components));

        //exit($collection->getSelect()->__toString());

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
            'escape'    => true,
            'filter_index' => 'main_table.title'
        ));

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'left',
                'width'          => '120px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'main_table.component_mode',
                'sortable'       => false,
                'options'        => Mage::helper('M2ePro/View_Common_Component')->getActiveComponentsTitles()
            ));
        }

        $this->addColumn('attribute_sets', array(
            'header' => Mage::helper('M2ePro')->__('Attribute Sets'),
            'align'  => 'left',
            'width'  => '250px',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnAttributeSets')
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
            'width'     => '100px',
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
        $this->setMassactionIdField('main_table.id');
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

    public function callbackColumnAttributeSets($value, $row, $column, $isExport)
    {
        $attributeSets = Mage::getModel('M2ePro/AttributeSet')->getCollection()
            ->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT)
            ->addFieldToFilter('object_id',(int)$row->getId())
            ->toArray();

        $value = '';
        foreach ($attributeSets['items'] as $attributeSet) {
            if (strlen($value) > 100) {
                $value .= ', <strong>...</strong>';
                break;
            }
            if (isset($this->attributeSets[$attributeSet['attribute_set_id']])) {
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
        return Mage::helper('M2ePro/View')
            ->getUrl($row, 'template_sellingFormat', 'edit', array('id' => $row->getData('id')));
    }

    // ####################################
}