<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Settings_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('settingsForm');
        //------------------------------

        $this->setTemplate('M2ePro/settings.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->component_ebay_mode = Mage::helper('M2ePro/Component_Ebay')->isActive();
        $this->component_amazon_mode = Mage::helper('M2ePro/Component_Amazon')->isActive();
        $this->component_buy_mode = Mage::helper('M2ePro/Component_Buy')->isActive();
        $this->component_play_mode = Mage::helper('M2ePro/Component_Play')->isActive();

        $this->component_ebay_allowed = Mage::helper('M2ePro/Component_Ebay')->isAllowed();
        $this->component_amazon_allowed = Mage::helper('M2ePro/Component_Amazon')->isAllowed();
        $this->component_buy_allowed = Mage::helper('M2ePro/Component_Buy')->isAllowed();
        $this->component_play_allowed = Mage::helper('M2ePro/Component_Play')->isAllowed();

        $this->component_group_rakuten_allowed = Mage::helper('M2ePro/Component')->isRakutenAllowed();

        $this->components_allowed_count = count(Mage::helper('M2ePro/Component')->getAllowedComponents());

        $this->component_default = Mage::helper('M2ePro/Component')->getDefaultComponent();

        $this->products_show_thumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/products/settings/','show_thumbnails'
        );
        $this->block_notices_show = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/block_notices/settings/','show'
        );
        $this->feedbacks_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/feedbacks/notification/','mode'
        );
        $this->messages_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/messages/notification/','mode'
        );
        $this->cron_notification_mode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/cron/notification/','mode'
        );
        //----------------------------

        //-------------------------------
        $url = $this->getUrl('*/*/restoreBlockNotices');
        $confirm = Mage::helper('M2ePro')->__('Are you sure?');

        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Restore All Helps'),
                                'onclick' => 'confirmSetLocation(\''.$confirm.'\', \''.$url.'\')',
                                'class' => 'restore_block_notices'
                            ) );
        $this->setChild('restore_block_notices',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    public function getComponentConstants()
    {
        $constants = array();

        $constants[] = array('EBAY', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $constants[] = array('AMAZON', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $constants[] = array('BUY', Ess_M2ePro_Helper_Component_Buy::NICK);
        $constants[] = array('PLAY', Ess_M2ePro_Helper_Component_Play::NICK);

        $constants[] = array('EBAY_TITLE', Ess_M2ePro_Helper_Component_Ebay::TITLE);
        $constants[] = array('AMAZON_TITLE', Ess_M2ePro_Helper_Component_Amazon::TITLE);
        $constants[] = array('BUY_TITLE', Ess_M2ePro_Helper_Component_Buy::TITLE);
        $constants[] = array('PLAY_TITLE', Ess_M2ePro_Helper_Component_Play::TITLE);

        return json_encode($constants);
    }
}