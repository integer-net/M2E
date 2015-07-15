<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_OtherIssues
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspectionOtherIssues');
        //------------------------------

        $this->setTemplate('M2ePro/development/inspection/otherIssues.phtml');
    }

    // ########################################

    protected function isShown()
    {
        return $this->isMagicQuotesEnabled() ||
               $this->isGdLibraryUnAvailable() ||
               $this->isZendOpcacheAvailable();
    }

    // ########################################

    public function isMagicQuotesEnabled()
    {
        return (bool)ini_get('magic_quotes_gpc');
    }

    public function isGdLibraryUnAvailable()
    {
        return !extension_loaded('gd') || !function_exists('gd_info');
    }

    public function isZendOpcacheAvailable()
    {
        return function_exists('opcache_get_status');
    }

    // ########################################
}