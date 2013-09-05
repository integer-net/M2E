<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_OtherController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')

             ->addJs('M2ePro/GridHandler.js')
             ->addJs('M2ePro/Listing/Other/GridHandler.js')
             ->addJs('M2ePro/Ebay/Listing/Other/GridHandler.js')

             ->addJs('M2ePro/ActionHandler.js')
             ->addJs('M2ePro/Ebay/Listing/Other/ActionHandler.js')
             ->addJs('M2ePro/Listing/MovingHandler.js')

             ->addJs('M2ePro/Listing/Other/MappingHandler.js')
             ->addJs('M2ePro/Listing/Other/AutoMappingHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //#############################################

    public function viewAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_view'))
             ->renderLayout();
    }

    //#############################################

    public function viewGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_view_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$ebayProductsIds = $this->getRequest()->getParam('selected_products')) {
            exit('You should select products');
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $ebayProductsIds = explode(',', $ebayProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Ebay_OtherItem_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $ebayProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR) {
            exit(json_encode(array('result'=>'error','action_id'=>$actionId)));
        }

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING) {
            exit(json_encode(array('result'=>'warning','action_id'=>$actionId)));
        }

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS) {
            exit(json_encode(array('result'=>'success','action_id'=>$actionId)));
        }

        exit(json_encode(array('result'=>'error','action_id'=>$actionId)));
    }

    //-------------------------------------------

    public function runReviseProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE,array());
    }

    public function runRelistProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,array());
    }

    public function runStopProductsAction()
    {
        $this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP,array());
    }

    //#############################################

    public function deleteAction()
    {
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $listingOtherId = $this->getRequest()->getParam('id');

        /* @var $listingOther Ess_M2ePro_Model_Listing_Other */
        $listingOther = Mage::helper('M2ePro/Component')->getComponentObject(
            $component,'Listing_Other',$listingOtherId
        );

        if (!is_null($listingOther->getProductId())) {
            $listingOther->unmapProduct(Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION);
        }

        $listingOther->deleteInstance();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__(
            'The item was successfully removed.'
        ));
        return $this->_redirect('*/*/view',array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING_OTHER,
            'account' => $this->getRequest()->getParam('account'),
            'marketplace' => $this->getRequest()->getParam('marketplace'),
            'back' => $this->getRequest()->getParam('back'),
        ));
    }

    //#############################################
}