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
            $this->addMarketplacesCategoriesVersionNotificationMessage($controller);
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

    private function addTokenExpirationDateNotificationMessage(
                            Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $tokenExpirationMessages = Mage::helper('M2ePro/Data_Cache')->getValue(
            'ebay_accounts_token_expiration_messages'
        );

        if ($tokenExpirationMessages === false) {

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

    private function addMarketplacesCategoriesVersionNotificationMessage(
                            Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $outdatedMarketplaces = Mage::helper('M2ePro/Data_Cache')->getValue('outdated_marketplaces');

        if ($outdatedMarketplaces === false) {
            $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
            $dictionaryTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

            $rows = $readConn->select()->from($dictionaryTable,'marketplace_id')
                ->where('client_categories_version IS NOT NULL')
                ->where('server_categories_version IS NOT NULL')
                ->where('client_categories_version < server_categories_version')
                ->query();

            $ids = array();
            foreach ($rows as $row) {
                $ids[] = $row['marketplace_id'];
            }

            $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
                ->addFieldToFilter('id',array('in' => $ids));

            $outdatedMarketplaces = array();
            /* @var $marketplace Ess_M2ePro_Model_Marketplace */
            foreach ($marketplacesCollection as $marketplace) {
                $outdatedMarketplaces[] = $marketplace->getTitle();
            }

            Mage::helper('M2ePro/Data_Cache')->setValue('outdated_marketplaces',
                                                         $outdatedMarketplaces,
                                                         array('ebay','marketplace'),
                                                         60*60*24);
        }

        if (count($outdatedMarketplaces) <= 0) {
            return;
        }

        $message = '%s data was changed on eBay. You need to synchronize it the extension works properly.
                    Please, go to %s > Configuration >
                    <a href="%s" target="_blank">eBay Sites</a> and click the Save And Update button.';

        $controller->getSession()->addNotice(Mage::helper('M2ePro')->__(
            $message,
            implode(', ',$outdatedMarketplaces),
            Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
            $controller->getUrl(
                '*/adminhtml_ebay_marketplace',
                array('tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Tabs::TAB_ID_MARKETPLACE)
            )
        ));
    }

    //#############################################
}