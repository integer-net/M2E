<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Common_Controller extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function addMessages(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        if (Mage::helper('M2ePro/Module_Cron')->isReadyToRun() &&
            Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1,true) &&
            !Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {

            $this->addCronErrorMessage($controller);
        }
    }

    // ########################################

    private function addCronErrorMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $url = 'http://support.m2epro.com/knowledgebase/articles/';
        $url .= '162927-why-cron-job-is-required-for-amazon-and-rakuten-co';

        // M2ePro_TRANSLATIONS
        // Attention! AUTOMATIC synchronization is not running at the moment.<br/>Please check this <a href="%url% target="_blank">article</a> to learn why it is required.
        $message = 'Attention! AUTOMATIC synchronization is not running at the moment.';
        $message .= '<br/>Please check this <a href="%url% target="_blank">article</a> ';
        $message .= 'to learn why it is required.';
        $message = Mage::helper('M2ePro')->__($message, $url);

        $controller->getSession()->addError($message);
    }

    // ########################################
}