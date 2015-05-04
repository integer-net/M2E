<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_StepTwo extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct($attributes)
    {
        parent::__construct();

        $this->setData($attributes);

        // Initialization block
        //------------------------------
        $this->setId($this->getData('component').'ListingAddStepTwo');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_'.$this->getData('component').'_listing';
        $this->_mode = 'add';
        //------------------------------

        // Set header text
        //------------------------------
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $titleConst = constant('Ess_M2ePro_Helper_Component_'.ucfirst($this->getData('component')).'::TITLE');
            $componentName =  Mage::helper('M2ePro')->__($titleConst);
            $headerText = Mage::helper('M2ePro')
                ->__("Creating A New %component_name% M2E Pro Listing", $componentName);
        } else {
            $headerText = Mage::helper('M2ePro')->__("Creating A New M2E Pro Listing");
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

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing_create/index', array(
            '_current' => true,
            'step' => '1',
            'component' => $this->getData('component')
        ));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Previous Step'),
            'onclick'   => ucfirst($this->getData('component')) .
                                'ListingChannelSettingsHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing_create/index', array(
            '_current' => true
        ));
        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next Step'),
            'onclick'   => ucfirst($this->getData('component')) .
                                'ListingChannelSettingsHandlerObj.save_click(\'' . $url . '\')',
            'class'     => 'next'
        ));
        //------------------------------
    }
}