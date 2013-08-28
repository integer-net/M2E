<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';
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

            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index');

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
        }

        $url = $this->getUrl('*/adminhtml_listingOther/index',array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY,
            'back'=> Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
            ))
        ));
        $this->_addButton('goto_listing_other', array(
            'label'     => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_template', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link ebay-templates-drop-down'
        ));

        $url = $this->getUrl('*/adminhtml_log/listing', array(
            'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index',array(
                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
            )),
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));

        $url = $this->getUrl('*/adminhtml_ebay_listing/search', array(
            'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
            ))
        ));
        $this->_addButton('search_ebay_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search Items'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link search'
        ));

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) == 1) {
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'CommonHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
        }

        $url = $this->getUrl('*/adminhtml_ebay_listing/add',array('step'=>'1','clear'=>'yes'));
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Listing'),
            'onclick'   => 'setLocation(\'' .$url.'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    // ####################################

    public function getTemplatesButtonJavascript()
    {
        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getTemplatesButtonDropDownHtml());

        $javascript = <<<JAVASCRIPT
$$('.ebay-templates-drop-down')[0].innerHTML += '{$tempDropDownHtml}';
DropDownObj = new DropDown();
DropDownObj.prepare($$('.ebay-templates-drop-down')[0]);
JAVASCRIPT;

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $javascript = "Event.observe(window, 'load', function() { {$javascript} });";
        }

        return <<<JAVASCRIPT
<script type="text/javascript">
    {$javascript}
</script>
JAVASCRIPT;
    }

    protected function getTemplatesButtonDropDownHtml()
    {
        $sellingFormat = Mage::helper('M2ePro')->__('Selling Format');
        $sellingFormatUrl = $this->getUrl('*/adminhtml_template_sellingFormat/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        $description = Mage::helper('M2ePro')->__('Description');
        $descriptionUrl = $this->getUrl('*/adminhtml_template_description/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        $general = Mage::helper('M2ePro')->__('General');
        $generalUrl = $this->getUrl('*/adminhtml_template_general/index', array(
            'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Ebay::NICK)
        ));

        $synchronization = Mage::helper('M2ePro')->__('Synchronization');
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

    // ####################################
}