<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database_Table extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabaseTable');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_development_tabs_database_table';
        //------------------------------

        // Set header text
        //------------------------------
        $tableName = $this->getRequest()->getParam('table');
        $this->_headerText = Mage::helper('M2ePro')->__('Manage Table "%table_name%"', $tableName);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl();
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => "window.open('{$url}','_blank')",
            'class'     => 'back'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/*/truncateTables', array('tables' => $this->getRequest()->getParam('table')));
        $this->_addButton('delete_all', array(
            'label'     => Mage::helper('M2ePro')->__('Truncate Table'),
            'onclick'   => 'deleteConfirm(\'Are you sure?\', \''.$url.'\')',
            'class'     => 'delete_all'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('add_row', array(
            'label'     => Mage::helper('M2ePro')->__('Append Row'),
            'onclick'   => 'DevelopmentDatabaseGridHandlerObj.openTableCellsPopup(\'add\')',
            'class'     => 'delete_all'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    // ########################################
}