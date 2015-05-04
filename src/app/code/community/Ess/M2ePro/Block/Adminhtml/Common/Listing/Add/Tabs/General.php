<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Add_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    protected $sessionKey = 'listing_create';

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingAddTabsGeneral');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        isset($sessionData['title'])        && $this->setData('title',$sessionData['title']);
        isset($sessionData['account_id'])   && $this->setData('account_id',$sessionData['account_id']);
        isset($sessionData['store_id'])     && $this->setData('store_id',$sessionData['store_id']);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => 'Add',
                'onclick' => '',
                'id' => 'add_account_button',
            ) );

        $this->setChild('add_account_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->setChild(
            'store_switcher',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_storeSwitcher', '', array('id'=>'store_id','selected' => $this->getData('store_id'))
            )
        );
        //------------------------------

        return parent::_beforeToHtml();
    }
}