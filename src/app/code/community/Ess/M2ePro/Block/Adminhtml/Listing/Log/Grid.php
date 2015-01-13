<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Listing_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    protected $viewComponentHelper = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialize view
        //------------------------------
        $view = Mage::helper('M2ePro/View')->getCurrentView();
        $this->viewComponentHelper = Mage::helper('M2ePro/View')->getComponentHelper($view);
        //------------------------------

        // Initialization block
        //------------------------------
        $this->setId($view . 'ListingLogGrid' . $this->getEntityId());
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/Listing_Log')->getCollection();
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if ($this->getEntityId()) {
            $collection->addFieldToFilter('listing_id', $this->getEntityId());
        }
        //--------------------------------

        // prepare components
        //--------------------------------
        $components = $this->viewComponentHelper->getActiveComponents();
        $collection->getSelect()
            ->where('component_mode IN(\''.implode('\',\'',$components).'\') OR component_mode IS NULL');
        //--------------------------------

        // we need sort by id also, because create_date may be same for some adjustment entries
        //--------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        //--------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'width'     => '150px',
            'index'     => 'create_date'
        ));

        if (!$this->getEntityId() && !$this->viewComponentHelper->isSingleActiveComponent()) {
            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'right',
                'width'          => '120px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'main_table.component_mode',
                'sortable'       => false,
                'options'        => $this->viewComponentHelper->getActiveComponentsTitles()
            ));
        }

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => $this->getActionTitles()
        ));

        if (!$this->getEntityId()) {

            $this->addColumn('listing_id', array(
                'header'    => Mage::helper('M2ePro')->__('Listing ID'),
                'align'     => 'right',
                'width'     => '100px',
                'type'      => 'number',
                'index'     => 'listing_id',
                'filter_index' => 'main_table.listing_id'
            ));

            $this->addColumn('listing_title', array(
                'header'    => Mage::helper('M2ePro')->__('Listing Title'),
                'align'     => 'left',
                'width'     => '150px',
                'type'      => 'text',
                'index'     => 'listing_title',
                'filter_index' => 'main_table.listing_title',
                'frame_callback' => array($this, 'callbackColumnListingTitle')
            ));
        }

        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'product_id',
            'filter_index' => 'main_table.product_id'
        ));

        $this->addColumn('product_title', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'product_title',
            'filter_index' => 'main_table.product_title',
            'frame_callback' => array($this, 'callbackColumnProductTitle')
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackDescription')
        ));

        $this->addColumn('initiator', array(
            'header'=> Mage::helper('M2ePro')->__('Run Mode'),
            'width' => '80px',
            'index' => 'initiator',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogInitiatorList(),
            'frame_callback' => array($this, 'callbackColumnInitiator')
        ));

        $this->addColumn('type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogTypeList(),
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('priority', array(
            'header'=> Mage::helper('M2ePro')->__('Priority'),
            'width' => '80px',
            'index' => 'priority',
            'align'     => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogPriorityList(),
            'frame_callback' => array($this, 'callbackColumnPriority')
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
    }

    // ####################################

    public function callbackColumnListingTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50) . '...';
        }

        if ($row->getData('listing_id')) {
            $url = $this->getUrl(
                '*/adminhtml_'.$row->getData('component_mode').'_listing/view',
                array('id' => $row->getData('listing_id'))
            );
            $value = '<a target="_blank" href="'.$url.'">'.
                     Mage::helper('M2ePro')->escapeHtml($value).
                     '</a>';
        }

        return $value;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        if (strlen($value) > 50) {
            $value = substr($value, 0, 50) . '...';
        }

        if ($row->getData('product_id')) {
            $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $row->getData('product_id')));
            $value = '<a target="_blank" href="'.$url.'" target="_blank">'.
                        Mage::helper('M2ePro')->escapeHtml($value).
                     '</a>';
        }

        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/listingGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    abstract protected function getActionTitles();

    // ####################################
}