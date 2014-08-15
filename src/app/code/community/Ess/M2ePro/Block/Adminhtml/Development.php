<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development extends Mage_Adminhtml_Block_Widget_Form_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentContainer');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'development';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_View_Development::TITLE);
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
        $url = Mage::helper('M2ePro/Module_Support')->getMainWebsiteUrl();
        $this->_addButton('goto_m2epro', array(
            'label'     => Mage::helper('M2ePro')->__('Main Website'),
            'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
            'class'     => 'button_development'
        ));
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/Module_Support')->getMainSupportUrl();
        $this->_addButton('goto_support', array(
            'label'     => Mage::helper('M2ePro')->__('Support'),
            'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
            'class'     => 'button_development'
        ));
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/Module_Support')->getMagentoConnectUrl();
        $this->_addButton('goto_magento_connect', array(
            'label'     => Mage::helper('M2ePro')->__('Magento Connect'),
            'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
            'class'     => 'button_development'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('goto_docs', array(
            'label' => Mage::helper('M2ePro')->__('Documentation'),
            'class' => 'button_link drop_down button_documentation'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('goto_video_tutorials', array(
            'label' => Mage::helper('M2ePro')->__('Video Tutorials'),
            'class' => 'button_link drop_down button_video_tutorial'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    // ----------------------------------------

    public function getHeaderHtml()
    {
        $data = array(
            'target_css_class' => 'button_documentation',
            'style' => 'max-height: 120px; overflow: auto; width: 150px;',
            'items' => $this->getDocumentationDropDownItems()
        );

        $dropDownBlockDocumentation = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown', '', $data);

        $data = array(
            'target_css_class' => 'button_video_tutorial',
            'style' => 'max-height: 120px; overflow: auto; width: 150px;',
            'items' => $this->getVideoTutorialDropDownItems()
        );

        $dropDownBlockVideoTutorial = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown', '', $data);

        return parent::getHeaderHtml()
        . $dropDownBlockDocumentation->toHtml()
        . $dropDownBlockVideoTutorial->toHtml();
    }

    // ########################################

    private function getVideoTutorialDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = Mage::helper('M2ePro/View_Ebay')->getVideoTutorialsUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/View_Common')->getVideoTutorialsUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel(),
            'target' =>'_blank'
        );
        //------------------------------

        return $items;
    }

    // ----------------------------------------

    private function getDocumentationDropDownItems()
    {
        $items = array();

        //------------------------------
        $url = Mage::helper('M2ePro/View_Ebay')->getDocumentationUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
            'target' => '_blank'
        );
        //------------------------------

        //------------------------------
        $url = Mage::helper('M2ePro/View_Common')->getDocumentationUrl();
        $items[] = array(
            'url' => $url,
            'label' => Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel(),
            'target' =>'_blank'
        );
        //------------------------------

        return $items;
    }

    // ########################################
}