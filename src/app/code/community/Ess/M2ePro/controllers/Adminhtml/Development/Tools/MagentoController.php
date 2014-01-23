<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Tools_MagentoController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    private function getStyleHtml()
    {
        return <<<HTML
<style type="text/css">

    table.grid {
        border-color: black;
        border-style: solid;
        border-width: 1px 0 0 1px;
    }
    table.grid th {
        padding: 5px 20px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
        background-color: silver;
        color: white;
        font-weight: bold;
    }
    table.grid td {
        padding: 3px 10px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
    }

</style>
HTML;
    }

    //---------------------------------------------

    /**
     * @title "Show Overwriten Models"
     * @description "Show Overwriten Models"
     */
    public function showOverwritenModelsAction()
    {
        $overwrittenModels = Mage::helper('M2ePro/Magento')->getRewrites();

        if (count ($overwrittenModels) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">No Overwritten Models</span></h2>';
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Overwritten Models
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 600px">
            From
        </th>
        <th style="width: 600px">
            To
        </th>
    </tr>
HTML;
        foreach ($overwrittenModels as $item) {

            $rewritedFrom = $item['from'];
            $rewritedTo = $item['to'];

            $html .= <<<HTML
<tr>
    <td>
        $rewritedFrom
    </td>
    <td>
        $rewritedTo
    </td>
</tr>

HTML;
        }

        $html .= '</table>';

        print str_replace('%count%',count($overwrittenModels),$html);
    }

    /**
     * @title "Show Local Pool Overwrites"
     * @description "Show Local Pool Overwrites"
     * @new_line
     */
    public function showLocalPoolOverwritesAction()
    {
        $localPoolOverwrites = Mage::helper('M2ePro/Magento')->getLocalPoolOverwrites();

        if (count($localPoolOverwrites) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">No Local Pool Overwrites</span></h2>';
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Local Pool Overwrites
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 800px">
            Path
        </th>
    </tr>
HTML;
        foreach ($localPoolOverwrites as $item) {

            $html .= <<<HTML
<tr>
    <td>
        $item
    </td>
</tr>

HTML;
        }

        $html .= '</table>';

        print str_replace('%count%',count($localPoolOverwrites),$html);
    }

    /**
     * @title "Show Installed Modules"
     * @description "Show Installed Modules"
     * @new_line
     */
    public function showInstalledModulesAction()
    {
        $installedModules = Mage::getConfig()->getNode('modules')->asArray();

        if (count($installedModules) <= 0) {
            echo '<h2 style="margin: 20px 0 0 10px">No Installed Modules</span></h2>';
            return;
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Installed Modules
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 500px">
            Module
        </th>
        <th style="width: 100px">
            Status
        </th>
        <th style="width: 100px">
            Code Pool
        </th><th style="width: 100px">
            Version
        </th>
    </tr>
HTML;
        foreach ($installedModules as $module => $data) {

            $status = $data['active']
                ? 'Enabled'
                : 'Disabled';

            $codePool = $data['codePool'];
            $version = $data['version']!=''?$data['version']:'&nbsp;';

            $html .= <<<HTML
<tr>
    <td>
        $module
    </td>
    <td>
        $status
    </td>
    <td>
        $codePool
    </td>
    <td>
        $version
    </td>
</tr>

HTML;
        }

        $html .= '</table>';

        print str_replace('%count%',count($installedModules),$html);
    }

    /**
     * @title "Refresh Compilation"
     * @description "Refresh Compilation"
     * @confirm "Are you sure?"
     */
    public function refreshCompilationAction()
    {
        if (!defined('COMPILER_INCLUDE_PATH')) {
            $this->_getSession()->addError('Compilation is not enabled');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
        } else {
            $this->_redirect('*/*/runCompilation');
            Mage::getModel('compiler/process')->clear();
            $this->getResponse()->sendHeaders();
        }
    }

    /**
     * @title "Run Compilation"
     * @description "Run Compilation"
     * @hidden
     */
    public function runCompilationAction()
    {
        try {
            Mage::getModel('compiler/process')->run();

            $this->_getSession()->addSuccess('The compilation has completed.');
        } catch (Exception $e) {
            $this->_getSession()->addError('Compilation error');
        }

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear Cache"
     * @description "Clear magento cache"
     * @confirm "Are you sure?"
     */
    public function clearMagentoCacheAction()
    {
        Mage::helper('M2ePro/Magento')->clearCache();
        $this->_getSession()->addSuccess('Magento cache was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //#############################################
}