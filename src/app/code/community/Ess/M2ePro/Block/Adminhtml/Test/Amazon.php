<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Test_Amazon extends Ess_M2ePro_Block_Adminhtml_Left
{
    protected function _beforeToHtml()
    {
        $this->setChild('left', $this->getLayout()->createBlock('M2ePro/adminhtml_test_amazon_tabs'));

        return parent::_beforeToHtml();
    }

    public function getContentElementId()
    {
        return 'm2epro_amazon_content';
    }

    public function getLeftElementId()
    {
        return 'm2epro_amazon_left';
    }
}