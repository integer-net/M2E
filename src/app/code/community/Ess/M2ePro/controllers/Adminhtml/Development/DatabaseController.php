<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_DatabaseController
    extends Ess_M2ePro_Controller_Adminhtml_Development_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()->getLayout()->getBlock('head')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Development/DatabaseGridHandler.js');

        $this->_initPopUp();

        return $this;
    }

    //#############################################

    public function manageTableAction()
    {
        $this->_initAction();

        $table = $this->getRequest()->getParam('table');

        if (is_null($table)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
            return;
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_database_table'))
             ->renderLayout();
    }

    public function manageTablesAction()
    {
        $tables = $this->getRequest()->getParam('tables', array());

        $response = '';
        foreach ($tables as $table) {

            if (is_null($model = Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($table))) {
                continue;
            }

            $url = Mage::helper('adminhtml')->getUrl('*/adminhtml_development_database/manageTable',
                                                     array('table' => $table, 'model' => $model));

            $response .= "window.open('{$url}');";
        }

        $backUrl = Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl();

        $response = "<script>
                        {$response}
                        window.location = '{$backUrl}';
                     </script>";

        $this->getResponse()->setBody($response);
    }

    //---------------------------------------------

    public function deleteTableRowsAction()
    {
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        $ids = $this->getRequest()->getParam('ids');
        $ids = explode(',', $ids);

        if (is_null($table) || is_null($model)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
            return;
        }

        if (empty($ids)) {
            $this->redirectToTablePage($table, $model);
        }

        if (!$modelInstance = Mage::getModel('M2ePro/'.$model)) {
            $this->_getSession()->addError("Failed to get model {$model}.");
            $this->redirectToTablePage($table, $model);
        }

        $collection = $modelInstance->getCollection();
        $idFieldName = $modelInstance->getIdFieldName();

        $collection->addFieldToFilter($idFieldName, array('in' => $ids));

        if ($collection->getSize() == 0) {
            $this->redirectToTablePage($table, $model);
        }

        foreach ($collection as $item) {
            $item->delete();
        }

        $this->afterTableAction($model);
        $this->redirectToTablePage($table, $model);
    }

    public function truncateTablesAction()
    {
        $tables = $this->getRequest()->getParam('tables',array());
        !is_array($tables) && $tables = array($tables);

        foreach ($tables as $table) {

            $model = Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($table);
            $tableName  = Mage::getSingleton('core/resource')->getTableName($table);

            Mage::getSingleton('core/resource')->getConnection('core_write')->delete($tableName);
            $this->afterTableAction($model);
        }

        $this->_getSession()->addSuccess('Truncate tables was successfully completed.');

        if (count($tables) == 1) {
            $tableName = array_shift($tables);
            $this->redirectToTablePage($tableName,
                                       Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($tableName));
        }

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
    }

    public function updateTableCellsAction()
    {
        $ids = explode(',', $this->getRequest()->getParam('ids'));

        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        $cellsValues = $this->prepareCellsValuesArray();

        if (is_null($table) || is_null($model) || empty($ids) || empty($cellsValues)) {
            return;
        }

        if (!$modelInstance = Mage::getModel('M2ePro/'.$model)) {
            return;
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            Mage::getSingleton('core/resource')->getTableName($table),
            $cellsValues,
            "`{$modelInstance->getIdFieldName()}` IN (".implode(',', $ids).")"
        );

        $this->afterTableAction($model);
    }

    public function addTableRowAction()
    {
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        $cellsValues = $this->prepareCellsValuesArray();

        if (is_null($table) || is_null($model) || empty($cellsValues)) {
            return;
        }
        if (!$modelInstance = Mage::getModel('M2ePro/'.$model)) {
            return;
        }

        $modelInstance->setData($cellsValues)->save();
    }

    private function prepareCellsValuesArray()
    {
        $cells = $this->getRequest()->getParam('cells', array());
        is_string($cells) && $cells = array($cells);

        $bindArray = array();
        foreach ($cells as $columnName) {

            if (is_null($columnValue = $this->getRequest()->getParam('value_'.$columnName))) {
                continue;
            }

            strtolower($columnValue) == 'null' && $columnValue = NULL;
            $bindArray[$columnName] = $columnValue;
        }

        return $bindArray;
    }

    private function afterTableAction($model)
    {
        if (is_null($model)) {
            return;
        }

        if (strpos($model, 'Config_') !== 0 && strpos($model, 'Wizard') !== 0) {
            return;
        }

        Mage::helper('M2ePro/Module')->clearCache();
    }

    //#############################################

    public function databaseGridAction()
    {
        $response = $this->loadLayout()
                         ->getLayout()
                         ->createBlock('M2ePro/adminhtml_development_tabs_database_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function databaseTableGridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database_table_grid')->toHtml();

        $this->getResponse()->setBody($response);
    }

    public function getTableCellsPopupHtmlAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database_table_tableCellsPopup')->toHtml();

        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function redirectToTablePage($tableName, $modelName)
    {
        $this->_redirect('*/*/manageTable', array('table' => $tableName, 'model' => $modelName));
    }

    //#############################################
}