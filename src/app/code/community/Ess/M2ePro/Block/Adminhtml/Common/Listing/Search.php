<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Search extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingSearch');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_search';
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
        //------------------------------

        if (!is_null($this->getRequest()->getParam('back'))) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_listing/search');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            //------------------------------
        }

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_listing/search');

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing/index', array('back' => $backUrl));
        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('goto_templates', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link drop_down templates-drop-down'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_log/listing');
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'window.open(\''.$url.'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
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
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    // ########################################

    protected function getTemplatesButtonJavascript()
    {
        $data = array(
            'target_css_class' => 'templates-drop-down',
            'items'            => $this->getTemplatesButtonDropDownItems()
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    protected function getTemplatesButtonDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_template_sellingFormat/index');
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Selling Format Templates'),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_template_synchronization/index');
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Synchronization Templates'),
            'target' => '_blank'
        );
        //------------------------------

        return $items;
    }

    // ########################################
}