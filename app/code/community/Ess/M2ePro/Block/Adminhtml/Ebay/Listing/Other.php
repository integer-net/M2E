<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOther');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_other';
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

        $url = $this->getUrl('*/adminhtml_ebay_listingOtherSynchronization/edit',array(
            'back'=> Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listingOther/index',array(
                'tab'=>Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
            ))
        ));
        $this->_addButton('goto_listing_other_synchronization', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
            'class'     => 'button_link'
        ));

        //------------------------------
    }

    // ####################################
}