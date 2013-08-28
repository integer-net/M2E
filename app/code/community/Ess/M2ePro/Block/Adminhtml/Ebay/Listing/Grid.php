<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_component_grid_massaction';
    }

    protected function _prepareCollection()
    {
        // Update statistic table values
        Mage::getResourceModel('M2ePro/Listing')->updateStatisticColumns();
        Mage::getResourceModel('M2ePro/Ebay_Listing')->updateStatisticColumns();

        // Get collection of listings
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');

        // Set global filters
        //--------------------------
        $filterSellingFormatTemplate = $this->getRequest()->getParam('filter_ebay_selling_format_template');
        $filterDescriptionTemplate = $this->getRequest()->getParam('filter_ebay_description_template');
        $filterGeneralTemplate = $this->getRequest()->getParam('filter_ebay_general_template');
        $filterSynchronizationTemplate = $this->getRequest()->getParam('filter_ebay_synchronization_template');

        !is_null($filterSellingFormatTemplate) &&
            $filterSellingFormatTemplate != 0 &&
            $collection->addFieldToFilter('template_selling_format_id', (int)$filterSellingFormatTemplate);
        !is_null($filterDescriptionTemplate) &&
            $filterDescriptionTemplate != 0 &&
            $collection->addFieldToFilter('template_description_id', (int)$filterDescriptionTemplate);
        !is_null($filterGeneralTemplate) &&
            $filterGeneralTemplate != 0 &&
            $collection->addFieldToFilter('template_general_id', (int)$filterGeneralTemplate);
        !is_null($filterSynchronizationTemplate) &&
            $filterSynchronizationTemplate != 0 &&
            $collection->addFieldToFilter('template_synchronization_id', (int)$filterSynchronizationTemplate);
        //--------------------------

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
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('products_total_count', array(
            'header'    => Mage::helper('M2ePro')->__('Total Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_total_count',
            'filter_index' => 'main_table.products_total_count',
            'frame_callback' => array($this, 'callbackColumnTotalProducts')
        ));

        $this->addColumn('products_active_count', array(
            'header'    => Mage::helper('M2ePro')->__('Active Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_active_count',
            'filter_index' => 'main_table.products_active_count',
            'frame_callback' => array($this, 'callbackColumnListedProducts')
        ));

        $this->addColumn('products_inactive_count', array(
            'header'    => Mage::helper('M2ePro')->__('Inactive Items'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'products_inactive_count',
            'filter_index' => 'main_table.products_inactive_count',
            'frame_callback' => array($this, 'callbackColumnInactiveProducts')
        ));

        $this->addColumn('items_sold_count', array(
            'header'    => Mage::helper('M2ePro')->__('Sold QTY'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'items_sold_count',
            'filter_index' => 'second_table.items_sold_count',
            'frame_callback' => array($this, 'callbackColumnSoldQTY')
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

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('View Products'),
                    'url'       => array('base'=> '*/adminhtml_ebay_listing/view/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Add Products from Products List'),
                    'url'       => array('base'=> '*/adminhtml_ebay_listing/product/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Add Products from Categories'),
                    'url'       => array('base'=> '*/adminhtml_ebay_listing/categoryProduct/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Settings'),
                    'url'       => array('base'=> '*/adminhtml_ebay_listing/edit/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete Listing'),
                    'url'       => array('base'=> '*/adminhtml_'
                                            .Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing/delete'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('View Log'),
                    'url'       => array('base'=> '*/adminhtml_log/listing/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Clear Log'),
                    'url'       => array('base'=> '*/adminhtml_listing/clearLog/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Selling Format Template'),
                    'url'       => array('base'=> '*/adminhtml_listing/goToSellingFormatTemplate/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Description Template'),
                    'url'       => array('base'=> '*/adminhtml_listing/goToDescriptionTemplate/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit General Template'),
                    'url'       => array('base'=> '*/adminhtml_listing/goToGeneralTemplate/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Synchronization Template'),
                    'url'       => array('base'=> '*/adminhtml_listing/goToSynchronizationTemplate/back/'
                                            .Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                                                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                                            )).'/'),
                    'field'     => 'id'
                )
            )
        ));

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        // Set clear log action
        //--------------------------------
        $this->getMassactionBlock()->addItem('clear_logs', array(
             'label'    => Mage::helper('M2ePro')->__('Clear Log(s)'),
             'url'      => $this->getUrl('*/adminhtml_listing/clearLog',array(
                 'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                     'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
                 ))
             )),
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        // Set remove listings action
        //--------------------------------
        $this->getMassactionBlock()->addItem('delete_listings', array(
             'label'    => Mage::helper('M2ePro')->__('Delete Listing(s)'),
             'url'      => $this->getUrl('*/adminhtml_'.Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing/delete'),
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

    public function callbackColumnTotalProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnListedProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnSoldQTY($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnInactiveProducts($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else if ($value <= 0) {
            $value = '<span style="color: red;">0</span>';
        }

        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/listingGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_ebay_listing/view', array(
            'id' => $row->getId(),
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
            ))
        ));
    }

    // ####################################
}