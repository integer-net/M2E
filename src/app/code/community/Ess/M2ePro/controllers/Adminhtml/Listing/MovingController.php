<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

// move from listing to listing

class Ess_M2ePro_Adminhtml_Listing_MovingController
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
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/moveToListingGrid', array('_current'=>true)),
                'moving_handler_js' => 'ListingGridHandlerObj.movingHandler',
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
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/failedProductsGrid', array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    public function failedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_failedProducts_grid','',
            array(
                'grid_url' => $this->getUrl('*/adminhtml_listing_moving/failedProductsGrid', array('_current'=>true))
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function prepareMoveToListingAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));

        $listingProductCollection = Mage::helper('M2ePro/Component')
            ->getComponentModel($componentMode, 'Listing_Product')
            ->getCollection();

        $listingProductCollection->addFieldToFilter('`main_table`.`id`', array('in' => $selectedProducts));
        $tempData = $listingProductCollection
            ->getSelect()
            ->join( array('listing'=>Mage::getSingleton('core/resource')->getTableName('m2epro_listing')),
                    '`main_table`.`listing_id` = `listing`.`id`' )
            ->join( array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                    '`main_table`.`product_id` = `cpe`.`entity_id`' )
            ->group(array('listing.account_id','listing.marketplace_id','cpe.attribute_set_id'))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('marketplace_id', 'account_id'), 'listing')
            ->columns('attribute_set_id', 'cpe')
            ->query()
            ->fetchAll();

        $attributeSets = array();
        foreach ($tempData as $data) {
            $attributeSets[] = $data['attribute_set_id'];
        }

        return $this->getResponse()->setBody(json_encode(array(
            'accountId' => $tempData[0]['account_id'],
            'marketplaceId' => $tempData[0]['marketplace_id'],
            'attrSetId' => $attributeSets
        )));
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
            $listingProductInstance = Mage::helper('M2ePro/Component')->getComponentObject(
                $componentMode,'Listing_Product',$selectedProduct
            );

            if (!$listingInstance->addProduct($listingProductInstance->getProductId(),true)) {
                $failedProducts[] = $listingProductInstance->getProductId();
            }
        }

        if (count($failedProducts) == 0) {
            return $this->getResponse()->setBody(json_encode(array('result' => 'success')));
        }

        return $this->getResponse()->setBody(json_encode(array(
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

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode($componentMode);

        $errors = 0;
        foreach ($selectedProducts as $listingProductId) {

            $listingProductInstance = Mage::helper('M2ePro/Component')
                ->getComponentObject($componentMode,'Listing_Product',$listingProductId);

            if ($listingProductInstance->isLockedObject() ||
                $listingProductInstance->isLockedObject('in_action')) {

                $logModel->addProductMessage(
                    $listingProductInstance->getListingId(),
                    $listingProductInstance->getProductId(),
                    $listingProductInstance->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                    // M2ePro_TRANSLATIONS
                    // Item was not moved
                    'Item was not moved',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $errors++;
                continue;
            }

            if (!$listingInstance->addProduct($listingProductInstance->getProductId(),true)) {

                $logModel->addProductMessage(
                    $listingProductInstance->getListingId(),
                    $listingProductInstance->getProductId(),
                    $listingProductInstance->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                    // M2ePro_TRANSLATIONS
                    // Item was not moved
                    'Item was not moved',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                $errors++;
                continue;
            }

            $logModel->addProductMessage(
                $listingId,
                $listingProductInstance->getProductId(),
                $listingProductInstance->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                // M2ePro_TRANSLATIONS
                // Item was successfully moved
                'Item was successfully moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $logModel->addProductMessage(
                $listingProductInstance->getListingId(),
                $listingProductInstance->getProductId(),
                $listingProductInstance->getId(),
                Ess_M2ePro_Helper_Data::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                // M2ePro_TRANSLATIONS
                // Item was successfully moved
                'Item was successfully moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $listingProductInstance->setData('listing_id', $listingId)->save();

            // Set listing store id to Component Item
            //---------------------------------
            $method = 'get' . ucfirst(strtolower($componentMode)) . 'Item';
            if (!$listingProductInstance->isNotListed()) {
                $listingProductInstance->getChildObject()
                    ->$method()
                    ->setData('store_id', $listingInstance->getStoreId())
                    ->save();
            }
            //---------------------------------
        };

        if ($errors == 0) {
            return $this->getResponse()->setBody(json_encode(array('result'=>'success')));
        } else {
            return $this->getResponse()->setBody(json_encode(array('result'=>'error', 'errors'=>$errors)));
        }
    }

    //#############################################
}