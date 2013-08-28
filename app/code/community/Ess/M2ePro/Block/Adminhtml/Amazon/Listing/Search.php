<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Search extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingAmazonSearch');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_listing_search';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
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

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''
                               .Mage::helper('M2ePro')->getBackUrl('*/adminhtml_amazon_listing/search').'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\''
                           .$this->getUrl('*/adminhtml_listing/index',
                                          array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_AMAZON,
                                                'back'=> Mage::helper('M2ePro')
                                                            ->makeBackUrlParam('*/adminhtml_amazon_listing/search')))
                                          .'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_templates', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link drop_down templates-drop-down'
        ));

        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'setLocation(\''
                           .$this->getUrl('*/adminhtml_log/listing',
                                          array('back'=>Mage::helper('M2ePro')
                                                            ->makeBackUrlParam('*/adminhtml_amazon_listing/search'),
                                                'filter' => base64_encode('component_mode='
                                                                          .Ess_M2ePro_Helper_Component_Amazon::NICK)))
                                          .'\')',
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

    public function _toHtml()
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
        $sellingFormatUrl = $this->getUrl('*/adminhtml_template_sellingFormat/index',
                                          array('filter' => base64_encode('component_mode='
                                                                          .Ess_M2ePro_Helper_Component_Amazon::NICK)));

        $synchronization = Mage::helper('M2ePro')->__('Synchronization Templates');
        $synchronizationUrl = $this->getUrl('*/adminhtml_template_synchronization/index',
                                            array('filter'=>base64_encode('component_mode='
                                                                          .Ess_M2ePro_Helper_Component_Amazon::NICK)));

        return <<<HTML
<ul style="display: none;">
    <li href="{$sellingFormatUrl}" target="_blank">{$sellingFormat}</li>
    <li href="{$synchronizationUrl}" target="_blank">{$synchronization}</li>
</ul>
HTML;
    }

    // ########################################
}