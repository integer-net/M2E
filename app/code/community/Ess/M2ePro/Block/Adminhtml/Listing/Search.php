<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Search extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingSearch');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listing_search';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Search Listings Items');
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

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/search');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $url = $this->getUrl(
            '*/adminhtml_listing/index',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/search'))
        );
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

        $url = $this->getUrl(
            '*/adminhtml_log/listing',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/search'))
        );
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
        $sellingFormatTemplate = Mage::helper('M2ePro')->__('Selling Format Templates');
        $descriptionTemplate = Mage::helper('M2ePro')->__('Description Templates');
        $generalTemplate = Mage::helper('M2ePro')->__('General Templates');
        $synchronizationTemplate = Mage::helper('M2ePro')->__('Synchronization Templates');

        $dropDownHtml = "<li href=\"{$this->getUrl('*/adminhtml_template_sellingFormat/index')}\" target=\"_blank\">
                            {$sellingFormatTemplate}
                         </li>";

        Mage::helper('M2ePro/Component_Ebay')->isEnabled() &&
            $dropDownHtml .= "<li href=\"{$this->getUrl('*/adminhtml_template_description/index')}\" target=\"_blank\">
                                 {$descriptionTemplate}
                              </li>
                              <li href=\"{$this->getUrl('*/adminhtml_template_general/index')}\" target=\"_blank\">
                                 {$generalTemplate}
                              </li>";

        $dropDownHtml .= "<li href=\"{$this->getUrl('*/adminhtml_template_synchronization/index')}\" target=\"_blank\">
                            {$synchronizationTemplate}
                          </li>";

        return '<ul style="display: none;">' .
                   $dropDownHtml .
               '</ul>';
    }

    // ########################################
}