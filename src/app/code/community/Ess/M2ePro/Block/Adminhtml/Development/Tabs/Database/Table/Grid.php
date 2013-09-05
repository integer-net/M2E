<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentTable'.Mage::helper('M2ePro/Data_Global')->getValue('data_model').'Grid');
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

    protected function _prepareCollection()
    {
        // Get collection of prices templates
        $collection = Mage::getModel('M2ePro/'.Mage::helper('M2ePro/Data_Global')->getValue('data_model'))
                                                                                 ->getCollection();
        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $resourceModel = Mage::getResourceModel('M2ePro/'.Mage::helper('M2ePro/Data_Global')->getValue('data_model'));

        $tableAction = Mage::getSingleton('core/resource')
                                 ->getTableName(Mage::helper('M2ePro/Data_Global')->getValue('data_table'));
        $columns = $resourceModel->getReadConnection()->fetchAll('SHOW COLUMNS FROM '.$tableAction);

        $type = 'number';
        foreach ($columns as $column) {

            $header = '<big>'.$column['Field'].'</big> &nbsp;
                       <small style="font-weight:normal;">('.$column['Type'].')</small>';

            $this->addColumn($column['Field'], array(
                'header'    => $header,
                'align'     => 'left',
                //'width'     => '200px',
                'type'      => $type,
                'string_limit' => 10000,
                'index'     => strtolower($column['Field']),
                'filter_index' => 'main_table.'.strtolower($column['Field']),
                'frame_callback' => array($this, 'callbackColumnData')
            ));

            $type = 'text';
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

    // ####################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------

        //--------------------------------
        $url = $this->getUrl(
            '*/*/deleteTableSelectedRows',
            array(
                'model'=>Mage::helper('M2ePro/Data_Global')->getValue('data_model'),
                'table'=>Mage::helper('M2ePro/Data_Global')->getValue('data_table')
            )
        );
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('M2ePro')->__('Delete'),
             'url'      => $url,
             'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    // ####################################

    public function callbackColumnData($value, $row, $column, $isExport)
    {
        $cellId = 'table_row_cell_'.$column->getId().'_'.$row->getId();

        $htmlValue = '<div id="'.$cellId.'" onmouseover="mouseOverCell(\''.$cellId.'\');"
                                            onmouseout="mouseOutCell(\''.$cellId.'\');">';

        $htmlValue .= '<span id="'.$cellId.'_view_container">';
        if (is_null($value)) {
            $htmlValue .= '<span style="color:silver;"><small>NULL</small></span>';
        } else {
            $tempValue = $value;
            strlen($tempValue) > 255 && $tempValue = substr($tempValue,0,255).' ...';
            $htmlValue .= Mage::helper('M2ePro')->escapeHtml($tempValue);
        }
        $htmlValue .= '</span>';

        $inputValue = $value;

        if (is_null($inputValue)) {
            $inputValue = 'NULL';
        } else {
            $inputValue = Mage::helper('M2ePro')->escapeHtml($inputValue);
        }

        $htmlValue .= '<span id="'.$cellId.'_edit_container" style="display:none;">';
        $htmlValue .= '<textarea style="width:100%;height:100%;" id="'.$cellId.'_edit_input">';
        $htmlValue .= $inputValue;
        $htmlValue .= '</textarea>';
        $htmlValue .= '</span>';

        $tempUrl = $this->getUrl(
            '*/*/updateTableCell',
            array(
                'id'=>$row->getId(),
                'column'=>$column->getId(),
                'model'=>Mage::helper('M2ePro/Data_Global')->getValue('data_model'),
                'table'=>Mage::helper('M2ePro/Data_Global')->getValue('data_table'),
            )
        );

        $htmlValue .= '&nbsp;<a id="'.$cellId.'_edit_link"
                                href="javascript:void(0);"
                                onclick="switchCellToEdit(\''.$cellId.'\');"
                                style="display:none;">edit</a>';

        $htmlValue .= '&nbsp;<a id="'.$cellId.'_view_link"
                                href="javascript:void(0);"
                                onclick="switchCellToView(\''.$cellId.'\');"
                                style="display:none;">cancel</a>';
        $htmlValue .= '&nbsp;<a id="'.$cellId.'_save_link"
                                href="javascript:void(0);"
                                onclick="saveCell(\''.$cellId.'\',\''.$tempUrl.'\');"
                                style="display:none;">save</a>';

        return $htmlValue.'</div>';
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $resultHtml = '';
        $tempId = $row->getId();

        $tempUrl = $this->getUrl(
            '*/*/deleteTableRow',
            array(
                'id'=>$tempId,
                'model'=>Mage::helper('M2ePro/Data_Global')->getValue('data_model'),
                'table'=>Mage::helper('M2ePro/Data_Global')->getValue('data_table'),
            )
        );
        $resultHtml .= '<a href="javascript:void(0);" onclick="deleteTableRow(\''.$tempUrl.'\')">Delete</a>';

        return $resultHtml;
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
}