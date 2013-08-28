<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_AccountController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/configuration')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Accounts'));

        $this->getLayout()->getBlock('head')->addJs('M2ePro/Plugin/DropDown.js')
                                            ->addCss('M2ePro/css/Plugin/DropDown.css');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/configuration/account');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_account'))
             ->renderLayout();
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $componentMode = Mage::getModel('M2ePro/Account')->load($id)->getComponentMode();
        return $this->_redirect('*/adminhtml_'.$componentMode.'_account/edit', array('id'=>$id));
    }

    public function accountGridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_account_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select account(s) to remove'));
            return $this->_redirect('*/*/index');
        }

        $idsForDelete = array();
        !is_null($id) && $idsForDelete[] = (int)$id;
        !is_null($ids) && $idsForDelete = array_merge($idsForDelete,(array)$ids);

        $deleted = $locked = 0;
        $isListing = false;
        $isGeneralTpl = false;
        foreach ($idsForDelete as $id) {

            /** @var $account Ess_M2ePro_Model_Account */
            $account = Mage::getModel('M2ePro/Account')->loadInstance($id);

            if ($account->isLocked(true)) {

                if ($account->isComponentModeEbay()) {
                    $isGeneralTpl = true;
                } elseif ($account->isComponentModeAmazon() ||
                          $account->isComponentModeBuy() ||
                          $account->isComponentModePlay()) {
                    $isListing = true;
                }

                $locked++;
            } else {

                try {

                    if ($account->isComponentModeEbay()) {

                        Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                            ->processVirtualAbstract('account','delete','entity',
                                                      array(), NULL,
                                                      NULL,$account->getId(),NULL);

                    } else if ($account->isComponentModeAmazon()) {

                        /** @var $amazonAccountObj Ess_M2ePro_Model_Amazon_Account */
                        $amazonAccountObj = $account->getChildObject();

                        $items = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')->getItems();
                        foreach ($items as $marketplaceObj) {

                            /** @var $marketplaceObj Ess_M2ePro_Model_Marketplace */

                            $tempMarketplaceData = $amazonAccountObj->getMarketplaceItem($marketplaceObj->getId());

                            if (is_null($tempMarketplaceData)) {
                                continue;
                            }

                            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
                            $dispatcherObject->processConnector('account', 'delete' ,'entity', array(),
                                                                $marketplaceObj, $account);
                        }

                    } else if ($account->isComponentModeBuy()) {

                        $marketplace = Mage::helper('M2ePro/Component_Buy')->getCachedObject(
                            'Marketplace', Ess_M2ePro_Helper_Component_Buy::MARKETPLACE_VIRTUAL_ID
                        );

                        $dispatcherObject = Mage::getModel('M2ePro/Buy_Connector')->getDispatcher();
                        $dispatcherObject->processConnector('account', 'delete' ,'entity',
                                                            array(), $marketplace, $account);
                    } else if ($account->isComponentModePlay()) {

                        $marketplace = Mage::helper('M2ePro/Component_Play')->getCachedObject(
                            'Marketplace', Ess_M2ePro_Helper_Component_Play::MARKETPLACE_VIRTUAL_ID
                        );

                        $dispatcherObject = Mage::getModel('M2ePro/Play_Connector')->getDispatcher();
                        $dispatcherObject->processConnector('account', 'delete' ,'entity',
                                                            array(), $marketplace, $account);
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

        $tempString = Mage::helper('M2ePro')->__('%s record(s) were successfully deleted.', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        if ($isGeneralTpl) {
            $tempString = $isListing
            ? Mage::helper('M2ePro')->__('%s record(s) are used in M2E Listing(s) or General Template(s).', $locked).' '
            : Mage::helper('M2ePro')->__('%s record(s) are used in General Template(s).', $locked) . ' ';
            $tempString .= Mage::helper('M2ePro')->__('Account must not be in use to be deleted.');
        } elseif ($isListing) {
            $tempString  = Mage::helper('M2ePro')->__('%s record(s) are used in M2E Listing(s).', $locked) . ' ';
            $tempString .= Mage::helper('M2ePro')->__('Account must not be in use to be deleted.');
        }

        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/*/index');
    }

    //#############################################
}