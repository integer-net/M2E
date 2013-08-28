<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_ListingController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/listings')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
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
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/listings/listing');
    }

    //#############################################

    public function indexAction()
    {
        /*!(bool)Mage::getModel('M2ePro/Template_SellingFormat')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one selling format template first.')
        );

        !(bool)Mage::getModel('M2ePro/Template_Description')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one description template first.')
        );

        !(bool)Mage::getModel('M2ePro/Template_General')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one general template first.')
        );

        !(bool)Mage::getModel('M2ePro/Template_Synchronization')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one synchronization template first.')
        );*/

        $this->_initAction();

        // Video tutorial
        //-------------
        if ((Mage::helper('M2ePro/Component_Amazon')->isActive() &&
                    !Mage::helper('M2ePro/Module')->getConfig()
                                    ->getGroupValue('/cache/amazon/listing/', 'tutorial_shown')) ||

            (Mage::helper('M2ePro/Component_Buy')->isActive() &&
                    !Mage::helper('M2ePro/Module')->getConfig()
                                    ->getGroupValue('/cache/buy/listing/', 'tutorial_shown')) ||

            (Mage::helper('M2ePro/Component_Play')->isActive() &&
                !Mage::helper('M2ePro/Module')->getConfig()
                    ->getGroupValue('/cache/play/listing/', 'tutorial_shown'))) {

            $this->_initPopUp();
            $this->getLayout()->getBlock('head')->addJs('M2ePro/VideoTutorialHandler.js');
        }
        //-------------

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listing'))
             ->renderLayout();
    }

    //#############################################

    public function searchAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listing_search'))
             ->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function moveToListingGridAction()
    {
        Mage::helper('M2ePro')->setGlobalValue(
            'componentMode', $this->getRequest()->getParam('componentMode')
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'accountId', $this->getRequest()->getParam('accountId')
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'marketplaceId', $this->getRequest()->getParam('marketplaceId')
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'attrSetId', json_decode($this->getRequest()->getParam('attrSetId'))
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'ignoreListings', json_decode($this->getRequest()->getParam('ignoreListings'))
        );

        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_moveToListing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //---------------------------------------------

    public function getFailedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_moveToListing_failedProducts');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function failedProductsGridAction()
    {
        $block = $this->loadLayout()->getLayout()
                      ->createBlock('M2ePro/adminhtml_listing_moveToListing_failedProducts_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

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
            ->join( array('tg'=>Mage::getSingleton('core/resource')->getTableName('m2epro_template_general')),
                    '`listing`.`template_general_id` = `tg`.`id`' )
            ->join( array('cpe'=>Mage::getSingleton('core/resource')->getTableName('catalog_product_entity')),
                    '`main_table`.`product_id` = `cpe`.`entity_id`' )
            ->group(array('tg.account_id','tg.marketplace_id','cpe.attribute_set_id'))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('marketplace_id', 'account_id'), 'tg')
            ->columns('attribute_set_id', 'cpe')
            ->query()
            ->fetchAll();

        $attributeSets = array();
        foreach ($tempData as $data) {
            $attributeSets[] = $data['attribute_set_id'];
        }

        exit(json_encode(array(
            'accountId' => $tempData[0]['account_id'],
            'marketplaceId' => $tempData[0]['marketplace_id'],
            'attrSetId' => $attributeSets
        )));
    }

    //#############################################

    public function goToSellingFormatTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect(
            "*/adminhtml_{$model->getComponentMode()}_template_sellingFormat/edit",
            array(
                'id' => $model->getData('template_selling_format_id'),
                'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );
    }

    public function goToGeneralTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect(
            "*/adminhtml_{$model->getComponentMode()}_template_general/edit",
            array(
                'id' => $model->getData('template_general_id'),
                'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );
    }

    public function goToDescriptionTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect(
            "*/adminhtml_{$model->getComponentMode()}_template_description/edit",
            array(
                'id' => $model->getData('template_description_id'),
                'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );
    }

    public function goToSynchronizationTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $this->_redirect(
            "*/adminhtml_{$model->getComponentMode()}_template_synchronization/edit",
            array(
                'id' => $model->getData('template_synchronization_id'),
                'back'=>Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );
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
            Mage::getModel('M2ePro/Listing_Log')->clearMessages($id);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing(s) log was successfully cleaned.'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list'));
    }

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
            exit('locked');
        }

        exit('unlocked');
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

        exit();
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

        exit();
    }

    //---------------------------------------------

    public function getErrorsSummaryAction()
    {
        $blockParams = array(
            'action_ids' => $this->getRequest()->getParam('action_ids'),
            'table_name' => Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable(),
            'type_log'   => 'listing'
        );
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_log_errorsSummary','',$blockParams);
        exit($block->toHtml());
    }

    //#############################################

    public function duplicateProductsAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductsIds = $this->getRequest()->getParam('ids');
        $listingProductsIds = explode(',',$listingProductsIds);
        $listingProductsIds = array_filter($listingProductsIds);

        if (empty($listingProductsIds) || !$component) {
            exit(json_encode(array(
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

        exit(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('The items were successfully duplicated.')
        )));
    }

    //#############################################

    public function getVariationEditPopupAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            exit(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Product must be specified.')
            )));
        }

        $variationEditBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_product_variation_edit','',
            array(
                'component' => $component,
                'listing_product_id' => $listingProductId,
                'template' => 'M2ePro/adminhtml_listing_product_variation_edit'
            )
        );

        exit(json_encode(array(
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
            exit(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Product must be specified.')
            )));
        }

        $variationManageBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_product_variation_manage','',
            array(
                'component' => $component,
                'listing_product_id' => $listingProductId,
                'template' => 'M2ePro/adminhtml_listing_product_variation_manage'
            )
        );

        exit(json_encode(array(
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
            exit(json_encode(array(
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
            exit(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Only 1 variation must leave.')
            )));
        }

        $listingProduct->getChildObject()->unsetMatchedVariation();
        $listingProduct->getChildObject()->setMatchedVariation(reset($magentoVariations));

        exit(json_encode(array(
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
            exit(json_encode(array(
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

        if ($listingProduct->getChildObject()->isVariationProduct() &&
            $listingProduct->getChildObject()->isVariationMatched()) {
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
                exit(json_encode(array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Only 1 variation must leave.')
                )));
            }

            $listingProduct->getChildObject()->unsetMatchedVariation();
            $listingProduct->getChildObject()->setMatchedVariation(reset($tempMagentoVariations));
        }

        exit(json_encode(array(
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
            exit(json_encode(array(
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
            exit(json_encode(array(
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
            if (!$listingProduct->getChildObject()->isVariationProduct() ||
                !$listingProduct->getChildObject()->isVariationMatched()) {
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

        exit(json_encode(array(
            'type' => 'success',
            'text' => array_values($magentoVariations)
        )));

    }

    //#############################################
}