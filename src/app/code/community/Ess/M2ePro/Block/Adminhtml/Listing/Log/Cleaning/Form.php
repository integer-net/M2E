<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Log_Cleaning_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingLogCleaningForm');
        //------------------------------

        $this->setTemplate('M2ePro/listing/log/cleaning.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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
            $runNowButton = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                    'label'   => Mage::helper('M2ePro')->__('Run Now'),
                    'onclick' => 'LogCleaningHandlerObj.runNowLog(\''.$task.'\')',
                    'class' => 'run_now_'.$task
                ) );
            $this->setChild('run_now_'.$task,$runNowButton);

            $viewLogButton = $this->getLayout()
                                ->createBlock('adminhtml/widget_button')
                                ->setData( array(
                                    'label'   => Mage::helper('M2ePro')->__('View Log'),
                                    'onclick' => 'setLocation(\''.$this->getLogUrl($task).'\')',
                                    'class' => 'button_link'
                                ) );
            $this->setChild('view_log_'.$task,$viewLogButton);

            if ($task == Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS) {
                continue;
            }

            $clearAllButton = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                    'label'   => Mage::helper('M2ePro')->__('Clear All'),
                    'onclick' => 'LogCleaningHandlerObj.clearAllLog(\''.$task.'\')',
                    'class' => 'clear_all_'.$task
                ) );
            $this->setChild('clear_all_'.$task,$clearAllButton);
        }

        return parent::_beforeToHtml();
    }

    private function getLogUrl($task)
    {
        $url = '';
        $params = array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logCleaning/index'));

        switch ($task) {
            case Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS:
                $url = $this->getUrl('*/adminhtml_log/listing', $params);
                break;
            case Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS:
                $url = $this->getUrl('*/adminhtml_log/listingOther', $params);
                break;
            case Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS:
                $url = $this->getUrl('*/adminhtml_log/synchronization', $params);
                break;
            case Ess_M2ePro_Model_Log_Cleaning::LOG_ORDERS:
                $url = $this->getUrl('*/adminhtml_log/order', $params);
                break;
        }

        return $url;
    }
}