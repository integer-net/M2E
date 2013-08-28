<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $synchTask = $this->getRequest()->getParam('synch_task');
        $component = $this->getRequest()->getParam('component');

        // Initialization block
        //------------------------------
        $this->setId(
            'synchronizationLogGrid'.(!is_null($synchTask) ? $synchTask : '').(!is_null($component) ? $component : '')
        );
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $filters = array();
        !is_null($synchTask) && $filters['synch_task'] = $synchTask;
        !is_null($synchTask) && $filters['component_mode'] = $component;
        $this->setDefaultFilter($filters);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/Synchronization_Log')->getCollection();
        //--------------------------------

        $components = Mage::helper('M2ePro/Component')->getActiveComponents();
        $collection->getSelect()
            ->where('component_mode IN(\''.implode('\',\'',$components).'\') OR component_mode IS NULL');

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
            'index'     => 'create_date'
        ));

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'left',
                'width'          => '120px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'main_table.component_mode',
                'sortable'       => false,
                'options'        => $this->getComponentModeFilterOptions()
            ));
        }

        $this->addColumn('synch_task', array(
            'header'    => Mage::helper('M2ePro')->__('Synchronization'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'options',
            'index'     => 'synch_task',
            'sortable'  => false,
            'filter_index' => 'main_table.synch_task',
            'options' => Mage::getModel('M2ePro/Synchronization_Log')->getActionsTitles()
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

        $this->addColumn('type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align'     => 'right',
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

    public function callbackDescription($value, $row, $column, $isExport)
    {
        $value = Mage::getModel('M2ePro/Log_Abstract')->decodeDescription($value);

        preg_match_all('/href="([^"]*)"/', $value, $matches);

        if (!count($matches[0])) {
            return $value;
        }

        foreach ($matches[1] as $key => $href) {

            preg_match_all('/route:([^;]*)/', $href, $routeMatch);
            preg_match_all('/back:([^;]*)/', $href, $backMatch);

            if (count($routeMatch[1]) == 0) {
                $value = str_replace($matches[0][$key], '', $value);
                continue;
            }

            $params = array();
            if (count($backMatch[1]) > 0) {
                $params = array('back' => Mage::helper('M2ePro')->makeBackUrlParam($backMatch[1][$key]));
            }

            $url = $routeMatch[1][$key];
            $value = str_replace($href, $this->getUrl($url, $params), $value);
        }

        return $value;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/synchronizationGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}