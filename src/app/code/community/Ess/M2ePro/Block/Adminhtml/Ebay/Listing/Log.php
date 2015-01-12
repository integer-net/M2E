<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_log';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = '';

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (isset($listingData['id'])) {

            if (!Mage::helper('M2ePro/View_Ebay_Component')->isSingleActiveComponent()) {
                $component =  Mage::helper('M2ePro/Component')->getComponentTitle($listingData['component_mode']);
                $headerText = Mage::helper('M2ePro')->__("Log For %component_name% Listing", $component);
            } else {
                $headerText = Mage::helper('M2ePro')->__("Log For Listing");
            }

            $this->_headerText = $headerText;
            $this->_headerText .= ' "'.$this->escapeHtml($listingData['title']).'"';
        } else {

            // Set template
            //------------------------------
            $this->setTemplate('M2ePro/log/grid/container.phtml');
            //------------------------------

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

        $this->addButton('show_general_log', array(
            'label'     => Mage::helper('M2ePro')->__('Show General Log'),
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_ebay_log/listing').'\')',
            'class'     => 'button_link'
        ));
        //------------------------------
    }

    // ########################################
}