<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listing_log';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        if (isset($listingData['id'])) {

            $component = '';

            if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
                if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                    $component = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
                }
                if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Amazon::NICK) {
                    $component = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
                }
                if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Buy::NICK) {
                    $component = ' ' . Ess_M2ePro_Helper_Component_Buy::TITLE;
                }
                if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Play::NICK) {
                    $component = ' ' . Ess_M2ePro_Helper_Component_Play::TITLE;
                }
            }

            $this->_headerText = Mage::helper('M2ePro')->__("Log For%s Listing", $component);
            $this->_headerText .= ' "'.$this->escapeHtml($listingData['title']).'"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Listings Log');
        }
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

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_listing/index').'\')',
            'class'     => 'button_link'
        ));

        if (isset($listingData['id'])) {

            $controller = '';
            if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $controller = 'adminhtml_ebay_listing';
            }
            if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Amazon::NICK) {
                $controller = 'adminhtml_amazon_listing';
            }
            if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Buy::NICK) {
                $controller = 'adminhtml_buy_listing';
            }
            if ($listingData['component_mode'] == Ess_M2ePro_Helper_Component_Play::NICK) {
                $controller = 'adminhtml_play_listing';
            }

            $url = $this->getUrl("*/{$controller}/edit", array('id' => $listingData['id']));
            $this->_addButton('goto_listing_settings', array(
                'label'     => Mage::helper('M2ePro')->__('Listing Settings'),
                'onclick'   => 'setLocation(\'' .$url.'\')',
                'class'     => 'button_link'
            ));

            $url = $this->getUrl("*/{$controller}/view", array('id' => $listingData['id']));
            $this->_addButton('goto_listing_items', array(
                'label'     => Mage::helper('M2ePro')->__('Listing Items'),
                'onclick'   => 'setLocation(\'' .$url.'\')',
                'class'     => 'button_link'
            ));
        }

        $url = $this->getUrl(
            '*/adminhtml_logCleaning/index',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_log/listing'))
        );
        $this->_addButton('goto_logs_cleaning', array(
            'label'     => Mage::helper('M2ePro')->__('Clearing'),
            'onclick'   => 'setLocation(\'' .$url.'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        if (isset($listingData['id'])) {

            $this->_addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' .$this->getUrl('*/*/*').'\')',
                'class'     => 'show_general_log'
            ));
        }
        //------------------------------
    }

    // ########################################

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_log_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    // ########################################
}