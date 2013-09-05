<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_InspectionController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    public function phpInfoAction()
    {
        phpinfo();
    }

    //#############################################
}