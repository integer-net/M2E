<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $this->addTab('general', array(
            'label'     => $this->__('General'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_about_tabs_general')->toHtml(),
            'active'    => false
        ));

        foreach (Mage::helper('M2ePro/Component')->getAllowedComponents() as $component) {

            $componentBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_about_tabs_component',
                '',
                array('component'=>$component)
            );

            $this->addTab($component, array(
                'label'     => $this->__(constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE')),
                'content'   => $componentBlock->toHtml(),
                'active'    => false
            ));
        }

        return parent::_prepareLayout();
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml() . '<div id="content"></div>';
    }

    // ########################################
}
