<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Common_Controller extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function addMessages(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        !$wizardHelper->isFinished(Ess_M2ePro_Helper_View_Common::WIZARD_INSTALLATION_NICK) ||
        Mage::helper('M2ePro/Magento')->isDeveloper() ||
        $this->addCronErrorMessage($controller);
    }

    // ########################################

    private function addCronErrorMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        if (Mage::getModel('M2ePro/Cron')->isShowError()) {

            $url = 'http://support.m2epro.com/knowledgebase/articles/';
            $url .= '162927-why-cron-job-is-required-for-amazon-and-rakuten-co';
            $startLinkArticle = '<a href="'.$url.'" target="_blank">';
            $endLink = '</a>';

            $message = 'Attention! The Cron job is not running at the moment. ';
            $message .= 'The Amazon/Rakuten.com/Play.com Integration DOES NOT WORK ';
            $message .= 'without automatic task scheduled by cron job. <br/>You can check this %sarticle%s ';
            $message .= 'to get better idea why cron job is mandatory.';
            $message = Mage::helper('M2ePro')->__($message, $startLinkArticle, $endLink);

            $controller->getSession()->addError($message);
            return true;
        }

        return false;
    }

    // ########################################
}