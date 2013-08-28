<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Translation_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('translationEditTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Languages'));
        $this->setDestElementId('text_grid');
    }

    protected function _beforeToHtml()
    {
        $languages = Mage::getModel('M2ePro/Translation_Language')->getCollection()->getItems();
        foreach ($languages as $language) {
            $this->addTab($language->getData('code'), array(
                'label' => $language->getData('title'),
                'title' => $language->getData('title'),
                'url' => $this->getUrl('*/adminhtml_translation/index/', array('language' => $language->getCode()))
            ));
        }

        $this->setActiveTab($this->getData('active_language'));

        return parent::_beforeToHtml();
    }
}