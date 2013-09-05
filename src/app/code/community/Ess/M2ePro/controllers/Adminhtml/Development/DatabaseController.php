<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_DatabaseController
    extends Ess_M2ePro_Controller_Adminhtml_Development_MainController
{
    //#############################################

    public function manageTableAction()
    {
        $mainTable = $this->getRequest()->getParam('table');

        if (is_null($mainTable)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
            return;
        }

        $mainModel = NULL;

        $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
        foreach ($tempModels->asArray() as $tempModel => $tempTable) {
            if ($tempTable['table'] == $mainTable) {
                $mainModel = $tempModel;
                break;
            }
        }

        if (is_null($mainModel)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('data_table', $mainTable);
        Mage::helper('M2ePro/Data_Global')->setValue('data_model', $mainModel);

        $this->loadLayout()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_database_table'))
             ->renderLayout();
    }

    public function manageTablesAction()
    {
        $tables = $this->getRequest()->getParam('tables',array());
        $backUrl = Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl();

        $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');

        $response = '';
        foreach ($tables as $table) {

            $isModelFound = false;
            foreach ($tempModels->asArray() as $tempTable) {
                if ($tempTable['table'] == $table) {
                    $isModelFound = true;
                    break;
                }
            }

            if (!$isModelFound) {
                continue;
            }

            $url = $this->getUrl(
                '*/adminhtml_development_database/manageTable',
                array('table' => $table)
            );
            $response .= "window.open('{$url}');";
        }

        $response = "<script>
                        {$response}
                        setLocation('{$backUrl}');
                     </script>";

        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function deleteTableRowAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        if (is_null($id) || is_null($table) || is_null($model)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
        }

        Mage::getModel('M2ePro/'.$model)->load((int)$id)->delete();

        $this->afterTableAction($model);

        exit();
    }

    public function deleteTableSelectedRowsAction()
    {
        $ids   = $this->getRequest()->getParam('ids',array());
        !is_array($ids) && $ids = array($ids);

        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        if (is_null($table) || is_null($model)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
        }

        $modelInstance = Mage::getModel('M2ePro/'.$model);
        if (!$modelInstance) {
            return $this->_redirect('*/*/manageTable',array('table'=>$table));
        }

        $collection = $modelInstance->getCollection();
        $idFieldName = $modelInstance->getIdFieldName();

        $collection->addFieldToFilter($idFieldName, array('in' => $ids));

        if ($collection->getSize() == 0) {
            return $this->_redirect('*/*/manageTable',array('table'=>$table));
        }

        foreach ($collection as $item) {
            $item->delete();
        }

        $this->afterTableAction($model);

        $this->_redirect('*/*/manageTable',array('table'=>$table));
    }

    public function truncateTablesAction()
    {
        $tables = $this->getRequest()->getParam('tables',array());
        !is_array($tables) && $tables = array($tables);

        $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');

        $countTruncatedTables = 0;
        foreach ($tables as $table) {
            $model = NULL;
            foreach ($tempModels->asArray() as $tempModel => $tempTable) {
                if ($tempTable['table'] == $table) {
                    $model = $tempModel;
                    break;
                }
            }

            if (is_null($model)) {
                continue;
            }

            $tableAction  = Mage::getSingleton('core/resource')->getTableName($table);
            Mage::getSingleton('core/resource')->getConnection('core_write')->delete($tableAction);

            $this->afterTableAction($model);
            $countTruncatedTables++;
        }

        count($tables) == $countTruncatedTables ?
            $this->_getSession()->addSuccess('Truncate tables was successfully completed.') :
            $this->_getSession()->addError('Some tables was not truncated. (Resource model are missing.)');

        if (count($tables) == 1) {
            $this->_redirect('*/*/manageTable',array('table'=>array_shift($tables)));
        }
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
    }

    //#############################################

    public function updateTableCellAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        $column = $this->getRequest()->getParam('column');
        $value = $this->getRequest()->getParam('value');

        if (is_null($id) || is_null($table) || is_null($model)) {
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageDatabaseTabUrl());
            return;
        }

        if (strtolower($value) == 'null') {
            $value = NULL;
        }

        Mage::getModel('M2ePro/'.$model)->load((int)$id)->setData($column,$value)->save();

        $this->afterTableAction($model);

        exit();
    }

    private function afterTableAction($model)
    {
        if (strpos($model, 'Config_') === 0 || strpos($model, 'Wizard') === 0) {
            Mage::helper('M2ePro/Module')->clearCache();
        }
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
        $mainTable = $this->getRequest()->getParam('table');
        $mainModel = NULL;

        $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
        foreach ($tempModels->asArray() as $tempModel => $tempTable) {
            if ($tempTable['table'] == $mainTable) {
                $mainModel = $tempModel;
                break;
            }
        }

        Mage::helper('M2ePro/Data_Global')->setValue('data_table', $mainTable);
        Mage::helper('M2ePro/Data_Global')->setValue('data_model', $mainModel);

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_development_tabs_database_table_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################
}