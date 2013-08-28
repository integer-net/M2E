<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_buy_listing';
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

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.Mage::helper('M2ePro')
                    ->getBackUrl('*/adminhtml_listing/index').'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_listing_other', array(
            'label'     => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_listingOther/index',
                array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY,
                    'back' => Mage::helper('M2ePro')
                        ->makeBackUrlParam('*/adminhtml_listing/index',
                        array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY)))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_template', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link buy-templates-drop-down'
        ));

        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_log/listing',
                array('back' => Mage::helper('M2ePro')->
                    makeBackUrlParam('*/adminhtml_listing/index',
                    array('tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY)),
                    'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK))).'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('search_buy_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search Items'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_buy_listing/search',
                array('back' => Mage::helper('M2ePro')->
                    makeBackUrlParam('*/adminhtml_listing/index',
                    array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY)))).'\')',
            'class'     => 'button_link search'
        ));

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) == 1) {
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'CommonHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
        }

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Listing'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_buy_listing/add',
                array('step'=>'1','clear'=>'yes')).'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    // ####################################

    public function getTemplatesButtonJavascript()
    {
        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getTemplatesButtonDropDownHtml());

        $javascript = <<<JAVASCRIPT
$$('.buy-templates-drop-down')[0].innerHTML += '{$tempDropDownHtml}';
DropDownObj = new DropDown();
DropDownObj.prepare($$('.buy-templates-drop-down')[0]);
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
        $sellingFormatUrl = $this->getUrl('*/adminhtml_template_sellingFormat/index',
            array('filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK)));

        $synchronization = Mage::helper('M2ePro')->__('Synchronization');
        $synchronizationUrl = $this->getUrl('*/adminhtml_template_synchronization/index',
            array('filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK)));

        return <<<HTML
<ul style="display: none;">
    <li href="{$sellingFormatUrl}" target="_blank">{$sellingFormat}</li>
    <li href="{$synchronizationUrl}" target="_blank">{$synchronization}</li>
</ul>
HTML;
    }

    // ####################################
}
