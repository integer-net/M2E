<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingEditForm');
        //------------------------------
    }

    protected function _prepareForm()
    {
        // Prepare action
        // -------------------
        $step = $this->getRequest()->getParam('step');

        if (is_null($step)) {
            // Edit listing mode
            $action = $this->getUrl('*/adminhtml_buy_listing/save');
        } else {
            // Add listing mode
            $action = $this->getUrl('*/adminhtml_buy_listing/add', array('step' => (int)$step));
        }
        // -------------------

        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $action,
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Add listing mode
        // -------------------
        $child = NULL;
        $step = $this->getRequest()->getParam('step');

        if ($step == 1) {
            $child = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_edit_tabs_settings');
        } else if ($step == 2) {
            $child = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_edit_tabs_channelSettings');
        } elseif ($step == 3) {
            $child = $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_edit_tabs_productsFilter');
        }

        if (!is_null($child)) {
            $this->setTemplate('M2ePro/buy/listing/add.phtml');
            $this->setChild('general',
                $this->getLayout()->createBlock('M2ePro/adminhtml_buy_listing_edit_tabs_general'));
            $this->setChild('content', $child);
        }
        // -------------------

        return parent::_beforeToHtml();
    }
}
