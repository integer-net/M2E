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

        $this->_headerText = '';

        if (!(bool)$this->getRequest()->getParam('wizard',false)) {

            $this->setTemplate(NULL);

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

            // Set header text
            //------------------------------
            $this->_headerText = Mage::helper('M2ePro')->__('Synchronization');
            //------------------------------

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

    // ########################################

    protected function _componentsToHtml()
    {
        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

        $tabsContainer = $this->getTabsContainerBlock();
        $tabsContainer->setDestElementId($this->tabsContainerId);

        foreach ($this->tabs as $tabId) {
            $tab = $this->prepareTabById($tabId);
            $tabsContainer->addTab($tabId, $tab);
        }

        $tabsContainer->setActiveTab($this->getActiveTab());

        $hideChannels = '';
        $tabsIds = $tabsContainer->getTabsIds();
        if (count($tabsIds) <= 1) {
            $hideChannels = ' style="visibility: hidden"';
        }

        $hideTabsHeader = '';
        $help = '';
        if ((bool)$this->getRequest()->getParam('wizard',false)) {
            $hideTabsHeader = ' style="display: none"';
            $help = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_help')->toHtml();
        }

        return <<<HTML
<div class="content-header skip-header"{$hideTabsHeader}>
    <table cellspacing="0">
        <tr>
            <td{$hideChannels}>{$tabsContainer->toHtml()}</td>
            <td class="form-buttons">{$this->getButtonsHtml()}</td>
        </tr>
    </table>
</div>
{$help}
{$formBlock->toHtml()}
HTML;

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

    protected function getTabsContainerBlock()
    {
        if (is_null($this->tabsContainerBlock)) {
            $this->tabsContainerBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization_tabs');
        }

        return $this->tabsContainerBlock;
    }

    // ########################################

}