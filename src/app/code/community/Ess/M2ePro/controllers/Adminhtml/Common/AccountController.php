<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_AccountController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Accounts'));

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/DropDown.js')
                                            ->addCss('M2ePro/css/Plugin/DropDown.css');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration/account');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_account'))
             ->renderLayout();
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $componentMode = Mage::getModel('M2ePro/Account')->load($id)->getComponentMode();
        return $this->_redirect('*/adminhtml_common_'.$componentMode.'_account/edit', array('id'=>$id));
    }

    public function accountGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_account_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select account(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {

            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Account')->loadInstance($id);

            if ($account->isLocked(true)) {
                $locked++;
            } else {

                try {

                    if ($account->isComponentModeAmazon()) {

                        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
                        $dispatcherObject->processConnector('account', 'delete' ,'entity', array(), $account);

                    } else if ($account->isComponentModeBuy()) {

                        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Dispatcher');
                        $dispatcherObject->processConnector('account', 'delete' ,'entity', array(), $account);

                    } else if ($account->isComponentModePlay()) {

                        $dispatcherObject = Mage::getModel('M2ePro/Connector_Play_Dispatcher');
                        $dispatcherObject->processConnector('account', 'delete' ,'entity', array(), $account);
                    }

                } catch (Exception $e) {

                    $account->deleteProcessingRequests();
                    $account->deleteObjectLocks();
                    $account->deleteInstance();

                    throw $e;
                }

                $account->deleteProcessingRequests();
                $account->deleteObjectLocks();
                $account->deleteInstance();

                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString  = Mage::helper('M2ePro')->__('%amount% record(s) are used in M2E Listing(s).', $locked) . ' ';
        $tempString .= Mage::helper('M2ePro')->__('Account must not be in use to be deleted.');
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //#############################################
}