<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

// move from 3rd party to listing

class Ess_M2ePro_Adminhtml_Listing_Other_MovingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function moveToListingGridAction()
    {
        Mage::helper('M2ePro/Data_Global')->setValue(
            'componentMode', $this->getRequest()->getParam('componentMode')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'accountId', $this->getRequest()->getParam('accountId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'marketplaceId', $this->getRequest()->getParam('marketplaceId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'attrSetId', json_decode($this->getRequest()->getParam('attrSetId'))
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'ignoreListings', json_decode($this->getRequest()->getParam('ignoreListings'))
        );

        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_grid','',
            array(
                'mode' => strtolower($this->getRequest()->getParam('componentMode')).'ListingOther',
                'grid_url' => $this->getUrl('*/adminhtml_listing_other_moving/moveToListingGrid',array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function getFailedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_failedProducts','',
            array(
              'grid_url' => $this->getUrl('*/adminhtml_listing_other_moving/failedProductsGrid',array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    public function failedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_failedProducts_grid','',
            array(
              'grid_url' => $this->getUrl('*/adminhtml_listing_other_moving/failedProductsGrid',array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

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

        $collection->addFieldToFilter('`main_table`.`marketplace_id`', $marketplaceId);
        $collection->addFieldToFilter('`main_table`.`account_id`', $accountId);

        if ($collection->getSize() < 1) {
            $response['offerListingCreation'] = true;
        }

        exit(json_encode($response));
    }

    //#############################################

    public function tryToMoveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode,'Listing',$listingId
        );

        $failedProducts = array();
        foreach ($selectedProducts as $selectedProduct) {
            $otherListingProductInstance = Mage::helper('M2ePro/Component')->getComponentObject(
                $componentMode,'Listing_Other',$selectedProduct
            );

            if (!$listingInstance->addProduct($otherListingProductInstance->getProductId(),true) &&
                in_array($otherListingProductInstance->getProductId(),$failedProducts) === false) {
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

    //#############################################

    public function moveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');

        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode,'Listing',$listingId
        );

        $otherLogModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $otherLogModel->setComponentMode($componentMode);

        $listingLogModel = Mage::getModel('M2ePro/Listing_Log');
        $listingLogModel->setComponentMode($componentMode);

        $errors = 0;
        foreach ($selectedProducts as $otherListingProduct) {

            $otherListingProductInstance = Mage::helper('M2ePro/Component')
                ->getComponentObject($componentMode,'Listing_Other',$otherListingProduct);

            $listingProductInstance = $listingInstance
                ->getChildObject()
                ->addProductFromOther($otherListingProductInstance);

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

        $temp = Mage::helper('M2ePro/Component')->getComponentCollection($componentMode, 'Listing_Other');
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

        $account = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode,'Account',$accountId
        );
        $marketplace = Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $componentMode,'Marketplace',$marketplaceId
        );

        $movingModel = Mage::getModel('M2ePro/'.$componentMode.'_Listing_Other_Moving');
        $movingModel->initialize($marketplace,$account);
        $movingModel->getDefaultListing($otherListingInstance);
    }

    //#############################################
}