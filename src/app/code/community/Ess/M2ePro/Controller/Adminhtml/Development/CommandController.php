<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Development_CommandController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/View_Development')->getPageRoute());
    }

    //#############################################
}