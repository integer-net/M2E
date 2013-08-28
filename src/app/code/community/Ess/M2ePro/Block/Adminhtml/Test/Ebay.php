<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Test_Ebay extends Ess_M2ePro_Block_Adminhtml_Left
{
    protected function _beforeToHtml()
    {
        $this->setChild('left', $this->getLayout()->createBlock('M2ePro/adminhtml_test_ebay_tabs'));

        return parent::_beforeToHtml();
    }

    public function getContentElementId()
    {
        return 'm2epro_ebay_content';
    }

    public function getLeftElementId()
    {
        return 'm2epro_ebay_left';
    }
}