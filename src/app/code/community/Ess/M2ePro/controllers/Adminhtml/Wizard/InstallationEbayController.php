<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_InstallationEbayController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_WizardController
{
    //#############################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK;
    }

    //#############################################

    public function installationAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isFinished($this->getNick()) ||
            $wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/index');
        }

        if (!$wizardHelper->getStep($this->getNick())) {
            $wizardHelper->setStep(
                $this->getNick(),
                $wizardHelper->getWizard($this->getNick())->getFirstStep()
            );
        }

        $currentStep = $wizardHelper->getStep($this->getNick());

        $this->_forward($currentStep);
    }

    //#############################################

    private function renderSimpleStep()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $currentStep = $wizardHelper->getStep($this->getNick());

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('installation_'.$currentStep,$this->getNick()))
            ->renderLayout();
    }

    //#############################################

    public function wizardTutorialAction()
    {
        return $this->renderSimpleStep();
    }

    public function licenseAction()
    {
        return $this->renderSimpleStep();
    }

    public function modeConfirmationAction()
    {
        return $this->renderSimpleStep();
    }

    public function accountAction()
    {
        return $this->renderSimpleStep();
    }

    //#############################################

    public function listingTutorialAction()
    {
        return $this->renderSimpleStep();
    }

    public function listingAccountAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create',array('step' => 1,'wizard' => true,'clear' => true));
    }

    public function listingGeneralAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create',array('step' => 2, 'wizard' => true));
    }

    public function listingSellingAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create',array('step' => 3, 'wizard' => true));
    }

    public function listingSynchronizationAction()
    {
        return $this->_redirect('*/adminhtml_ebay_listing_create',array('step' => 4, 'wizard' => true));
    }

    //#############################################

    public function productTutorialAction()
    {
        return $this->renderSimpleStep();
    }

    public function sourceModeAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_productAdd/sourceMode',
            array(
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true
            )
        );
    }

    public function productSelectionAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        $productAddSessionData = Mage::helper('M2ePro/Data_Session')->getValue('ebay_listing_product_add');
        $source = isset($productAddSessionData['source']) ? $productAddSessionData['source'] : NULL;

        Mage::helper('M2ePro/Data_Session')->setValue('ebay_listing_product_add', $productAddSessionData);
        return $this->_redirect(
            '*/adminhtml_ebay_listing_productAdd',
            array(
                'clear' => true,
                'step'  => 1,
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true,
                'source' => $source
            )
        );
    }

    public function productSettingsAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_productAdd',
            array(
                'step' => 2,
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true,
            )
        );
    }

    //#############################################

    public function categoryStepOneAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_categorySettings',
            array(
                'step' => 1,
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true,
            )
        );
    }

    public function categoryStepTwoAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_categorySettings',
            array(
                'step' => 2,
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true,
            )
        );
    }

    public function categoryStepThreeAction()
    {
        $listingId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing')->getLastItem()->getId();

        return $this->_redirect(
            '*/adminhtml_ebay_listing_categorySettings',
            array(
                'step' => 3,
                'wizard' => true,
                'listing_id' => $listingId,
                'listing_creation' => true,
            )
        );
    }

    //#############################################

    public function beforeTokenAction()
    {
        // Get and save session id
        //-------------------------------

        $keys = array(
            'email',
            'firstname',
            'lastname',
            'country',
            'city',
            'postal_code',
        );

        $post = $this->getRequest()->getPost();
        unset($post['form_key']);
        foreach ($keys as $key) {
            (!isset($post[$key]) || !$post[$key]) && $post[$key] = 'undefined';
        }

        $registry = Mage::getModel('M2ePro/Registry')->load('wizard_license_form_data', 'key');
        $registry->setData('key', 'wizard_license_form_data');
        $registry->setData('value', json_encode($post));
        $registry->save();

        if (!Mage::helper('M2ePro/Module_License')->getKey()) {

            $licenseResult = Mage::helper('M2ePro/Module_License')->obtainRecord(
                $post['email'],
                $post['firstname'], $post['lastname'],
                $post['country'], $post['city'], $post['postal_code']
            );

            if (!$licenseResult) {
                return $this->getResponse()->setBody(json_encode(array(
                    'url' => null
                )));
            }
        }

        $accountMode = $this->getRequest()->getParam('account_mode');

        try {
            $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')->processVirtual(
                    'account','get','authUrl',
                    array('back_url'=>$this->getUrl('*/*/afterToken', array('mode' => $accountMode))),
                    NULL,NULL,NULL,$accountMode
            );
        } catch (Exception $exception) {
            return $this->getResponse()->setBody(json_encode(array(
                'url' => null
            )));
        }

        if (!$response || !isset($response['url'],$response['session_id'])) {
            return $this->getResponse()->setBody(json_encode(array(
                'url' => null
            )));
        }

        Mage::helper('M2ePro/Data_Session')->setValue('token_session_id', $response['session_id']);

        return $this->getResponse()->setBody(json_encode(array(
            'url' => $response['url']
        )));

        //-------------------------------
    }

    public function afterTokenAction()
    {
        $tokenSessionId = Mage::helper('M2ePro/Data_Session')->getValue('token_session_id', true);

        if (!$tokenSessionId) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Token is not defined'));
            return $this->_redirect('*/*/installation');
        }

        Mage::helper('M2ePro/Module_License')->setTrial(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $accountMode = $this->getRequest()->getParam('mode');

        $requestParams = array(
            'mode' => $accountMode,
            'token_session' => $tokenSessionId
        );
        $response = array_filter(Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
            ->processVirtual('account','add','entity',
                             $requestParams,NULL,
                             NULL,NULL,$accountMode));

        if (empty($response)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Account Add Entity failed.'));
            return $this->_redirect('*/*/installation');
        }

        if ($accountMode == Ess_M2ePro_Model_Connector_Ebay_Abstract::MODE_SANDBOX) {
            $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX;
        } else {
            $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION;
        }

        $data = array_merge(
            array(
                'title' => $response['info']['UserID'],
                'mode' => $accountMode,
                'ebay_info' => json_encode($response['info']),
                'server_hash' => $response['hash'],
                'token_session' => $tokenSessionId,
                'token_expired_date' => $response['token_expired_date']
            ),
            Mage::getModel('M2ePro/Ebay_Account')->getDefaultSettingsSimpleMode()
        );

        $accountModel = Mage::helper('M2ePro/Component_Ebay')->getModel('Account')->setData($data)->save();
        $accountModel->getChildObject()->updateEbayStoreInfo();

        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();

        Mage::helper('M2ePro/Module_Wizard')->setStep($this->getNick(),$nextStep);

        return $this->_redirect('*/*/installation');
    }

    //#############################################

    public function setModeAndUpdateAccountAction()
    {
        $mode = $this->getRequest()->getParam('mode','');

        if (!in_array(
            $mode,
            array(
                Ess_M2ePro_Helper_View_Ebay::MODE_SIMPLE,
                Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED
            )
        )) {
            return $this->getResponse()->setBody(json_encode(array(
                'result' => 'error',
                'message' => Mage::helper('M2ePro')->__('Unknown Mode "%mode%"', $mode)
            )));
        }

        Mage::helper('M2ePro/View_Ebay')->setMode($mode);

        $method = 'getDefaultSettings'.ucfirst($mode).'Mode';

        Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Account')
            ->getLastItem()
            ->addData(Mage::getModel('M2ePro/Ebay_Account')->$method())
            ->save();

        return $this->getResponse()->setBody(json_encode(array(
            'result' => 'success'
        )));
    }

    //#############################################

    public function getAccountSettingsAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        if (!$accountId) {
            return $this->getResponse()->setBody(json_encode(array(
                'result' => 'error',
                'message' => Mage::helper('M2ePro')->__('Account id is not defined')
            )));
        }

        $account = Mage::helper('M2ePro/Component_Ebay')->getObject('Account',$accountId);

        $yes = Mage::helper('M2ePro')->__('Yes');
        $no  = Mage::helper('M2ePro')->__('No');

        return $this->getResponse()->setBody(json_encode(array(
            'result' => 'success',
            'text' => array(
                'orders'        => $account->getChildObject()->isOrdersModeEnabled() ? $yes : $no,
                'listing_other' => $account->getChildObject()->isOtherListingsSynchronizationEnabled() ? $yes : $no,
                'feedbacks'     => $account->getChildObject()->isFeedbacksReceive() ? $yes : $no,
            )
        )));

    }

    //#############################################
}