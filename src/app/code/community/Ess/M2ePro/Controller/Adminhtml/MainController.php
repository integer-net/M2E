<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_MainController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            // check rewrite menu
            if (count($this->getCustomViewComponentHelper()->getActiveComponents()) < 1) {
                throw new Exception('At least 1 channel of current view should be enabled.');
            }

            // update client data
            try {
                Mage::helper('M2ePro/Client')->updateBackupConnectionData(false);
            } catch (Exception $exception) {}

            // run servicing code
            try {
                Mage::getModel('M2ePro/Servicing_Dispatcher')->process(
                    Ess_M2ePro_Model_Servicing_Dispatcher::DEFAULT_INTERVAL
                );
            } catch (Exception $exception) {}
        }

        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            if (Mage::helper('M2ePro/Module_Maintenance')->isOwner()) {
                Mage::helper('M2ePro/Module_Maintenance')->prolongRestoreDate();
            } elseif (Mage::helper('M2ePro/Module_Maintenance')->isExpired()) {
                Mage::helper('M2ePro/Module_Maintenance')->disable();
            }
        }

        return $this;
    }

    //---------------------------------------------

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            $lockNotification = $this->addLockNotifications();
            $browserNotification = $this->addBrowserNotifications();
            $maintenanceNotification = $this->addMaintenanceNotifications();

            $muteMessages = $lockNotification || $browserNotification || $maintenanceNotification;

            if (!$muteMessages &&
                Mage::helper('M2ePro/Module_Wizard')->isFinished(
                    $this->getCustomViewHelper()->getWizardInstallationNick()
                )) {

                $licenseMainErrorStatus =
                    $this->addLicenseActivationNotifications() ||
                    $this->addLicenseValidationFailNotifications();

                if (!$licenseMainErrorStatus) {
                    $this->addLicenseModesNotifications();
                    $this->addLicenseStatusesNotifications();
                    $this->addLicenseExpirationDatesNotifications();
                    $this->addLicenseTrialNotifications();
                    $this->addLicensePreExpirationDateNotifications();
                }
            }

            $this->addServerNotifications();

            if (!$muteMessages) {
                $this->getCustomViewControllerHelper()->addMessages($this);
            }
        }

        return parent::loadLayout($ids, $generateBlocks, $generateXml);
    }

    //---------------------------------------------

    protected function addLeft(Mage_Core_Block_Abstract $block)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            if ($this->isContentLocked()) {
                return $this;
            }
        }

        return parent::addLeft($block);
    }

    protected function addContent(Mage_Core_Block_Abstract $block)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            if ($this->isContentLocked()) {
                return $this;
            }
        }

        return parent::addContent($block);
    }

    //---------------------------------------------

    protected function beforeAddContentEvent()
    {
        $this->addRequirementsErrorMessage();
        $this->addWizardUpgradeNotification();
    }

    //#############################################

    protected function getCustomViewHelper()
    {
        return Mage::helper('M2ePro/View')->getHelper($this->getCustomViewNick());
    }

    protected function getCustomViewComponentHelper()
    {
        return Mage::helper('M2ePro/View')->getComponentHelper($this->getCustomViewNick());
    }

    protected function getCustomViewControllerHelper()
    {
        return Mage::helper('M2ePro/View')->getControllerHelper($this->getCustomViewNick());
    }

    //---------------------------------------------

    abstract protected function getCustomViewNick();

    //#############################################

    private function addLockNotifications()
    {
        if (Mage::helper('M2ePro/Module')->isLockedByServer()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('M2E Pro module is locked because of security reason. Please contact us.')
            );
            return true;
        }
        return false;
    }

    private function addMaintenanceNotifications()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Maintenance')->isOwner()) {

            $this->_getSession()->addNotice(Mage::helper('M2ePro')->__(
                'Maintenance is Active.'
            ));

            return false;
        }

        $this->_getSession()->addError(Mage::helper('M2ePro')->__(
            'M2E Pro is working in maintenance mode at the moment. Developers are investigating your issue.'
        ).'<br/>'.Mage::helper('M2ePro')->__(
            'You will be able to see a content of this page soon. Please wait and then refresh a browser page later.'
        ));

        return true;
    }

    // --------------------------------------------

    private function addServerNotifications()
    {
        $messages = Mage::helper('M2ePro/Module')->getServerMessages();

        foreach ($messages as $message) {

            if (isset($message['text']) && isset($message['type']) && $message['text'] != '') {

                switch ($message['type']) {
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_ERROR:
                        $this->_getSession()->addError(Mage::helper('M2ePro')->__($message['text']));
                        break;
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_WARNING:
                        $this->_getSession()->addWarning(Mage::helper('M2ePro')->__($message['text']));
                        break;
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_SUCCESS:
                        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__($message['text']));
                        break;
                    case Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_NOTICE:
                    default:
                        $this->_getSession()->addNotice(Mage::helper('M2ePro')->__($message['text']));
                        break;
                }
            }
        }
    }

    private function addBrowserNotifications()
    {
        if (Mage::helper('M2ePro/Client')->isBrowserIE()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'We are sorry, Internet Explorer browser is not supported. Please, use
                 another browser (Mozilla Firefox, Google Chrome, etc.).'
            ));
            return true;
        }
        return false;
    }

    //#############################################

    private function addLicenseActivationNotifications()
    {
        if (!Mage::helper('M2ePro/Module_License')->getKey() ||
            !Mage::helper('M2ePro/Module_License')->getDomain() ||
            !Mage::helper('M2ePro/Module_License')->getIp() ||
            !Mage::helper('M2ePro/Module_License')->getDirectory()) {

            $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
            $endLink = '</a>';

            $message = Mage::helper('M2ePro')->__(
                'M2E Pro module requires activation. Go to the %slicense page%s.', $startLink, $endLink
            );

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    private function addLicenseValidationFailNotifications()
    {
        // MAGENTO GO UGLY HACK
        //#################################
        if (Mage::helper('M2ePro/Magento')->isGoEdition()) {
            return false;
        }
        //#################################

        $domainNotify = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/license/validation/domain/notification/', 'mode'
        );

        $licenseDomain = Mage::helper('M2ePro/Module_License')->getDomain();

        if ($domainNotify &&
            strtolower($licenseDomain) != strtolower(Mage::helper('M2ePro/Client')->getDomain())) {

            $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
            $endLink = '</a>';

            $message = 'M2E Pro license key validation is failed for this domain. ';
            $message .= 'Go to the %slicense page%s.';
            $message = Mage::helper('M2ePro')->__($message, $startLink, $endLink);

            $this->_getSession()->addError($message);
            return true;
        }

        $ipNotify = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/license/validation/ip/notification/', 'mode'
        );

        $licenseIp = Mage::helper('M2ePro/Module_License')->getIp();

        if ($ipNotify &&
            strtolower($licenseIp) != strtolower(Mage::helper('M2ePro/Client')->getIp())) {

            $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
            $endLink = '</a>';

            $message = 'M2E Pro license key validation is failed for this IP. ';
            $message .= 'Go to the %slicense page%s.';
            $message = Mage::helper('M2ePro')->__($message, $startLink, $endLink);

            $this->_getSession()->addError($message);
            return true;
        }

        $directoryNotify = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/license/validation/directory/notification/', 'mode'
        );

        $licenseDirectory = Mage::helper('M2ePro/Module_License')->getDirectory();

        if ($directoryNotify &&
            strtolower($licenseDirectory) != strtolower(Mage::helper('M2ePro/Client')->getBaseDirectory())) {

            $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
            $endLink = '</a>';

            $message = 'M2E Pro license key validation is failed for this base directory. ';
            $message .= 'Go to the %slicense page%s.';
            $message = Mage::helper('M2ePro')->__($message, $startLink, $endLink);

            $this->_getSession()->addError($message);
            return true;
        }

        return false;
    }

    // --------------------------------------------

    private function addLicenseModesNotifications()
    {
        $hasMessage = false;

        foreach ($this->getCustomViewComponentHelper()->getActiveComponents() as $component) {

            if (Mage::helper('M2ePro/Module_License')->isNoneMode($component)) {

                $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
                $endLink = '</a>';

                $message = 'M2E Pro module requires activation for "%s" component. ';
                $message .= 'Go to the %slicense page%s.';
                $message = Mage::helper('M2ePro')->__(
                    $message,
                    constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE'),
                    $startLink,
                    $endLink
                );

                $this->_getSession()->addError($message);
                $hasMessage = true;
            }
        }

        return $hasMessage;
    }

    private function addLicenseStatusesNotifications()
    {
        $hasMessage = false;

        foreach ($this->getCustomViewComponentHelper()->getActiveComponents() as $component) {

            if (Mage::helper('M2ePro/Module_License')->isSuspendedStatus($component)) {

                $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
                $endLink = '</a>';

                $message = 'M2E Pro module license suspended for "%s" component. ';
                $message .= 'Go to the %slicense page%s.';
                $message = Mage::helper('M2ePro')->__(
                    $message,
                    constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE'),
                    $startLink,
                    $endLink
                );

                $this->_getSession()->addError($message);
                $hasMessage = true;
            }

            if (Mage::helper('M2ePro/Module_License')->isClosedStatus($component)) {

                $message = 'M2E Pro module license closed for "%s" component. ';
                $message .= 'Go to the %slicense page%s.';

                $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
                $endLink = '</a>';

                $message = Mage::helper('M2ePro')->__(
                    $message,
                    constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE'),
                    $startLink,
                    $endLink
                );

                $this->_getSession()->addError($message);
                $hasMessage = true;
            }
        }

        return $hasMessage;
    }

    private function addLicenseExpirationDatesNotifications()
    {
        $hasMessage = false;

        foreach ($this->getCustomViewComponentHelper()->getActiveComponents() as $component) {

            if (Mage::helper('M2ePro/Module_License')->isExpirationDate($component)) {

                $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
                $endLink = '</a>';

                $message = 'M2E Pro module license has expired for "%s" component. ';
                $message .= 'Go to the %slicense page%s.';
                $message = Mage::helper('M2ePro')->__(
                    $message,
                    constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE'),
                    $startLink,
                    $endLink
                );

                $this->_getSession()->addError($message);
                $hasMessage = true;
            }
        }

        return $hasMessage;
    }

    private function addLicenseTrialNotifications()
    {
        $hasMessage = false;

        foreach ($this->getCustomViewComponentHelper()->getActiveComponents() as $component) {

            if (Mage::helper('M2ePro/Module_License')->isTrialMode($component)) {

                $expirationDate = Mage::helper('M2ePro/Module_License')->getTextExpirationDate($component);

                $message = 'M2E Pro module is running under Trial License for "%s" component, ';
                $message .= 'that will expire on %s.';
                $message = Mage::helper('M2ePro')->__(
                    $message,
                    constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE'),
                    $expirationDate
                );

                $this->_getSession()->addWarning($message);

                $hasMessage = true;
            }
        }

        return $hasMessage;
    }

    private function addLicensePreExpirationDateNotifications()
    {
        $hasMessage = false;

        foreach ($this->getCustomViewComponentHelper()->getActiveComponents() as $component) {

            if (Mage::helper('M2ePro/Module_License')->getIntervalBeforeExpirationDate($component) > 0 &&
                Mage::helper('M2ePro/Module_License')->getIntervalBeforeExpirationDate($component) <= 60*60*24*3) {

                $startLink = '<a href="'.Mage::helper('M2ePro/View_Configuration')->getLicenseUrl().'" target="_blank">';
                $endLink = '</a>';
                $expirationDate = Mage::helper('M2ePro/Module_License')->getTextExpirationDate($component);

                $message = 'M2E Pro module license will expire on %s for "%s" component. ';
                $message .= 'Go to the %slicense page%s.';
                $message = Mage::helper('M2ePro')->__(
                    $message,
                    $expirationDate,
                    constant('Ess_M2ePro_Helper_Component_'.ucfirst($component).'::TITLE'),
                    $startLink,
                    $endLink
                );

                $this->_getSession()->addWarning($message);

                $hasMessage = true;
            }
        }

        return $hasMessage;
    }

    //#############################################

    private function addWizardUpgradeNotification()
    {
        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeWizard = $wizardHelper->getActiveWizard($this->getCustomViewNick());

        if (!$activeWizard) {
            return;
        }

        $activeWizardNick = $wizardHelper->getNick($activeWizard);

        if ((bool)$this->getRequest()->getParam('wizard',false) ||
            $this->getRequest()->getControllerName() == 'adminhtml_wizard_'.$activeWizardNick) {
            return;
        }

        $wizardHelper->addWizardHandlerJs();

        // Video tutorial
        //-------------
        $this->_initPopUp();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/VideoTutorialHandler.js');
        //-------------

        $this->getLayout()->getBlock('content')->append(
            $wizardHelper->createBlock('notification',$activeWizardNick)
        );
    }

    //#############################################

    protected function addRequirementsErrorMessage()
    {
        if (Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/requirements/popup/', 'closed')) {
            return;
        };

        $isMeetRequirements = Mage::helper('M2ePro/Data_Cache')->getValue('is_meet_requirements');

        if ($isMeetRequirements === false) {
            $isMeetRequirements = true;
            foreach (Mage::helper('M2ePro/Module')->getRequirementsInfo() as $requirement) {
                if (!$requirement['current']['status']) {
                    $isMeetRequirements = false;
                    break;
                }
            }
            Mage::helper('M2ePro/Data_Cache')->setValue(
                'is_meet_requirements',(int)$isMeetRequirements, array(), 60*60
            );
        }

        if ($isMeetRequirements) {
            return;
        }

        $this->_initPopUp();
        $this->getLayout()->getBlock('content')->append(
            $this->getLayout()->createBlock('M2ePro/adminhtml_requirementsPopup')
        );
    }

    //#############################################

    private function isContentLocked()
    {
        return $this->isContentLockedByWizard() ||
               Mage::helper('M2ePro/Client')->isBrowserIE() ||
               Mage::helper('M2ePro/Module')->isLockedByServer() ||
               (
                   Mage::helper('M2ePro/Module_Maintenance')->isEnabled() &&
                   !Mage::helper('M2ePro/Module_Maintenance')->isOwner()
               );
    }

    private function isContentLockedByWizard()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!($activeWizard = $wizardHelper->getActiveBlockerWizard($this->getCustomViewNick()))) {
            return false;
        }

        $activeWizardNick = $wizardHelper->getNick($activeWizard);

        if ((bool)$this->getRequest()->getParam('wizard',false) ||
            $this->getRequest()->getControllerName() == 'adminhtml_wizard_'.$activeWizardNick) {
            return false;
        }

        return true;
    }

    //#############################################
}