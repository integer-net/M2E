<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Synchronization
    extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_synchronization';
        //------------------------------

        // Form id of marketplace_general_form
        //------------------------------
        $this->tabsContainerId = 'edit_form';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization');
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

        if (!(bool)$this->getRequest()->getParam('wizard',false)) {
            //------------------------------
            $url = $this->getUrl('*/adminhtml_common_account/index');
            $this->_addButton('goto_accounts', array(
                'label'     => Mage::helper('M2ePro')->__('Accounts'),
                'onclick'   => 'setLocation(\''. $url .'\')',
                'class'     => 'button_link'
            ));
            //------------------------------

            //------------------------------
            $url = $this->getUrl('*/adminhtml_common_log/synchronization');
            $this->_addButton('view_log', array(
                'label'     => Mage::helper('M2ePro')->__('View Log'),
                'onclick'   => 'window.open(\'' . $url . '\')',
                'class'     => 'button_link'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'SynchronizationHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
            //------------------------------

            //------------------------------
            $params = Mage::helper('M2ePro')->escapeHtml(
                json_encode(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents())
            );
            $this->_addButton('run_all_enabled_now', array(
                'label'     => Mage::helper('M2ePro')->__('Run Enabled Now'),
                'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'runAllEnabledNow\', ' . $params . ');',
                'class'     => 'save'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save Settings'),
                'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'\', ' . $params . ')',
                'class'     => 'save'
            ));
            //------------------------------
        } else {
            $activeWizardNick = Mage::helper('M2ePro/Module_Wizard')->getNick(
                Mage::helper('M2ePro/Module_Wizard')->getActiveWizard(Ess_M2ePro_Helper_View_Common::NICK)
            );

            $this->setEnabledTab($activeWizardNick);

            //------------------------------
            $escapedWizardNick = Mage::helper('M2ePro')->escapeHtml("'" . $activeWizardNick . "'");
            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save Settings'),
                'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'\',' . $escapedWizardNick . ')',
                'class'     => 'save'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                'onclick'   => 'SynchronizationHandlerObj.completeStep();',
                'class'     => 'close'
            ));
            //------------------------------
        }
    }

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        SynchProgressBarObj = new ProgressBar('synchronization_progress_bar');
        SynchWrapperObj = new AreaWrapper('synchronization_content_container');
    });

</script>
JAVASCRIPT;

        return $javascriptsMain .
               '<div id="synchronization_progress_bar"></div>' .
               '<div id="synchronization_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (!$this->getChild('amazon_tab')) {
            $this->setChild(
                'amazon_tab', $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_synchronization_form')
            );
        }
        return $this->getChild('amazon_tab');
    }

    public function getAmazonTabHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_synchronization_help');

        return $helpBlock->toHtml() . parent::getAmazonTabHtml();
    }

    // ########################################

    protected function getBuyTabBlock()
    {
        if (!$this->getChild('buy_tab')) {
            $this->setChild(
                'buy_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_synchronization_form')
            );
        }
        return $this->getChild('buy_tab');
    }

    public function getBuyTabHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_synchronization_help');

        return $helpBlock->toHtml() . parent::getBuyTabHtml();
    }

    // ########################################

    protected function getPlayTabBlock()
    {
        if (!$this->getChild('play_tab')) {
            $this->setChild(
                'play_tab',
                $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_synchronization_form')
            );
        }
        return $this->getChild('play_tab');
    }

    public function getPlayTabHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_play_synchronization_help');

        return $helpBlock->toHtml() . parent::getPlayTabHtml();
    }

    // ########################################

    protected function _componentsToHtml()
    {
        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

        return parent::_componentsToHtml() . $formBlock->toHtml();
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '';
    }

    // ########################################

    public function canShowRunNowButton($nick)
    {
        if (!(bool)$this->getRequest()->getParam('wizard',false)) {
            return true;
        }

        $activeWizard = Mage::helper('M2ePro/Module_Wizard')->getActiveWizard(Ess_M2ePro_Helper_View_Common::NICK);

        if (!$activeWizard || Mage::helper('M2ePro/Module_Wizard')->getNick($activeWizard) != $nick) {
            return true;
        }

        return false;
    }

    // ########################################
}