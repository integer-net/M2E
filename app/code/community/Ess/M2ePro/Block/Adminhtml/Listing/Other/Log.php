<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('otherListingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_listing_other_log';
        //------------------------------

        // Set header text
        //------------------------------
        $otherListingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        if (isset($otherListingData['id'])) {

            $component = '';

            if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
                if ($otherListingData['component_mode'] == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                    $component = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
                }
                if ($otherListingData['component_mode'] == Ess_M2ePro_Helper_Component_Amazon::NICK) {
                    $component = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
                }
                if ($otherListingData['component_mode'] == Ess_M2ePro_Helper_Component_Buy::NICK) {
                    $component = ' ' . Ess_M2ePro_Helper_Component_Buy::TITLE;
                }
                if ($otherListingData['component_mode'] == Ess_M2ePro_Helper_Component_Play::NICK) {
                    $component = ' ' . Ess_M2ePro_Helper_Component_Play::TITLE;
                }
            }

            $tempTitle = Mage::helper('M2ePro/Component_'.ucfirst($otherListingData['component_mode']))
                ->getObject('Listing_Other',$otherListingData['id'])
                ->getChildObject()->getTitle();

            $this->_headerText = Mage::helper('M2ePro')->__("Log For%s 3rd Party Listing", $component);
            $this->_headerText .= ' "' . $this->escapeHtml($tempTitle) . '"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('3rd Party Listings Log');
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

            $backUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listingOther/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$backUrl.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('goto_ebay_listings', array(
            'label'     => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_listingOther/index').'\')',
            'class'     => 'button_link'
        ));

        $url = $this->getUrl(
            '*/adminhtml_logCleaning/index',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_log/listingOther'))
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

        if (isset($otherListingData['id'])) {

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
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_log_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    // ########################################
}