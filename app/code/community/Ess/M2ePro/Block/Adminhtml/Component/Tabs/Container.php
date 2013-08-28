<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container extends Ess_M2ePro_Block_Adminhtml_Component_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/component/container.phtml');
    }

    // ########################################
}