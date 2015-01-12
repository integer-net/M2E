<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_NewProduct_Help extends Mage_Adminhtml_Block_Widget
{
   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateNewProductHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/template/newProduct/help.phtml');
    }

    public function getContainerId()
    {
        return 'block_notice_amazon_template_newProduct';
    }
}