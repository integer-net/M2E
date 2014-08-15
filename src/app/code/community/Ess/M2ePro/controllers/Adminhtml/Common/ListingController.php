<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/DropDown.js')
             ->addCss('M2ePro/css/Plugin/DropDown.css')
             ->addJs('M2ePro/Plugin/AutoComplete.js')
             ->addCss('M2ePro/css/Plugin/AutoComplete.css');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings/listing');
    }

    //#############################################

    public function indexAction()
    {
        /*!(bool)Mage::getModel('M2ePro/Template_SellingFormat')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one selling format template first.')
        );

        !(bool)Mage::getModel('M2ePro/Template_Synchronization')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one synchronization template first.')
        );*/

        $this->_initAction();

        // Video tutorial
        //-------------
        $isListingTutorialShownForAmazon = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/common/amazon/listing/', 'tutorial_shown');
        $isListingTutorialShownForBuy = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/common/buy/listing/', 'tutorial_shown');
        $isListingTutorialShownForPlay = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/view/common/play/listing/', 'tutorial_shown');

        if (
            (Mage::helper('M2ePro/Component_Amazon')->isActive() && !$isListingTutorialShownForAmazon)
            ||
            (Mage::helper('M2ePro/Component_Buy')->isActive() && !$isListingTutorialShownForBuy)
            ||
            (Mage::helper('M2ePro/Component_Play')->isActive() && !$isListingTutorialShownForPlay)
        ) {
            $this->_initPopUp();
            $this->getLayout()->getBlock('head')->addJs('M2ePro/VideoTutorialHandler.js');
        }
        //-------------

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing'))
             ->renderLayout();
    }

    //#############################################

    public function searchAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search'))
             ->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function goToSellingFormatTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id)->getChildObject();

        if (!$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            $this->_redirect('*/*/index');
            return;
        }

        $url = Mage::helper('M2ePro/View')->getUrl(
            $model, 'template_sellingFormat', 'edit',
            array(
                'id' => $model->getData('template_selling_format_id'),
                'back' => Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );

        $this->_redirectUrl($url);
    }

    public function goToSynchronizationTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id)->getChildObject();

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            $this->_redirect('*/*/index');
            return;
        }

        $url = Mage::helper('M2ePro/View')->getUrl(
            $model, 'template_synchronization', 'edit',
            array(
                'id' => $model->getData('template_synchronization_id'),
                'back' => Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );

        $this->_redirectUrl($url);
    }

    //#############################################

    public function confirmTutorialAction()
    {
        $component = $this->getRequest()->getParam('component');

        if (empty($component)) {
            $this->_redirect('*/adminhtml_common_listing/index');
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/common/'.$component.'/listing/', 'tutorial_shown', 1
        );

        $this->_redirect(
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::getTabIdByComponent($component)
            )
        );
    }

    //#############################################

    public function getVariationEditPopupAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Product must be specified.')
            )));
        }

        $variationEditBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_listing_product_variation_edit','',
            array(
                'component' => $component,
                'listing_product_id' => $listingProductId,
            )
        );

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'text' => $variationEditBlock->toHtml()
        )));
    }

    //---------------------------------------------

    public function getVariationManagePopupAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Product must be specified.')
            )));
        }

        $variationManageBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_listing_product_variation_manage','',
            array(
                'component' => $component,
                'listing_product_id' => $listingProductId,
            )
        );

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'text' => $variationManageBlock->toHtml()
        )));
    }

    //#############################################

    public function variationEditAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');
        $variationData = $this->getRequest()->getParam('variation_data');

        if (!$listingProductId || !$component || !$variationData) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'Component, Listing Product and Variation Data must be specified.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        $magentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $magentoVariations = $magentoVariations['variations'];
        foreach ($magentoVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $option) {
                $value = $option['option'];
                $attribute = $option['attribute'];

                if ($variationData[$attribute] != $value) {
                    unset($magentoVariations[$key]);
                }
            }
        }

        if (count($magentoVariations) != 1) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Only 1 variation must leave.')
            )));
        }

        $listingProduct->getChildObject()->unsetMatchedVariation();
        $listingProduct->getChildObject()->setMatchedVariation(reset($magentoVariations));

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('Variation has been successfully edited.')
        )));
    }

    public function variationManageAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');
        $variationsData = $this->getRequest()->getParam('variation_data');

        if (!$listingProductId || !$component || !$variationsData) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'Component, Listing Product and Variation Data must be specified.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        if ($listingProduct->getChildObject()->isVariationsReady()) {
            $listingProduct = $listingProduct->duplicate();
        }

        $magentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $magentoVariations = $magentoVariations['variations'];

        $isFirst = true;
        foreach ($variationsData as $variationData) {

            !$isFirst && $listingProduct = $listingProduct->duplicate();
            $isFirst = false;

            $tempMagentoVariations = $magentoVariations;

            foreach ($tempMagentoVariations as $key => $magentoVariation) {
                foreach ($magentoVariation as $option) {
                    $value = $option['option'];
                    $attribute = $option['attribute'];

                    if ($variationData[$attribute] != $value) {
                        unset($tempMagentoVariations[$key]);
                    }
                }
            }

            if (count($tempMagentoVariations) != 1) {
                return $this->getResponse()->setBody(json_encode(array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Only 1 variation must leave.')
                )));
            }

            $listingProduct->getChildObject()->unsetMatchedVariation();
            $listingProduct->getChildObject()->setMatchedVariation(reset($tempMagentoVariations));
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('Variation(s) has been successfully saved.')
        )));
    }

    //---------------------------------------------

    public function variationManageGenerateAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'Component and Listing Product must be specified.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        $magentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $magentoVariations = $magentoVariations['variations'];

        if (!$this->getRequest()->getParam('unique',false)) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'success',
                'text' => $magentoVariations
            )));
        }

        $listingProducts = Mage::helper('M2ePro/Component')
            ->getComponentCollection($component,'Listing_Product')
            ->addFieldToFilter('listing_id',$listingProduct->getListingId())
            ->addFieldToFilter('product_id',$listingProduct->getProductId())
            ->getItems();

        foreach ($listingProducts as $listingProduct) {

            if (!$listingProduct->getChildObject()->isVariationsReady()) {
                continue;
            }

            $variations = $listingProduct->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            $options = $variation->getOptions();
            foreach ($options as &$option) {
                $option = array(
                    'product_id' => $option['product_id'],
                    'product_type' => $option['product_type'],
                    'attribute' => $option['attribute'],
                    'option' => $option['option']
                );
            }
            unset($option);

            foreach ($magentoVariations as $key => $variation) {
                if ($variation != $options) {
                    continue;
                }
                unset($magentoVariations[$key]);
            }
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'text' => array_values($magentoVariations)
        )));

    }

    //#############################################

    public function duplicateProductsAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductsIds = $this->getRequest()->getParam('ids');
        $listingProductsIds = explode(',',$listingProductsIds);
        $listingProductsIds = array_filter($listingProductsIds);

        if (empty($listingProductsIds) || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Products must be specified.')
            )));
        }

        foreach ($listingProductsIds as $listingProductId) {

            /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
                $component, 'Listing_Product', $listingProductId
            );

            $listingProduct->duplicate();
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('The items were successfully duplicated.')
        )));
    }

    //#############################################
}