<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_ListingOtherController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/listings')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/listings/listing_other');
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_listingOther/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Listing_Other */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_other');
        $block->enableEbayTab();

        $this->getResponse()->setBody($block->getEbayTabHtml());
    }

    public function gridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_grid');
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

    public function mapToProductAction()
    {
        $productId = $this->getRequest()->getPost('productId');
        $sku = $this->getRequest()->getPost('sku');
        $productOtherId = $this->getRequest()->getPost('otherProductId');

        if ((!$productId && !$sku) || !$productOtherId) {
            exit();
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->joinField('qty',
                        'cataloginventory/stock_item',
                        'qty',
                        'product_id=entity_id',
                        '{{table}}.stock_id=1',
                        'left');

        $productId && $collection->addFieldToFilter('entity_id', $productId);
        $sku && $collection->addFieldToFilter('sku', $sku);

        $tempData = $collection->getSelect()->query()->fetch();
        $tempData || exit('1');

        $productId || $productId = $tempData['entity_id'];

        $productOtherInstance = Mage::helper('M2ePro/Component_Ebay')
            ->getModel('Listing_Other')
            ->load($productOtherId);

        $productOtherInstance->mapProduct($productId, Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER);

        exit('0');
    }

    public function mapAutoToProductAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            exit ('You should select one or more products');
        }

        $productIds = explode(',', $productIds);

        $productsForMapping = array();
        foreach ($productIds as $productId) {

            /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
            $listingOther = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Other',$productId);

            if ($listingOther->getProductId()) {
                continue;
            }

            $productsForMapping[] = $listingOther;
        }

        /** @var $mappingModel Ess_M2ePro_Model_Ebay_Listing_Other_Mapping */
        $mappingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Mapping');
        $mappingModel->initialize();

        if (!$mappingModel->autoMapOtherListingsProducts($productsForMapping)) {
            exit('1');
        }
    }

    public function unmapToProductAction()
    {
        $listingOtherProductId = $this->getRequest()->getParam('id');

        if(!$listingOtherProductId) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Id required.'));
            return $this->_redirect('*/adminhtml_listingOther/index/tab/ebay/');
        }

        $listingOtherProductInstance = Mage::getModel('M2ePro/Listing_Other')
            ->load($listingOtherProductId);

        if(!$listingOtherProductInstance->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Product does not exist.'));
            return $this->_redirect('*/adminhtml_listingOther/index/tab/ebay/');
        }

        if(is_null($listingOtherProductInstance->getData('product_id'))) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('The item has not mapped product.'));
            return $this->_redirect('*/adminhtml_listingOther/index/tab/ebay/');
        }

        $listingOtherProductInstance->unmapProduct(Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Product was successfully unmapped.'));
        $this->_redirect('*/adminhtml_listingOther/index/tab/ebay/');
    }

    //#############################################

    public function tryToMoveToListingAction()
    {
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $failedProducts = array();
        foreach ($selectedProducts as $selectedProduct) {
            $otherListingProductInstance = Mage::helper('M2ePro/Component_Ebay')
                ->getModel('Listing_Other')
                ->load($selectedProduct);

            if (!$listingInstance->addProduct($otherListingProductInstance->getProductId(),true)) {
                $failedProducts[] = $otherListingProductInstance->getProductId();
            }
        }

        count($failedProducts) == 0 && exit(json_encode(array(
            'result' => 'success'
        )));

        exit(json_encode(array(
            'result' => 'fail',
            'failed_products' => $failedProducts
        )));
    }

    public function moveToListingAction()
    {
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $otherLogModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $otherLogModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $listingLogModel = Mage::getModel('M2ePro/Listing_Log');
        $listingLogModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $errors = 0;
        foreach ($selectedProducts as $otherListingProduct) {

            $otherListingProductInstance = Mage::helper('M2ePro/Component_Ebay')
                ->getModel('Listing_Other')
                ->load($otherListingProduct);

            $listingProductInstance = $listingInstance
                ->addProduct($otherListingProductInstance->getData('product_id'));

            if (!($listingProductInstance instanceof Ess_M2ePro_Model_Listing_Product)) {

                $otherLogModel->addProductMessage(
                    $otherListingProductInstance->getId(),
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_MOVE_LISTING,
                    // Parser hack -> Mage::helper('M2ePro')->__('Product already exists in M2E listing(s).');
                    'Product already exists in M2E listing(s).',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $errors++;
                continue;
            }

            $ebayItemId = $otherListingProductInstance->getChildObject()->getItemId();
            $ebayItemsCollection = Mage::getModel('M2ePro/Ebay_Item')->getCollection();
            $ebayItemsCollection->addFieldToFilter('item_id', $ebayItemId);

            if ($ebayItemsCollection->getSize() < 1) {
                $dataForAdd = array(
                    'item_id' => $otherListingProductInstance->getChildObject()->getItemId(),
                    'product_id' => $listingProductInstance->getProductId(),
                    'store_id' => $listingInstance->getStoreId()
                );
                Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save();
            } else {
                $ebayItemsCollection->getFirstItem()->setData('store_id', $listingInstance->getStoreId())->save();
            }

            $dataForUpdate = array(
                'ebay_item_id' => $ebayItemsCollection->getFirstItem()->getId(),
                'online_buyitnow_price' => $otherListingProductInstance->getChildObject()->getOnlinePrice(),
                'online_qty' => $otherListingProductInstance->getChildObject()->getOnlineQty(),
                'online_qty_sold' => $otherListingProductInstance->getChildObject()->getOnlineQtySold(),
                'online_bids' => $otherListingProductInstance->getChildObject()->getOnlineBids(),
                'online_start_date' => $otherListingProductInstance->getChildObject()->getStartDate(),
                'online_end_date' => $otherListingProductInstance->getChildObject()->getEndDate(),
                'status' => $otherListingProductInstance->getStatus(),
                'status_changer' => $otherListingProductInstance->getStatusChanger(),
                'is_m2epro_listed_item' => Ess_M2ePro_Model_Ebay_Listing_Product::IS_M2EPRO_LISTED_ITEM_NO
            );

            $listingProductInstance->addData($dataForUpdate)->save();

            $otherLogModel->addProductMessage(
                $otherListingProductInstance->getId(),
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_MOVE_LISTING,
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully moved');
                'Item was successfully moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $listingLogModel->addProductMessage(
                $listingId,
                $otherListingProductInstance->getProductId(),
                $listingProductInstance->getId(),
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_FROM_OTHER_LISTING,
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully moved');
                'Item was successfully moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $otherListingProductInstance->deleteInstance();
        };

        ($errors == 0)
            ? exit(json_encode(array('result'=>'success')))
            : exit(json_encode(array('result'=>'error',
            'errors'=>$errors)));
    }

    //#############################################
}