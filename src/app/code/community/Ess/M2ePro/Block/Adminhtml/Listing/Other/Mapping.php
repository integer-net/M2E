<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_Mapping extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/listing/other/mapping.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'id'    => 'mapping_submit_button',
            'label' => Mage::helper('M2ePro')->__('Confirm'),
            'class' => 'mapping_submit_button submit'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('mapping_submit_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'mapping_advancedSearch_button',
            'label'   => Mage::helper('M2ePro')->__('Advanced Search'),
            'class'   => 'mapping_advancedSearch_button submit',
            'onclick' => '$(\'help_grid\').toggle()'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('mapping_advancedSearch_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->setChild(
            'mapping_grid',
            $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping_grid')
        );
        //------------------------------

        parent::_beforeToHtml();
    }
}