<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Search extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingEbaySearch');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_search';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
        } else {
            $componentName = '';
        }

        $this->_headerText = Mage::helper('M2ePro')->__("Search%s Listings Items", $componentName);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_ebay_listing/search');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
        }

        $url = $this->getUrl('*/adminhtml_listing/index',array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY,
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing/search')
        ));
        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_templates', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link drop_down templates-drop-down'
        ));

        $url = $this->getUrl('*/adminhtml_log/listing',array(
            'back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_ebay_listing/search'),
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    // ########################################

    protected function _toHtml()
    {
        return $this->getTemplatesButtonJavascript() . parent::_toHtml();
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_search_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    // ########################################

    protected function getTemplatesButtonJavascript()
    {
        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getGotoTemplatesDropDownHtml());

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        $$('.templates-drop-down')[0].innerHTML += '{$tempDropDownHtml}';
        DropDownObj = new DropDown();
        DropDownObj.prepare($$('.templates-drop-down')[0]);
    });

</script>
JAVASCRIPT;

        return $javascriptsMain;
    }

    protected function getGotoTemplatesDropDownHtml()
    {
        $sellingFormat = Mage::helper('M2ePro')->__('Selling Format Templates');
        $sellingFormatUrl = $this->getUrl('*/adminhtml_template_sellingFormat/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        $description = Mage::helper('M2ePro')->__('Description Templates');
        $descriptionUrl = $this->getUrl('*/adminhtml_template_description/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        $general = Mage::helper('M2ePro')->__('General Templates');
        $generalUrl = $this->getUrl('*/adminhtml_template_general/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        $synchronization = Mage::helper('M2ePro')->__('Synchronization Templates');
        $synchronizationUrl = $this->getUrl('*/adminhtml_template_synchronization/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        return <<<HTML
<ul style="display: none;">
    <li href="{$sellingFormatUrl}" target="_blank">{$sellingFormat}</li>
    <li href="{$descriptionUrl}" target="_blank">{$description}</li>
    <li href="{$generalUrl}" target="_blank">{$general}</li>
    <li href="{$synchronizationUrl}" target="_blank">{$synchronization}</li>
</ul>
HTML;
    }

    // ########################################
}