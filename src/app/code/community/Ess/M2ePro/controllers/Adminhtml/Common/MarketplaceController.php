<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_MarketplaceController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Marketplaces'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/MarketplaceHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration/marketplace');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_marketplace'))
             ->renderLayout();
    }

    public function saveAction()
    {
        $marketplaces = Mage::getModel('M2ePro/Marketplace')->getCollection();

        foreach ($marketplaces as $marketplace) {
            $newStatus = $this->getRequest()->getParam('status_'.$marketplace->getId());

            if (is_null($newStatus)) {
                 continue;
            }
            if ($marketplace->getStatus() == $newStatus) {
                continue;
            }
            $marketplace->setData('status', $newStatus)->save();
        }
    }

    //#############################################

    public function runSynchNowAction()
    {
        session_write_close();

        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id');
        $marketplaceObj = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',$marketplaceId);

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(array($marketplaceObj->getComponentMode()));
        $dispatcher->setAllowedTasksTypes(array(Ess_M2ePro_Model_Synchronization_Task::MARKETPLACES));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array('marketplace_id' => $marketplaceId));

        $dispatcher->process();
    }

    //#############################################
}