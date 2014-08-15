<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Synchronization_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    protected $viewComponentHelper = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->viewComponentHelper = Mage::helper('M2ePro/View')->getComponentHelper();

        $task = $this->getRequest()->getParam('task');
        $component = $this->getRequest()->getParam('component');

        // Initialization block
        //------------------------------
        $this->setId(
            'synchronizationLogGrid'.(!is_null($task) ? $task : '').(!is_null($component) ? $component : '')
        );
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

        $filters = array();
        !is_null($task) && $filters['task'] = $task;
        !is_null($task) && $filters['component_mode'] = $component;
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

        $components = $this->viewComponentHelper->getActiveComponents();
        $collection->getSelect()
            ->where('component_mode IN(\''.implode('\',\'',$components).'\') OR component_mode IS NULL');

        if (in_array(Ess_M2ePro_Helper_Component_Ebay::NICK, $components)
            && Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {

            $excludeTasks = array(
                Ess_M2ePro_Model_Synchronization_Log::TASK_FEEDBACKS,
                Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS
            );
            $collection->getSelect()->where('task NOT IN ('.implode(',', $excludeTasks).')');
        }

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

        if (!$this->viewComponentHelper->isSingleActiveComponent()) {
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

        $this->addColumn('task', array(
            'header'    => Mage::helper('M2ePro')->__('Synchronization'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'options',
            'index'     => 'task',
            'sortable'  => false,
            'filter_index' => 'main_table.task',
            'options' => $this->getActionTitles()
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'string_limit' => 350,
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
        $fullDescription = Mage::getModel('M2ePro/Log_Abstract')->decodeDescription($row->getData('description'));
        $row->setData('description', $fullDescription);
        $value = $column->getRenderer()->render($row);

        preg_match_all('/href="([^"]*)"/', $fullDescription, $matches);

        if (!count($matches[0])) {
            return $this->prepareLongText($fullDescription, $value);
        }

        foreach ($matches[1] as $key => $href) {

            preg_match_all('/route:([^;]*)/', $href, $routeMatch);
            preg_match_all('/back:([^;]*)/', $href, $backMatch);
            preg_match_all('/filter:([^;]*)/', $href, $filterMatch);

            if (count($routeMatch[1]) == 0) {
                $fullDescription = str_replace($matches[0][$key], '', $fullDescription);
                $value = str_replace($matches[0][$key], '', $value);

                continue;
            }

            $params = array();
            if (count($backMatch[1]) > 0) {
                $params['back'] = Mage::helper('M2ePro')->makeBackUrlParam($backMatch[1][$key]);
            }
            if (count($filterMatch[1]) > 0) {
                $params['filter'] = base64_encode($filterMatch[1][$key]);
            }

            $url = $routeMatch[1][$key];
            $fullDescription = str_replace($href, $this->getUrl($url, $params), $fullDescription);
            $value = str_replace($href, $this->getUrl($url, $params), $value);
        }

        return $this->prepareLongText($fullDescription, $value);
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

    abstract protected function getActionTitles();

    // ####################################
}