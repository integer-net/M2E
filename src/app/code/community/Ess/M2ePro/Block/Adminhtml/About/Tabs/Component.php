<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_Tabs_Component extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        //------------------------------
        $this->setTemplate('M2ePro/about/tabs/component.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $moduleDbTables = Mage::helper('M2ePro/Module')->getMySqlTables();
        $magentoDbTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $mysql['tables'] = array();
        foreach ($moduleDbTables as $moduleTable) {

            if (strpos(strtolower($moduleTable),strtolower($this->getComponent())) === false) {
                continue;
            }

            $arrayKey = $moduleTable;
            $arrayValue = array(
                'is_exist' => false,
                'count_items' => 0,
                'manage_link' => $this->getUrl('*/*/manageDbTable',array('table'=>$arrayKey)),
                'has_model' => false
            );

            // Find model
            //--------------------
            $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
            foreach ($tempModels->asArray() as $tempTable) {
                if ($tempTable['table'] == $arrayKey) {
                    $arrayValue['has_model'] = true;
                    break;
                }
            }
            //--------------------

            $moduleTable = Mage::getSingleton('core/resource')->getTableName($moduleTable);
            $arrayValue['is_exist'] = in_array($moduleTable, $magentoDbTables);

            if ($arrayValue['is_exist']) {
                $dbSelect = $connRead->select()->from($moduleTable,new Zend_Db_Expr('COUNT(*)'));
                $arrayValue['count_items'] = (int)$connRead->fetchOne($dbSelect);
            }

            //var_dump($arrayKey,$arrayValue);

            $mysql['tables'][$arrayKey] = $arrayValue;
        }

        $this->mysql = $mysql;
        //----------------------------

        //----------------------------
        $this->show_cmd = !is_null($this->getRequest()->getParam('show_cmd'));
        //----------------------------
    }

    // ########################################
}