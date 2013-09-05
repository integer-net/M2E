<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Listing_OtherController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
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
             ->addJs('M2ePro/Common/Listing/Other/GridHandler.js')
             ->addJs('M2ePro/Common/Buy/Listing/Other/GridHandler.js')
             ->addJs('M2ePro/Common/Play/Listing/Other/GridHandler.js')
             ->addJs('M2ePro/Common/Amazon/Listing/Other/GridHandler.js')

             ->addJs('M2ePro/ActionHandler.js')
             ->addJs('M2ePro/Listing/MovingHandler.js')
             ->addJs('M2ePro/Listing/Other/AutoMappingHandler.js')

             ->addJs('M2ePro/Listing/Other/MappingHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings/listing_other');
    }

    //#############################################

    public function indexAction()
    {
        // Check 3rd listing lock items
        //----------------------------
        $lockItemAmazon = Mage::getModel(
            'M2ePro/Listing_Other_LockItem',
            array('component'=>Ess_M2ePro_Helper_Component_Amazon::NICK)
        );
        $lockItemBuy = Mage::getModel(
            'M2ePro/Listing_Other_LockItem',
            array('component'=>Ess_M2ePro_Helper_Component_Buy::NICK)
        );
        $lockItemPlay = Mage::getModel(
            'M2ePro/Listing_Other_LockItem',
            array('component'=>Ess_M2ePro_Helper_Component_Play::NICK)
        );

        if ($lockItemAmazon->isExist() ||
            $lockItemBuy->isExist() || $lockItemPlay->isExist()) {
            $warning  = Mage::helper('M2ePro')->__('The 3rd party listings are locked by another process. ');
            $warning .= Mage::helper('M2ePro')->__('Please try again later.');
            $this->_getSession()->addWarning($warning);
        }
        //----------------------------

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other'))
             ->renderLayout();
    }

    //#############################################

    public function deleteAction()
    {
        $component = $this->getRequest()->getParam('component');

        if (!$component) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'Component is not defined.'
            ));
            return $this->_redirect('*/*/index');
        }

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
        return $this->_redirect('*/*/index',array('tab' => $component));
    }

    //#############################################
}