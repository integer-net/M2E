<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    private $modelName = null;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentTable'.$this->getRequest()->getParam('table').'Grid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function getModelName()
    {
        if (!is_null($this->modelName)) {
            return $this->modelName;
        }

        $modelName = Mage::helper('M2ePro/Module_Database')->getTableModel(
            $this->getRequest()->getParam('table')
        );

        if (is_null($modelName)) {
            throw new Exception(sprintf(
                'Specified table "%s" cannot be managed.', $this->getRequest()->getParam('table')
            ));
        }

        return $this->modelName = $modelName;
    }

    // ####################################

    protected function _prepareCollection()
    {
        // Get collection of prices templates
        $collection = Mage::getModel('M2ePro/'.$this->getModelName())->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $resourceModel = Mage::getResourceModel('M2ePro/'.$this->getModelName());
        $tableAction = Mage::getSingleton('core/resource')->getTableName($this->getRequest()->getParam('table'));

        $columns = $resourceModel->getReadConnection()->fetchAll('SHOW COLUMNS FROM '.$tableAction);

        foreach ($columns as $column) {

            $header = '<big>'.$column['Field'].'</big> &nbsp;
                       <small style="font-weight:normal;">('.$column['Type'].')</small>';

            $columnType = $this->getColumnType($column);

            $params = array(
                'header'    => $header,
                'align'     => 'left',
                'type'      => $columnType,
                'string_limit' => 10000,
                'index'     => strtolower($column['Field']),
                'filter_index' => 'main_table.'.strtolower($column['Field']),
                'frame_callback' => array($this, 'callbackColumnData'),
            );

            if ($columnType == 'datetime') {
                $params = array_merge($params, array(
                    'filter_time' => true,
                    'align'       => 'right',
                    'renderer' => 'M2ePro/adminhtml_development_tabs_database_table_grid_column_renderer_datetime',
                    'filter'   => 'M2ePro/adminhtml_development_tabs_database_table_grid_column_filter_datetime'
                ));
            }

            $this->addColumn($column['Field'], $params);
        }

        $this->addColumn('actions_row', array(
            'header'    => '&nbsp;'.Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'text',
            'index'     => 'actions_row',
            'filter'    => false,
            'sortable'  => false,
            'frame_callback' => array($this, 'callbackColumnActions')
        ));

        return parent::_prepareColumns();
    }

    protected function _toHtml()
    {
        $gridId = $this->getId();

        $urlParams = array(
            'model' => $this->getModelName(),
            'table' => $this->getRequest()->getParam('table')
        );

        $root = 'adminhtml_development_database';
        $urls = json_encode(array(
            $root.'/deleteTableRows' => $this->getUrl('*/*/deleteTableRows', $urlParams),
            $root.'/updateTableCells' => $this->getUrl('*/*/updateTableCells', $urlParams),
            $root.'/getUpdateCellsPopupHtml' => $this->getUrl('*/*/getUpdateCellsPopupHtml', $urlParams)
        ));

        $commonJs = <<<HTML
<script type="text/javascript">
    DevelopmentDatabaseGridHandlerObj.afterInitPage();
</script>
HTML;
        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">

   M2ePro.url.add({$urls});
   DevelopmentDatabaseGridHandlerObj = new DatabaseGridHandler('{$gridId}');

</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    // ####################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        //--------------------------------
        $this->getMassactionBlock()->addItem('deleteTableRows', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => '',
        ));
        //--------------------------------

        //--------------------------------
        $this->getMassactionBlock()->addItem('updateTableCells', array(
            'label'    => Mage::helper('M2ePro')->__('Update'),
            'url'      => ''
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnData($value, $row, $column, $isExport)
    {
        $cellId = 'table_row_cell_'.$column->getId().'_'.$row->getId();

        $tempValue = '<span style="color:silver;"><small>NULL</small></span>';
        if (!is_null($value)) {
            $tempValue = strlen($value) > 255 ? substr($value,0,255).' ...' : $value;
            $tempValue = Mage::helper('M2ePro')->escapeHtml($tempValue);
        }

        $inputValue = 'NULL';
        if (!is_null($value)) {
            $inputValue = Mage::helper('M2ePro')->escapeHtml($value);
        }

        return <<<HTML
<div id="{$cellId}" onmouseover="DevelopmentDatabaseGridHandlerObj.mouseOverCell('{$cellId}');"
                    onmouseout="DevelopmentDatabaseGridHandlerObj.mouseOutCell('{$cellId}');">

    <span id="{$cellId}_view_container">{$tempValue}</span>

    <span id="{$cellId}_edit_container" style="display: none;">
        <textarea style="width:100%; height:100%;" id="{$cellId}_edit_input">{$inputValue}</textarea>
    </span>

    <span id="{$cellId}_edit_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="DevelopmentDatabaseGridHandlerObj.switchCellToEdit('{$cellId}');">edit</a>
    </span>
    <span id="{$cellId}_view_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="DevelopmentDatabaseGridHandlerObj.switchCellToView('{$cellId}');">cancel</a>
    </span>
    <span id="{$cellId}_save_link" style="display: none;">&nbsp;
        <a href="javascript:void(0);"
           onclick="DevelopmentDatabaseGridHandlerObj.saveTableCell('{$row->getId()}','{$column->getId()}');">save</a>
    </span>
</div>
HTML;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        return <<<HTML
<a href="javascript:void(0);" onclick="DevelopmentDatabaseGridHandlerObj.deleteTableRows('{$row->getId()}')">
    Delete
</a>
HTML;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/databaseTableGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        //return $this->getUrl('*/*/editTableRow', array('id' => $row->getId()));
    }

    // ####################################

    private function getColumnType($columnData)
    {
        if ($columnData['Type'] == 'datetime') {
            return 'datetime';
        }

        if (preg_match('/int|float|decimal/', $columnData['Type'])) {
            return 'number';
        }

        return 'text';
    }

    // ####################################
}