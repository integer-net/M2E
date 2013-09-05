<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Ebay_Controller extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function addMessages(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        !$wizardHelper->isFinished(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK) ||
        Mage::helper('M2ePro/Magento')->isDeveloper() ||
        $this->addCronNotificationMessage($controller);

        if ($wizardHelper->isFinished(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            $this->addFeedbackNotificationMessage($controller);
            $this->addTokenExpirationDateNotificationMessage($controller);
        }
    }

    // ########################################

    private function addCronNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        if (Mage::getModel('M2ePro/Cron')->isShowNotification()) {

            $allowedInactiveHours = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/view/ebay/cron/notification/', 'max_inactive_hours'
            );

            $url = 'http://support.m2epro.com/knowledgebase/articles/42054-how-to-set-up-cron-job-for-m2e-pro-';
            $startLinkArticle = '<a href="'.$url.'" target="_blank">';
            $endLink = '</a>';

            $message = 'Attention! Last eBay AUTOMATIC synchronization was performed by cron ';
            $message .= 'more than %s hours ago. You should set up cron job, otherwise no automatic synchronization ';
            $message .= 'will be performed. <br/>You can check this %sarticle%s to get how to set cron job.';
            $message = Mage::helper('M2ePro')->__(
                $message, $allowedInactiveHours, $startLinkArticle, $endLink
            );

            $controller->getSession()->addNotice($message);
            return true;
        }

        return false;
    }

    //-----------------------------------------

    private function addFeedbackNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        if (Mage::getModel('M2ePro/Ebay_Feedback')->haveNew(true)) {

            $startLink = '<a href="'.$controller->getUrl('*/adminhtml_ebay_feedback/index').'" target="_blank">';
            $endLink = '</a>';

            // ->__('New buyer negative feedback was received. Go to the %sfeedback page%s.')
            $message = 'New buyer negative feedback was received. Go to the %sfeedback page%s.';
            $message = Mage::helper('M2ePro')->__($message, $startLink, $endLink);

            $controller->getSession()->addNotice($message);
            return true;
        }

        return false;
    }

    private function addTokenExpirationDateNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $tokenExpirationMessages = Mage::helper('M2ePro/Data_Cache')->getValue('ebay_accounts_token_expiration_messages');

        if (!$tokenExpirationMessages) {

            $tokenExpirationMessages = array();

            /* @var $tempCollection Mage_Core_Model_Resource_Db_Collection_Abstract */
            $tempCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

            $tempCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $tempCollection->getSelect()->columns(array('id','title'));
            $tempCollection->getSelect()->columns('token_expired_date','second_table');

            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

            foreach ($tempCollection->getData() as $accountData) {

                $tokenExpirationTimeStamp = strtotime($accountData['token_expired_date']);

                if ($tokenExpirationTimeStamp < $currentTimeStamp) {
                    $tempMessage = Mage::helper('M2ePro')->__(
                        'The token for "%s" eBay Account has been expired.
                        <br>
                        Please, go to %s > Configuration > eBay Account > <a href="%s" target="_blank">General TAB</a>,
                        click on the Get Token button. (You will be redirected to the eBay website.)
                        Sign-in and press I Agree on eBay page.
                        Do not forget to press Save button after returning back to Magento',
                        Mage::helper('M2ePro')->escapeHtml($accountData['title']),
                        Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                        $controller->getUrl('*/adminhtml_ebay_account/edit', array('id' => $accountData['id']))
                    );
                    $tokenExpirationMessages[] = array(
                        'type' => 'error',
                        'message' => $tempMessage
                    );

                    continue;
                }

                if (($currentTimeStamp + 60*60*24*10) >= $tokenExpirationTimeStamp) {
                    $tempMessage = Mage::helper('M2ePro')->__(
                    'Attention! The token for "%s" eBay Account will be expired soon ( %s ).
                    <br>
                    Please, go to %s > Configuration > eBay Account > <a href="%s" target="_blank">General TAB</a>,
                    click on the Get Token button. (You will be redirected to the eBay website.)
                    Sign-in and press I Agree on eBay page.
                    Do not forget to press Save button after returning back to Magento',
                    Mage::helper('M2ePro')->escapeHtml($accountData['title']),
                    Mage::app()->getLocale()->date(strtotime($accountData['token_expired_date']))->toString($format),
                    Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                    $controller->getUrl('*/adminhtml_ebay_account/edit', array('id' => $accountData['id']))
                    );

                    $tokenExpirationMessages[] = array(
                        'type' => 'notice',
                        'message' => $tempMessage
                    );

                    continue;
                }
            }

            Mage::helper('M2ePro/Data_Cache')->setValue('ebay_accounts_token_expiration_messages',
                                                         $tokenExpirationMessages,
                                                         array('account','ebay'),
                                                         60*60*24);
        }

        foreach ($tokenExpirationMessages as $messageData) {
            $method = 'add' . ucfirst($messageData['type']);
            $controller->getSession()->$method($messageData['message']);
        }

        return true;
    }

    //#############################################
}