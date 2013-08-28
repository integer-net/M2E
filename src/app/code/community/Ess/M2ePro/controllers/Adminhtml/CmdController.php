<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_CmdController extends Ess_M2ePro_Controller_Adminhtml_Cmd_DevelopmentController
{
    //#############################################

    /**
     * @title "Test"
     * @description "Command for quick development"
     * @group "Development"
     * @new_line
     */
    public function testAction()
    {
        $this->printBack();
    }

    //#############################################
}