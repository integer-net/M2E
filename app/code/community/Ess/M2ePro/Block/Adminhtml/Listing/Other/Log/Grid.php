<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('listingOtherLogGrid'.(isset($listingData['id'])?$listingData['id']:''));
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
        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/Listing_Other_Log')->getCollection();
//        $collection->getSelect()->where('`main_table`.component_mode = ? OR `main_table`.component_mode IS NULL',
//            Ess_M2ePro_Helper_Component_Amazon::NICK);
        //--------------------------------

        // Join amazon_listings_table
        //--------------------------------
        $collection->getSelect()
            ->joinLeft(array('lo' => Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable()),
                       '(`main_table`.listing_other_id = `lo`.id)',
                       array(
                           'account_id'     => 'lo.account_id',
                           'marketplace_id' => 'lo.marketplace_id'
                       )
            )
            ->joinLeft(array('ea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
                             '(`lo`.account_id = `ea`.account_id)',
                             array('account_mode' => 'ea.mode')
            );
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if (isset($listingData['id'])) {
            $collection->addFieldToFilter('`main_table`.listing_other_id', $listingData['id']);
        }
        //--------------------------------

        // we need sort by id also, because create_date may be same for some adjacents entries
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
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date',
        ));

        if (!isset($listingData['id']) && count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'right',
                'width'          => '120px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'main_table.component_mode',
                'sortable'       => false,
                'options'        => $this->getComponentModeFilterOptions()
            ));
        }

        $this->addColumn('identifier', array(
            'header' => Mage::helper('M2ePro')->__('Identifier'),
            'align'  => 'left',
            'width'  => '100px',
            'type'   => 'text',
            'index'  => 'identifier',
            'filter_index' => 'main_table.identifier',
            'frame_callback' => array($this, 'callbackColumnIdentifier')
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Product Name'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '250px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => Mage::getModel('M2ePro/Listing_Other_Log')->getActionsTitles()
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

    public function callbackColumnIdentifier($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        } else {
            if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl($value,
                                                                         $row->getData('account_mode'),
                                                                         $row->getData('marketplace_id'));

                return '<a href="' . $url . '" target="_blank">' . $value . '</a>';
            }

            if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Amazon::NICK) {

                $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($value,
                                                                           $row->getData('marketplace_id'));

                return '<a href="' . $url . '" target="_blank">' . $value . '</a>';
            }

            if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Buy::NICK) {
                $url = Mage::helper('M2ePro/Component_Buy')->getItemUrl($value);

                return '<a href="' . $url . '" target="_blank">' . $value . '</a>';
            }

            if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Play::NICK) {

                return '<p>' . $value . '</p>';
            }
        }
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else {
            $value = Mage::helper('M2ePro')->escapeHtml($value);
            if (strlen($value) > 60) {
                $value = substr($value, 0, 60) . '...';
            }

            $value = '<span>' . $value . '</span>';
        }

        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/listingOtherGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}