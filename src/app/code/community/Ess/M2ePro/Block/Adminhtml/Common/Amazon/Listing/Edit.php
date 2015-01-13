<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_listing';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $headerText = Mage::helper('M2ePro')->__(
                'Edit "%listing_title%" %component_name% Listing [Settings]',
                $this->escapeHtml($listingData['title']),
                Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE)
            );
        } else {
            $headerText = Mage::helper('M2ePro')->__(
                'Edit "%listing_title%" Listing [Settings]',
                $this->escapeHtml($listingData['title'])
            );
        }

        $this->_headerText = $headerText;
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
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_common_listing/index',
                array(
                    'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
                )
            );
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'AmazonListingSettingsHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            //------------------------------
        }

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'AmazonListingSettingsHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        $backUrl = Mage::helper('M2ePro')->getBackUrlParam('list');

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_amazon_listing/save',
            array(
                'id'   => $listingData['id'],
                'back' => $backUrl
            )
        );
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'AmazonListingSettingsHandlerObj.save_click(\'' . $url . '\')',
            'class' => 'save'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'AmazonListingSettingsHandlerObj.save_and_edit_click(\'\',\'amazonListingEditTabs\')',
            'class'     => 'save'
        ));
        //------------------------------
    }
}