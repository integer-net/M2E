<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabaseGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('component');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareLayout()
    {
        $this->setPagerVisibility(false);

        return parent::_prepareLayout();
    }

   // ########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $moduleTables = Mage::helper('M2ePro/Module_Database')->getGroupedMySqlTables();
        $magentoTables = Mage::helper('M2ePro/Magento')->getMySqlTables();
        $databaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();

        $collection = new Varien_Data_Collection();
        foreach ($moduleTables as $moduleTable => $group) {

            $tableRow = array(
                'table_name' => $moduleTable,
                'component'  => 'general',
                'group'      => $group,
                'is_exist'   => false,
                'records'    => 0,
                'size'       => 0,
                'model'      => Mage::helper('M2ePro/Module_Database')->getTableModel($moduleTable)
            );

            // Set component
            //--------------------
            foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
                if (strpos(strtolower($moduleTable),strtolower($component)) !== false) {
                    $tableRow['component'] = $component;
                    break;
                }
            }
            //--------------------

            // Set is exist
            //--------------------
            $moduleTable = Mage::getSingleton('core/resource')->getTableName($moduleTable);
            $tableRow['is_exist'] = in_array($moduleTable, $magentoTables);
            //--------------------

            // Set table size/rows count
            //--------------------
            if ($tableRow['is_exist']) {

                $dbSelect = $connRead->select()->from('information_schema.tables',array('data_length'))
                                               ->where('`table_name` = ?',$moduleTable)
                                               ->where('`table_schema` = ?',$databaseName);

                $tempRow = $connRead->fetchRow($dbSelect);
                $tableRow['size'] = round($tempRow['data_length'] / 1024 / 1024, 2);

                $dbSelect = $connRead->select()->from($moduleTable, array('count' => 'COUNT(*)'));
                $tempRow = $connRead->fetchRow($dbSelect);
                $tableRow['records'] = $tempRow['count'];
            }
            //--------------------

            $collection->addItem(new Varien_Object($tableRow));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('table_name', array(
            'header'    => Mage::helper('M2ePro')->__('Table Name'),
            'align'     => 'left',
            'index'     => 'table_name',
            'filter_index' => 'table_name',
            'frame_callback' => array($this, 'callbackColumnTableName'),
            'filter_condition_callback' => array($this, '_customColumnFilter'),
        ));

        //--------------------
        $options['general'] = 'General';
        $options = array_merge($options,Mage::helper('M2ePro/Component')->getComponentsTitles());

        $this->addColumn('component', array(
            'header'    => Mage::helper('M2ePro')->__('Component'),
            'align'     => 'right',
            'width'     => '120px',
            'index'     => 'component',
            'type'      => 'options',
            'options'   => $options,
            'filter_index' => 'component',
            'filter_condition_callback' => array($this, '_customColumnFilter'),
        ));
        //--------------------

        //--------------------
        $options = array(
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_CONFIGS        => 'Configs',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_ACCOUNTS       => 'Accounts',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_MARKETPLACES   => 'Marketplaces',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_LISTINGS       => 'Listings',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_LISTINGS_OTHER => 'Listings Other',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_ITEMS          => 'Items',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_DICTIONARY     => 'Dictionary',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_ORDERS         => 'Orders',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_TEMPLATES      => 'Templates',
            Ess_M2ePro_Helper_Module_Database::TABLE_GROUP_OTHER          => 'Other'
        );

        $this->addColumn('group', array(
            'header'    => Mage::helper('M2ePro')->__('Group'),
            'align'     => 'right',
            'width'     => '100px',
            'index'     => 'group',
            'type'      => 'options',
            'options'   => $options,
            'filter_index' => 'group',
            'filter_condition_callback' => array($this, '_customColumnFilter'),
        ));
        //--------------------

        $this->addColumn('records', array(
            'header'    => Mage::helper('M2ePro')->__('Records'),
            'align'     => 'right',
            'width'     => '100px',
            'index'     => 'records',
            'type'      => 'number',
            'filter'    => false,
        ));

        $this->addColumn('size', array(
            'header'    => Mage::helper('M2ePro')->__('Size (Mb)'),
            'align'     => 'right',
            'width'     => '100px',
            'index'     => 'size',
            'filter'    => false,
        ));

        return parent::_prepareColumns();
    }

    // ########################################

    public function callbackColumnTableName($value, $row, $column, $isExport)
    {
        $style= '';

        is_null($row->getData('model')) && $style = 'color: #878787;';
        !$row->getData('is_exist') && $style = 'color: red; font-weight: bold;';

        return "<p style=\"{$style}\">{$value}</p>";
    }

    // ########################################

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('table_name');
        $this->getMassactionBlock()->setFormFieldName('tables');
        $this->getMassactionBlock()->setUseSelectAll(false);
        //--------------------------------

        // Set edit action
        //--------------------------------
        $this->getMassactionBlock()->addItem('edit', array(
            'label'    => Mage::helper('M2ePro')->__('Edit Table(s)'),
            'url'      => $this->getUrl('*/adminhtml_development_database/manageTables')
        ));
        //--------------------------------

        // Set truncate action
        //--------------------------------
        $this->getMassactionBlock()->addItem('truncate', array(
            'label'    => Mage::helper('M2ePro')->__('Truncate Table(s)'),
            'url'      => $this->getUrl('*/adminhtml_development_database/truncateTables'),
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ########################################

    protected function _toHtml()
    {
        $gridJsObj = $this->getId().'JsObject';

        $javascript = <<<JAVASCRIPT
<script>

    $$('#developmentDatabaseGrid_table select[name="component"]',
       '#developmentDatabaseGrid_table select[name="status"]',
       '#developmentDatabaseGrid_table select[name="group"]').each(function(el)
        {
            el.observe('change', function(){
                $gridJsObj.doFilter();
            });
        });

</script>
JAVASCRIPT;

        return parent::_toHtml().$javascript;
    }

    // ########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_development_database/databaseGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        if ($row->getData('is_exist') && $row->getData('model')) {
            return $this->getUrl(
                '*/adminhtml_development_database/manageTable',
                array(
                    'table' => $row->getData('table_name'),
                )
            );
        }
        return false;
    }

    // ####################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection() && $column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
        }
        return $this;
    }

    // ####################################

    protected function _customColumnFilter($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition = $column->getFilter()->getCondition();
        $value = array_pop($condition);

        if ($field && isset($condition)) {
            $field == 'table_name' && $this->_filterByTableNameField($field, $value);
            ($field == 'component' || $field == 'group') && $this->_filterByField($field, $value);
        }

        return $this;
    }

    //--------------------------------

    protected function _filterByTableNameField($field, $value)
    {
        $filteredCollection = new Varien_Data_Collection();
        $value = str_replace(array(' ','%','\\','\''),'',$value);

        foreach ($this->getCollection()->getItems() as $item) {
            if (strpos($item->getData($field),$value) !== false) {
                $filteredCollection->addItem($item);
            }
        }
        $this->setCollection($filteredCollection);
    }

    protected function _filterByField($field, $value)
    {
        $filteredCollection = new Varien_Data_Collection();
        $filteredItems = $this->getCollection()->getItemsByColumnValue($field,$value);

        foreach ($filteredItems as $item) {
            $filteredCollection->addItem($item);
        }
        $this->setCollection($filteredCollection);
    }

    // ####################################

    protected function _setCollectionOrder($column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $direction = $column->getDir();

        if ($field && isset($direction)) {
            $this->_orderByColumn($field, $direction);
        }

        return $this;
    }

    //--------------------------------

    protected function _orderByColumn($column, $direction)
    {
        $sortedCollection = new Varien_Data_Collection();

        $collection = $this->getCollection()->toArray();
        $collection = $collection['items'];

        $sortByColumn = array();
        foreach ($collection as $item) {
            $sortByColumn[] = $item[$column];
        }

        strtolower($direction) == 'asc' && array_multisort($sortByColumn, SORT_ASC, $collection);
        strtolower($direction) == 'desc' && array_multisort($sortByColumn, SORT_DESC, $collection);

        foreach ($collection as $item) {
            $sortedCollection->addItem(new Varien_Object($item));
        }
        $this->setCollection($sortedCollection);
    }

    // ####################################
}