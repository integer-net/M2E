<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Common_Controller extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function addMessages(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        !Mage::helper('M2ePro/Module_Cron')->isReadyToRun() ||
        !Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1,true) ||
        Mage::helper('M2ePro/Magento')->isDeveloper() ||
        $this->addCronErrorMessage($controller);
    }

    // ########################################

    private function addCronErrorMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $url = 'http://support.m2epro.com/knowledgebase/articles/';
        $url .= '162927-why-cron-job-is-required-for-amazon-and-rakuten-co';

        $startLinkArticle = '<a href="'.$url.'" target="_blank">';
        $endLink = '</a>';

        $message = 'Attention! AUTOMATIC synchronization is not running at the moment.';
        $message .= '<br/>Please check this %sarticle%s to learn why it is required.';
        $message = $this->__($message, $startLinkArticle, $endLink);

        $controller->getSession()->addError($message);
    }

    // ########################################
}