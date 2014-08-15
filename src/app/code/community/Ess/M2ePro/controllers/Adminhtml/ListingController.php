<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function checkLockListingAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');
        $component = $this->getRequest()->getParam('component');

        $lockItemParams = array(
            'id' => $listingId,
            'component' => $component
        );

        $lockItem = Mage::getModel('M2ePro/Listing_LockItem',$lockItemParams);

        if ($lockItem->isExist()) {
            return $this->getResponse()->setBody('locked');
        }

        return $this->getResponse()->setBody('unlocked');
    }

    public function lockListingNowAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');
        $component = $this->getRequest()->getParam('component');

        $lockItemParams = array(
            'id' => $listingId,
            'component' => $component
        );

        $lockItem = Mage::getModel('M2ePro/Listing_LockItem',$lockItemParams);

        if (!$lockItem->isExist()) {
            $lockItem->create();
        }
    }

    public function unlockListingNowAction()
    {
        $listingId = (int)$this->getRequest()->getParam('id');
        $component = $this->getRequest()->getParam('component');

        $lockItemParams = array(
            'id' => $listingId,
            'component' => $component
        );

        $lockItem = Mage::getModel('M2ePro/Listing_LockItem',$lockItemParams);

        if ($lockItem->isExist()) {
            $lockItem->remove();
        }
    }

    //#############################################

    public function clearLogAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear.'));
            $this->_redirect('*/*/index');
            return;
        }

        foreach ($ids as $id) {
            Mage::getModel('M2ePro/Listing_Log')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing(s) log was successfully cleaned.'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

    public function getErrorsSummaryAction()
    {
        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
            'type_log'   => 'listing'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_log_errorsSummary','',$blockParams);
        return $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################
}