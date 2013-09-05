<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Listing_Other_MappingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function mapGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function mapAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $productId = $this->getRequest()->getPost('productId');
        $sku = $this->getRequest()->getPost('sku');
        $productOtherId = $this->getRequest()->getPost('otherProductId');

        if ((!$productId && !$sku) || !$productOtherId || !$componentMode) {
            exit();
        }

        $collection = Mage::getModel('catalog/product')->getCollection();

        $productId && $collection->addFieldToFilter('entity_id', $productId);
        $sku && $collection->addFieldToFilter('sku', $sku);

        $tempData = $collection->getSelect()->query()->fetch();
        $tempData || exit('1');

        $productId || $productId = $tempData['entity_id'];

        $productOtherInstance = Mage::helper('M2ePro/Component')->getComponentObject(
            $componentMode,'Listing_Other',$productOtherId
        );

        $productOtherInstance->mapProduct($productId, Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER);

        exit('0');
    }

    public function unmapAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $listingOtherProductId = $this->getRequest()->getParam('id');

        if(!$listingOtherProductId || !$componentMode) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Not enough data.'));
            return $this->_redirectUrl(base64_decode($this->getRequest()->getParam('redirect')));
        }

        $listingOtherProductInstance = Mage::getModel('M2ePro/Listing_Other')
            ->load($listingOtherProductId);

        if(!$listingOtherProductInstance->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Product does not exist.'));
            return $this->_redirectUrl(base64_decode($this->getRequest()->getParam('redirect')));
        }

        if(is_null($listingOtherProductInstance->getData('product_id'))) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('The item has not mapped product.'));
            return $this->_redirectUrl(base64_decode($this->getRequest()->getParam('redirect')));
        }

        $listingOtherProductInstance->unmapProduct(Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Product was successfully unmapped.'));
        return $this->_redirectUrl(base64_decode($this->getRequest()->getParam('redirect')));
    }

    public function autoMapAction()
    {
        $componentMode = $this->getRequest()->getParam('componentMode');
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            exit ('You should select one or more products');
        }

        if (empty($componentMode)) {
            exit ('Component is not defined.');
        }

        $productIds = explode(',', $productIds);

        $productsForMapping = array();
        foreach ($productIds as $productId) {

            /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
            $listingOther = Mage::helper('M2ePro/Component')
                ->getComponentObject($componentMode,'Listing_Other',$productId);

            if ($listingOther->getProductId()) {
                continue;
            }

            $productsForMapping[] = $listingOther;
        }

        $componentMode = ucfirst(strtolower($componentMode));
        $mappingModel = Mage::getModel('M2ePro/'.$componentMode.'_Listing_Other_Mapping');
        $mappingModel->initialize();

        if (!$mappingModel->autoMapOtherListingsProducts($productsForMapping)) {
            exit('1');
        }
    }

    //#############################################
}