<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_ListingOtherController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/listings')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/Listing/ItemGridHandler.js')
             ->addJs('M2ePro/Listing/ActionHandler.js')
             ->addJs('M2ePro/Listing/MoveToListingHandler.js')
             ->addJs('M2ePro/Listing/Other/MapToProductHandler.js')
             ->addJs('M2ePro/Listing/Other/AutoMapHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/listings/listing_other');
    }

    //#############################################

    public function indexAction()
    {
        // Check 3rd listing lock items
        //----------------------------
        $lockItemEbay = Mage::getModel(
            'M2ePro/Listing_Other_LockItem',
            array('component'=>Ess_M2ePro_Helper_Component_Ebay::NICK)
        );
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

        if ($lockItemEbay->isExist() || $lockItemAmazon->isExist() ||
            $lockItemBuy->isExist() || $lockItemPlay->isExist()) {
            $warning  = Mage::helper('M2ePro')->__('The 3rd party listings are locked by another process. ');
            $warning .= Mage::helper('M2ePro')->__('Please try again later.');
            $this->_getSession()->addWarning($warning);
        }
        //----------------------------

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listing_other'))
             ->renderLayout();
    }

    //#############################################

    public function mapToProductGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapToProduct_Grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function failedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()
                      ->createBlock('M2ePro/adminhtml_listing_other_moveToListing_failedProducts_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function prepareMoveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));

        $selectedProductsParts = array_chunk($selectedProducts, 1000);

        $attributes = array();
        foreach ($selectedProductsParts as $selectedProductsPart) {
            $listingOtherCollection = Mage::helper('M2ePro/Component')
                ->getComponentModel($componentMode, 'Listing_Other')
                ->getCollection();

            $listingOtherCollection->addFieldToFilter('`main_table`.`id`', array('in' => $selectedProductsPart));
            $tempData = $listingOtherCollection
                ->getSelect()
                ->query()
                ->fetchAll();

            foreach ($tempData as $data) {
                $data['product_id'] || exit('1');
            }

            $listingOtherCollection->getSelect()->join(
                array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                '`main_table`.`product_id` = `cpe`.`entity_id`'
            );

            $tempData = $listingOtherCollection
                ->getSelect()
                ->query()
                ->fetchAll();

            foreach ($tempData as $data) {
                in_array($data['attribute_set_id'],$attributes) === false && $attributes[] = $data['attribute_set_id'];
            }

            $tempData = $listingOtherCollection
                ->getSelect()
                ->group(array('main_table.account_id','main_table.marketplace_id'))
                ->query()
                ->fetchAll();

            count($tempData) > 1 && exit('2');
        }

        $marketplaceId = $tempData[0]['marketplace_id'];
        $accountId = $tempData[0]['account_id'];

        $response = array(
            'accountId' => $accountId,
            'marketplaceId' => $marketplaceId,
            'attrSetId' => $attributes
        );

        if ($componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            exit(json_encode($response));
        }

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($componentMode, 'Listing')
            ->getCollection();

        $collection->getSelect()
            ->join(array('tg'=>Mage::getResourceModel('M2ePro/Template_General')->getMainTable()),
                   '`main_table`.`template_general_id` = `tg`.`id`',
                   null);

        $collection->addFieldToFilter('`tg`.`marketplace_id`', $marketplaceId);
        $collection->addFieldToFilter('`tg`.`account_id`', $accountId);

        if ($collection->getSize() < 1) {
            $response['offerListingCreation'] = true;
        }

        exit(json_encode($response));
    }

    //#############################################

    public function clearLogAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear'));
            return $this->_redirect('*/*/index');
        }

        $idsForClear = array();
        !is_null($id) && $idsForClear[] = (int)$id;
        !is_null($ids) && $idsForClear = array_merge($idsForClear,(array)$ids);

        foreach ($idsForClear as $id) {
            Mage::getModel('M2ePro/Listing_Other_Log')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The 3rd party listing(s) log has been successfully cleaned.')
        );
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

    //#############################################

    public function checkLockListingAction()
    {
        $component = $this->getRequest()->getParam('component');

        $lockItem = Mage::getModel('M2ePro/Listing_Other_LockItem',array('component' => $component));

        if ($lockItem->isExist()) {
            exit('locked');
        }

        exit('unlocked');
    }

    public function lockListingNowAction()
    {
        $component = $this->getRequest()->getParam('component');

        $lockItem = Mage::getModel('M2ePro/Listing_Other_LockItem',array('component' => $component));

        if (!$lockItem->isExist()) {
            $lockItem->create();
        }

        exit();
    }

    public function unlockListingNowAction()
    {
        $component = $this->getRequest()->getParam('component');

        $lockItem = Mage::getModel('M2ePro/Listing_Other_LockItem',array('component' => $component));

        if ($lockItem->isExist()) {
            $lockItem->remove();
        }

        exit();
    }

    //---------------------------------------------

    public function getErrorsSummaryAction()
    {
        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable(),
            'type_log' => 'listing_other'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_log_errorsSummary','',$blockParams);
        exit($block->toHtml());
    }

    //#############################################

    public function createDefaultListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $accountId = (int)$this->getRequest()->getParam('accountId');
        $marketplaceId = (int)$this->getRequest()->getParam('marketplaceId');

        if (!$componentMode || !$accountId || !$marketplaceId) {
            exit(json_encode(array(
                'result' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component Mode or Account ID or Marketplace ID is empty.')
            )));
        }

        $temp = Mage::helper('M2ePro/Component')->getComponentModel($componentMode, 'Listing_Other')->getCollection();
        $temp->addFieldToFilter('marketplace_id',$marketplaceId);
        $temp->addFieldToFilter('account_id',$accountId);

        $temp->getSelect()->limit(1);
        $otherListingInstance = $temp->getFirstItem();

        if (!$otherListingInstance->getId()) {
            exit(json_encode(array(
                'result' => 'error',
                'message' => Mage::helper('M2ePro')->__('No Other Listings found.')
            )));
        }

        $componentMode = ucfirst(strtolower($componentMode));
        $account = Mage::helper('M2ePro/Component_'.$componentMode)->getCachedObject('Account',$accountId);
        $marketplace = Mage::helper('M2ePro/Component_'.$componentMode)->getCachedObject('Marketplace',$marketplaceId);

        $movingModel = Mage::getModel('M2ePro/'.$componentMode.'_Listing_Other_Moving');
        $movingModel->initialize($marketplace,$account);
        $movingModel->getDefaultListing($otherListingInstance);
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