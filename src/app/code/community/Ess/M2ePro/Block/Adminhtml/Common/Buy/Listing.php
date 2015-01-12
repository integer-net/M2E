<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_listing';
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
        //------------------------------

        //------------------------------

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam(
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY
            )
        );

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_listing_other/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_BUY,
                'back' => $backUrl
            )
        );
        $this->_addButton('goto_listing_other', array(
            'label'     => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('goto_template', array(
            'label'     => Mage::helper('M2ePro')->__('Templates'),
            'onclick'   => '',
            'class'     => 'button_link buy-templates-drop-down'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_log/listing',
            array(
                'filter' => base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK)
            )
        );
        $this->_addButton('view_log', array(
            'label'     => Mage::helper('M2ePro')->__('View Log'),
            'onclick'   => 'window.open(\''.$url.'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_buy_listing/search', array('back' => $backUrl));
        $this->_addButton('search_buy_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search Items'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'button_link search'
        ));
        //------------------------------

        //------------------------------
        if (Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'CommonHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
        }
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_buy_listing/add', array('step' => '1','clear'=>'yes'));
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
        $data = array(
            'target_css_class' => 'buy-templates-drop-down',
            'items'            => $this->getTemplatesButtonDropDownItems()
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    protected function getTemplatesButtonDropDownItems()
    {
        $items = array();

        $filter = base64_encode('component_mode=' . Ess_M2ePro_Helper_Component_Buy::NICK);

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_template_sellingFormat/index',
            array(
                'filter' => $filter
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Selling Format Templates'),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_template_synchronization/index',
            array(
                'filter' => $filter
            )
        );
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro')->__('Synchronization Templates'),
            'target' => '_blank'
        );
        //------------------------------

        return $items;
    }

    // ####################################
}
