<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_AboutController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/help')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Help'))
             ->_title(Mage::helper('M2ePro')->__('About'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/help/about');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_about'))
             ->renderLayout();
    }

    //#############################################

    public function manageDbTableAction()
    {
        $mainTable = $this->getRequest()->getParam('table');

        if (is_null($mainTable)) {
            $this->_redirect('*/*/index');
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
            $this->_redirect('*/*/index');
            return;
        }

        Mage::helper('M2ePro')->setGlobalValue('data_table', $mainTable);
        Mage::helper('M2ePro')->setGlobalValue('data_model', $mainModel);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_about_manageDbTable'))
             ->renderLayout();
    }

    public function manageDbTableGridAction()
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

        Mage::helper('M2ePro')->setGlobalValue('data_table', $mainTable);
        Mage::helper('M2ePro')->setGlobalValue('data_model', $mainModel);

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_about_manageDbTable_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function deleteDbTableRowAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        if (is_null($id) || is_null($table) || is_null($model)) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::getModel('M2ePro/'.$model)->load((int)$id)->delete();

        $this->afterDbTableAction($model);

        exit();
    }

    public function deleteDbTableSelectedRowsAction()
    {
        $model = $this->getRequest()->getParam('model');
        $table = $this->getRequest()->getParam('table');
        $key   = $this->getRequest()->getParam('massaction_prepare_key');
        $ids   = $this->getRequest()->getParam($key);

        if (is_null($table) || is_null($model)) {
            return $this->_redirect('*/*/index');
        }

        $modelInstance = Mage::getModel('M2ePro/'.$model);
        if (!$modelInstance) {
            return $this->_redirect('*/*/manageDbTable',array('table'=>$table));
        }

        $collection = $modelInstance->getCollection();
        $idFieldName = $modelInstance->getIdFieldName();

        $collection->addFieldToFilter($idFieldName, array('in' => $ids));

        if ($collection->getSize() == 0) {
            return $this->_redirect('*/*/manageDbTable',array('table'=>$table));
        }

        foreach ($collection as $item) {
            $item->delete();
        }

        $this->afterDbTableAction($model);

        $this->_redirect('*/*/manageDbTable',array('table'=>$table));
    }

    public function deleteDbTableAllAction()
    {
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        if (is_null($table) || is_null($model)) {
            $this->_redirect('*/*/index');
            return;
        }

        $tableAction  = Mage::getSingleton('core/resource')->getTableName($table);
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete($tableAction);

        $this->afterDbTableAction($model);

        $this->_redirect('*/*/manageDbTable',array('table'=>$table));
    }

    public function updateDbTableCellAction()
    {
        $id = $this->getRequest()->getParam('id');
        $table = $this->getRequest()->getParam('table');
        $model = $this->getRequest()->getParam('model');

        $column = $this->getRequest()->getParam('column');
        $value = $this->getRequest()->getParam('value');

        if (is_null($id) || is_null($table) || is_null($model)) {
            $this->_redirect('*/*/index');
            return;
        }

        if (strtolower($value) == 'null') {
            $value = NULL;
        }

        Mage::getModel('M2ePro/'.$model)->load((int)$id)->setData($column,$value)->save();

        $this->afterDbTableAction($model);

        exit();
    }

    private function afterDbTableAction($model)
    {
        if (strpos($model, 'Config_') === 0) {
            Mage::helper('M2ePro/Module')->clearCache();
        }
    }

    //#############################################
}