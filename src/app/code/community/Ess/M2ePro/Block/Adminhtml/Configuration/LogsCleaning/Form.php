<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_LogsCleaning_Form
    extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configurationLogsCleaningForm');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/logsCleaning.phtml');
    }

    // ########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_logsCleaning/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/LogCleaningHandler.js');
    }

    protected function _beforeToHtml()
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $tasks = array(
            Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS,
            Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS,
            Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS,
            Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS
        );

        //----------------------------
        $modes = array();
        $days  = array();

        foreach ($tasks as $task) {
            $modes[$task] = $config->getGroupValue('/logs/cleaning/'.$task.'/','mode');
            $days[$task] = $config->getGroupValue('/logs/cleaning/'.$task.'/','days');
        }

        $this->modes = $modes;
        $this->days = $days;
        //----------------------------

        foreach ($tasks as $task) {
            //------------------------------
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                'onclick' => 'LogCleaningHandlerObj.runNowLog(\'' . $task . '\')',
                'class'   => 'run_now_' . $task
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('run_now_'.$task, $buttonBlock);
            //------------------------------

            if ($task == Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS) {
                continue;
            }

            //------------------------------
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                'onclick' => 'LogCleaningHandlerObj.clearAllLog(\'' . $task . '\')',
                'class'   => 'clear_all_' . $task
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('clear_all_'.$task, $buttonBlock);
            //------------------------------
        }

        return parent::_beforeToHtml();
    }

    // ########################################
}