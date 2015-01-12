<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Other_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonListingOtherLog');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_listing_other_log';
        //------------------------------

        // Set header text
        //------------------------------
        $otherListingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (isset($otherListingData['id'])) {

            if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
                $component =  Mage::helper('M2ePro/Component')->getComponentTitle($otherListingData['component_mode']);
                $headerText = Mage::helper('M2ePro')->__("Log For %component_name% 3rd Party Listing", $component);
            } else {
                $headerText = Mage::helper('M2ePro')->__("Log For 3rd Party Listing");
            }

            $tempTitle = Mage::helper('M2ePro/Component_'.ucfirst($otherListingData['component_mode']))
                ->getObject('Listing_Other',$otherListingData['id'])
                ->getChildObject()->getTitle();

            $this->_headerText = $headerText;
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
        //------------------------------

        if (!is_null($this->getRequest()->getParam('back'))) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_common_listing_other/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
            //------------------------------
        }

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_listing_other/index');
        $this->_addButton('goto_listings_other', array(
            'label'     => Mage::helper('M2ePro')->__('3rd Party Listings'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
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

        //------------------------------
        if (isset($otherListingData['id'])) {
            $url = $this->getUrl('*/*/*');
            $this->_addButton('show_general_log', array(
                'label'     => Mage::helper('M2ePro')->__('Show General Log'),
                'onclick'   => 'setLocation(\'' . $url .'\')',
                'class'     => 'show_general_log'
            ));
        }
        //------------------------------
    }

    // ########################################

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other_log_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        $translations = json_encode(array(
            'Description' => Mage::helper('M2ePro')->__('Description')
        ));

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    M2ePro.translator.add({$translations});

    Event.observe(window, 'load', function() {
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        return $javascript . parent::_toHtml();
    }

    // ########################################
}