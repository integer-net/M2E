<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Ebay_Controller extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function addMessages(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        !Mage::helper('M2ePro/Module_Cron')->isReadyToRun() ||
        !Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(1,true) ||
        Mage::helper('M2ePro/Magento')->isDeveloper() ||
        $this->addCronNotificationMessage($controller);

        if (Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished()) {

            $feedbacksNotificationMode = Mage::helper('M2ePro/Module')->getConfig()
                                        ->getGroupValue('/view/ebay/feedbacks/notification/', 'mode');

            !$feedbacksNotificationMode ||
            !$this->haveNewNegativeFeedbacks() ||
            $this->addFeedbackNotificationMessage($controller);

            $this->addTokenExpirationDateNotificationMessage($controller);
            $this->addMarketplacesCategoriesVersionNotificationMessage($controller);
        }
    }

    // ########################################

    private function addCronNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $url = 'http://support.m2epro.com/knowledgebase/articles/';
        $url .= '334421-why-automatic-synchronization-is-needed-for-ebay-i';

        $startLinkArticle = '<a href="'.$url.'" target="_blank">';
        $endLink = '</a>';

        $message = 'Attention! AUTOMATIC synchronization is not running at the moment.';
        $message .= '<br/>Please check this %sarticle%s to learn why it is required.';
        $message = $this->__(
            $message, $startLinkArticle, $endLink
        );

        $controller->getSession()->addNotice($message);
    }

    private function addFeedbackNotificationMessage(Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $startLink = '<a href="'.$controller->getUrl('*/adminhtml_ebay_feedback/index').'" target="_blank">';
        $endLink = '</a>';

        // ->__('New buyer negative feedback was received. Go to the %sfeedback page%s.')
        $message = 'New buyer negative feedback was received. Go to the %sfeedback page%s.';
        $message = $this->__($message, $startLink, $endLink);

        $controller->getSession()->addNotice($message);
    }

    //#############################################

    private function addTokenExpirationDateNotificationMessage(
                            Ess_M2ePro_Controller_Adminhtml_BaseController $controller)
    {
        $tokenExpirationMessages = Mage::helper('M2ePro/Data_Cache')->getValue(
            'ebay_accounts_token_expiration_messages'
        );

        if ($tokenExpirationMessages === false) {

            $tokenExpirationMessages = array();

            /* @var $tempCollection Mage_Core_Model_Mysql4_Collection_Abstract */
            $tempCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

            $tempCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $tempCollection->getSelect()->columns(array('id','title'));
            $tempCollection->getSelect()->columns('token_expired_date','second_table');

            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

            foreach ($tempCollection->getData() as $accountData) {

                $tokenExpirationTimeStamp = strtotime($accountData['token_expired_date']);

                if ($tokenExpirationTimeStamp < $currentTimeStamp) {
                    $tempMessage = $this->__(
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
                    $tempMessage = $this->__(
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
    }

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

        $controller->getSession()->addNotice($this->__(
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

    private function haveNewNegativeFeedbacks()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();
        $configGroup = '/view/ebay/feedbacks/notification/';

        $lastCheckDate = $config->getGroupValue($configGroup, 'last_check');

        if (is_null($lastCheckDate)) {
            $config->setGroupValue($configGroup, 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $collection = Mage::getModel('M2ePro/Ebay_Feedback')->getCollection()
                            ->addFieldToFilter('buyer_feedback_date', array('gt' => $lastCheckDate))
                            ->addFieldToFilter('buyer_feedback_type', Ess_M2ePro_Model_Ebay_Feedback::TYPE_NEGATIVE);

        if ($collection->getSize() > 0) {
            $config->setGroupValue($configGroup, 'last_check', Mage::helper('M2ePro')->getCurrentGmtDate());
            return true;
        }

        return false;
    }

    //#############################################
}