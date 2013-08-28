<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Support_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('support');
        $this->setDestElementId('support_tab_container');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $isFromError = $this->getRequest()->getParam('error') === 'true';

        $this->addTab('results', array(
            'label'     => $this->__('Search Results'),
            'content'   => '',
            'active'    => !$isFromError,
        ));

        $this->addTab('documentation', array(
            'label'     => $this->__('Documentation'),
            'url'       => $this->getUrl('*/adminhtml_support/documentation'),
            'active'    => false,
            'class'     => 'ajax',
        ));

        $this->addTab('articles', array(
            'label'     => $this->__('Knowledge Base'),
            'url'       => $this->getUrl('*/adminhtml_support/knowledgeBase'),
            'active'    => false,
            'class'     => 'ajax',
        ));

        $this->addTab('support_form', array(
            'label'     => $this->__('Contact Support'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_support_contactForm')->toHtml(),
            'active'    => $isFromError,
        ));

        return parent::_prepareLayout();
    }

    // ########################################
}