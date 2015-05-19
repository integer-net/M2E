<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Listing_Variation_Product_ManageController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/AutoComplete.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')

            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Common/Listing/GridHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/GridHandler.js')

            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ActionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ProductSearchHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/TemplateDescriptionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/VariationProductManageVariationsGridHandler.js')

            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Common/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Common/Listing/Category/Handler.js')
            ->addJs('M2ePro/Common/Listing/AddListingHandler.js')
            ->addJs('M2ePro/Common/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ProductsFilterHandler.js')

            ->addJs('M2ePro/Common/Listing/Product/VariationHandler.js');

        return $this;
    }

    protected function _setActiveMenu($menuPath) {
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings/listing');
    }

    // -------------------------------------------

    protected function addNotificationMessages() {}

    protected function beforeAddContentEvent() {}

    // -------------------------------------------

    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);
        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();

        $tabs = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs');
        $tabs->setListingProductId($productId);

        return $this->getResponse()->setBody($tabs->toHtml());
    }

    //#############################################

    public function viewVariationsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_variations_grid');
        $grid->setListingProductId($productId);

        $help = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_variations_help');
        $help->setListingProductId($productId);

        $this->_initAction()->_addContent($help);

        $this->_initAction()->_addContent($grid)->renderLayout();
    }

    public function viewVariationsGridAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_variations_grid');
        $grid->setListingProductId($productId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    public function setChildListingProductOptionsAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $productOptions   = $this->getRequest()->getParam('product_options');

        if (empty($listingProductId) || empty($productOptions['values']) || empty($productOptions['attr'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Listing_Product $child */
        $child = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$listingProductId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
        $childTypeModel = $child->getChildObject()->getVariationManager()->getTypeModel();

        $parentListingProduct = $childTypeModel->getParentListingProduct();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonParentListingProduct */
        $amazonParentListingProduct = $parentListingProduct->getChildObject();

        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->loadProduct(
            $parentListingProduct->getProductId(), $parentListingProduct->getListing()->getStoreId()
        );
        $magentoVariation = $magentoProduct->getVariationInstance()->getVariationTypeStandard(array_combine(
            $productOptions['attr'],
            $productOptions['values']
        ));

        $childTypeModel->setProductVariation($magentoVariation);

        $productOptions = $childTypeModel->getProductOptions();
        $channelOptions = $childTypeModel->getChannelOptions();
        $matchedAttributes = $amazonParentListingProduct->getVariationManager()->getTypeModel()->getMatchedAttributes();

        foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
            Mage::helper('M2ePro/Component_Amazon_Vocabulary')->addOptions(
                $parentListingProduct->getMarketplace()->getId(),
                $productOptions[$magentoAttr],
                $channelOptions[$amazonAttr],
                $amazonAttr
            );
        }

        $amazonParentListingProduct->getVariationManager()
            ->getTypeModel()
            ->getProcessor()
            ->process();

        $this->getResponse()->setBody(json_encode(array('success' => true)));
    }

    //#############################################

    public function viewVariationsSettingsAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $settings = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_variation_product_manage_tabs_settings')
            ->setListingProductId($productId);

        $html = $settings->toHtml();
        $messages = $settings->getMessages();

        return $this->getResponse()->setBody(json_encode(array(
            'errors_count' => count($messages),
            'html' => $html
        )));
    }

    public function setGeneralIdOwnerAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $generalIdOwner = $this->getRequest()->getParam('general_id_owner', null);

        if (empty($listingProductId) || is_null($generalIdOwner)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        if ($generalIdOwner != Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES) {
            return $this->getResponse()->setBody(json_encode(
                $this->setGeneralIdOwner($listingProductId, $generalIdOwner)
            ));
        }

        $sku = Mage::helper('M2ePro/Data_Session')->getValue('listing_product_setting_owner_sku_' . $listingProductId);

        if (!$this->hasListingProductSku($listingProductId) && empty($sku)) {
            return $this->getResponse()->setBody(json_encode(array('success' => false, 'empty_sku' => true)));
        }

        $data = $this->setGeneralIdOwner($listingProductId, $generalIdOwner);

        if (!$data['success']) {
            $mainBlock = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_listing_templateDescription_main');
            $mainBlock->setMessages(array(
                array(
                'type' => 'warning',
                'text' => $data['msg']
            )));
            $data['html'] = $mainBlock->toHtml();
        } else {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);
            $listingProduct->setData('sku', $sku);
            $listingProduct->save();

            Mage::helper('M2ePro/Data_Session')->removeValue('listing_product_setting_owner_sku_' . $listingProductId);
        }

        return $this->getResponse()->setBody(json_encode($data));
    }

    public function setListingProductSkuAction()
    {
        $listingProductId = $this->getRequest()->getParam('product_id');
        $sku = $this->getRequest()->getParam('sku');
        $msg = '';

        if (empty($listingProductId) || is_null($sku)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $listingProductId);

        if ($this->isExistInM2eProListings($listingProduct, $sku)) {
            $msg = Mage::helper('M2ePro')->__('This SKU is already being used in M2E Pro Listing.');
        } else if ($this->isExistInOtherListings($listingProduct, $sku)) {
            $msg = Mage::helper('M2ePro')->__('This SKU is already being used in M2E Pro 3rd Party Listing.');
        } else {

            $skuInfo = $this->getSkuInfo($listingProduct, $sku);

            if (!$skuInfo) {
                $msg = Mage::helper('M2ePro')->__('This SKU is not found in your Amazon Inventory.');
            } else if ($skuInfo['info']['type'] != 'parent') {
                $msg = Mage::helper('M2ePro')->__('This SKU is used not for Parent Product in your Amazon Inventory.');
            } else if (!empty($skuInfo['info']['bad_parent'])) {
                $msg = Mage::helper('M2ePro')->__(
                    'Working with found Amazon Product is impossible because of the
                    limited access due to Amazon API restriction'
                );
            } else if ($skuInfo['asin'] != $listingProduct->getGeneralId()) {
                $msg = Mage::helper('M2ePro')->__(
                    'The ASIN/ISBN of the Product with this SKU in your Amazon Inventory is different
                     from the ASIN/ISBN for which you want to set you are creator.'
                );
            }
        }

        if (!empty($msg)) {
            return $this->getResponse()->setBody(json_encode(array(
                'success' => false,
                'msg' => $msg
            )));
        }

        Mage::helper('M2ePro/Data_Session')->setValue('listing_product_setting_owner_sku_' . $listingProductId, $sku);

        return $this->getResponse()->setBody(json_encode(array('success' => true)));
    }

    public function viewTemplateDescriptionsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_templateDescription_grid');
        $grid->setCheckNewAsinAccepted(true);
        $grid->setProductsIds(array($productId));
        $grid->setMapToTemplateJsFn('ListingGridHandlerObj.variationProductManageHandler.mapToTemplateDescription');

        return $this->getResponse()->setBody($grid->toHtml());
    }

    public function mapToTemplateDescriptionAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $templateId = $this->getRequest()->getParam('template_id');

        if (empty($productId) || empty($templateId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        $listingProduct->setData('template_description_id', $templateId)->save();
        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();

        return $this->getResponse()->setBody(json_encode(array('success' => true)));
    }

    public function setVariationThemeAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $variationTheme = $this->getRequest()->getParam('variation_theme', null);

        if (empty($productId) || is_null($variationTheme)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);
        $parent = $listingProduct->getChildObject()->getVariationManager()->getTypeModel();

        if ($parent->getChannelTheme() != $variationTheme) {
            $parent->setChannelTheme($variationTheme, false);
            $parent->setIsChannelThemeSetManually(true);
            $parent->getProcessor()->process();
        }

        $this->getResponse()->setBody(json_encode(array('success' => true)));
    }

    public function setMatchedAttributesAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $variationAttributes = $this->getRequest()->getParam('variation_attributes');

        if (empty($productId) || empty($variationAttributes)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $matchedAttributes = array_combine(
            $variationAttributes['magento_attributes'],
            $variationAttributes['amazon_attributes']
        );

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()
                                                                ->setMatchedAttributes($matchedAttributes);
        $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();

        foreach ($matchedAttributes as $magentoAttr => $amazonAttr) {
            Mage::helper('M2ePro/Component_Amazon_Vocabulary')
                ->addAttributes($listingProduct->getMarketplace()->getId(), $magentoAttr, $amazonAttr);
        }

        $this->getResponse()->setBody(json_encode(array('success' => true)));
    }

    public function createNewChildAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $newChildProductData = $this->getRequest()->getParam('new_child_product');
        $createNewAsin = (int)$this->getRequest()->getParam('create_new_asin', 0);

        if (empty($productId) || empty($newChildProductData)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Listing_Product $parentListingProduct */
        $parentListingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $parentListingProduct->getChildObject();

        $parentTypeModel = $parentAmazonListingProduct->getVariationManager()->getTypeModel();

        $productOptions = array_combine(
            $newChildProductData['product']['attributes'],
            $newChildProductData['product']['options']
        );

        if ($parentTypeModel->isProductsOptionsRemoved($productOptions)) {
            $parentTypeModel->restoreRemovedProductOptions($productOptions);
        }

        $channelOptions = array();
        $generalId = null;

        if (!$createNewAsin) {
            $channelOptions = array_combine(
                $newChildProductData['channel']['attributes'],
                $newChildProductData['channel']['options']
            );

            $generalId = $parentTypeModel->getChannelVariationGeneralId($channelOptions);
        }

        $parentTypeModel->createChildListingProduct(
            $productOptions, $channelOptions, $generalId
        );

        $parentTypeModel->getProcessor()->process();

        if (!$createNewAsin) {
            $matchedAttributes = $parentTypeModel->getMatchedAttributes();

            foreach ($matchedAttributes as $productAttribute => $channelAttribute) {
                Mage::helper('M2ePro/Component_Amazon_Vocabulary')->addOptions(
                    $parentListingProduct->getMarketplace()->getId(),
                    $productOptions[$productAttribute],
                    $channelOptions[$channelAttribute],
                    $channelAttribute
                );
            }
        }

        $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'msg'  => Mage::helper('M2ePro')->__('New Amazon Child Product was successfully created.')
        )));
    }

    //#############################################

    private function isExistInM2eProListings($listingProduct, $sku)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l'=>$listingTable),
            '`main_table`.`listing_id` = `l`.`id`',
            array()
        );

        $collection->addFieldToFilter('sku',$sku);
        $collection->addFieldToFilter('account_id',$listingProduct->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    private function isExistInOtherListings($listingProduct, $sku)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->addFieldToFilter('sku',$sku);
        $collection->addFieldToFilter('account_id',$listingProduct->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    private function getSkuInfo($listingProduct, $sku)
    {
        try {

            /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Amazon_Dispatcher */
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $response = $dispatcherObject->processVirtual(
                'product','search','asinBySkus',
                array(
                    'include_info' => true,
                    'only_realtime' => true,
                    'items' => array($sku)
                ),'items', $listingProduct->getAccount()->getId()
            );

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);

            return false;
        }

        return $response[$sku];
    }

    private function hasListingProductSku($productId)
    {
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

        $sku = $listingProduct->getSku();
        return !empty($sku);
    }

    private function setGeneralIdOwner($productId, $generalIdOwner)
    {
        $data = array('success' => true);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if ($generalIdOwner == Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES) {
            if (!$amazonListingProduct->isExistDescriptionTemplate()) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__(
                    'Description Policy with enabled ability to create new ASIN(s)/ISBN(s)
                     should be added in order for operation to be finished.'
                );

                return $data;
            }

            if (!$amazonListingProduct->getAmazonDescriptionTemplate()->isNewAsinAccepted()) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__(
                    'Description Policy with enabled ability to create new ASIN(s)/ISBN(s)
                     should be added in order for operation to be finished.'
                );

                return $data;
            }

            $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
            $detailsModel->setMarketplaceId($listingProduct->getListing()->getMarketplaceId());
            $themes = $detailsModel->getVariationThemes(
                $amazonListingProduct->getAmazonDescriptionTemplate()->getProductDataNick()
            );

            if (empty($themes)) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__(
                    'The Category chosen in the Description Policy does not support variations.'
                );

                return $data;
            }

            $productAttributes = $amazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getProductAttributes();

            $isCountEqual = false;
            foreach ($themes as $theme) {
                if (count($theme['attributes']) == count($productAttributes)) {
                    $isCountEqual = true;
                    break;
                }
            }

            if (!$isCountEqual) {
                $data['success'] = false;
                $data['msg'] = Mage::helper('M2ePro')->__('Number of attributes doesn’t match');

                return $data;
            }
        }

        $listingProduct->setData('is_general_id_owner', $generalIdOwner)->save();
        $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();

        return $data;
    }

    //#############################################
}