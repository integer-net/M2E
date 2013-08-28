<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_MapToProduct extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/listing/other/mapToProduct.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'    => 'mapToProduct_submit_button',
                                'label' => Mage::helper('M2ePro')->__('Confirm'),
                                'class' => 'mapToProduct_submit_button submit'
                            ) );
        $this->setChild('mapToProduct_submit_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'    => 'mapToProduct_advancedSearch_button',
                                'label' => Mage::helper('M2ePro')->__('Advanced Search'),
                                'class' => 'mapToProduct_advancedSearch_button submit',
                                'onclick' => '$(\'help_grid\').toggle()'
                        ) );
        $this->setChild('mapToProduct_advancedSearch_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->setChild(
            'mapToProduct_grid', $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapToProduct_grid')
        );
        //------------------------------

        parent::_beforeToHtml();
    }
}