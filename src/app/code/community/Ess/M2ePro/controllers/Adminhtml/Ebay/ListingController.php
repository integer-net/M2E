<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_ListingController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/AutoComplete.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Settings/GridHandler.js')
            ->addJs('M2ePro/Ebay/Motor/SpecificHandler.js')
        ;

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_manageListings'))
             ->renderLayout();
    }

    //#############################################

    public function getListingTabAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $this->getResponse()->setBody(
            $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing')->toHtml()
        );
    }

    public function getListingOtherTabAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $this->getResponse()->setBody(
            $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other')->toHtml()
        );
    }

    public function getSearchTabAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $this->getResponse()->setBody(
            $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search')->toHtml()
        );
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function goToEbayAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        if (is_null($itemId) || is_null($accountId) || is_null($marketplaceId)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested eBay Item ID is not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        $accountMode = Mage::helper('M2ePro/Component_Ebay')->getObject('Account', $accountId)
            ->getChildObject()
            ->getMode();

        $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
            $itemId, $accountMode, $marketplaceId
        );

        $this->_redirectUrl($url);
    }

    //#############################################

    public function searchAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search'))
             ->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function viewAction()
    {
        if ((bool)$this->getRequest()->getParam('do_list', false)) {

            Mage::helper('M2ePro/Data_Session')->setValue(
                'products_ids_for_list',
                implode(',', Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids'))
            );

            return $this->_redirect('*/*/*', array(
                '_current'  => true,
                'do_list'   => NULL,
                'view_mode' => Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View::VIEW_MODE_EBAY
            ));
        }

        $id = $this->getRequest()->getParam('id');

        try {
            $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        $productAddIds = $model->getData('product_add_ids');
        $productAddIds = array_filter((array)json_decode($productAddIds,true));

        if (!empty($productAddIds)) {

            $this->_getSession()->addNotice(Mage::helper('M2ePro')->__(
                'Adding New Products action was not completed. Please, complete it.'
            ));

            return $this->_redirect('*/adminhtml_ebay_listing_productAdd',array('listing_id' => $id, 'step' => 2));
        }

        // Check listing lock item
        //----------------------------
        $lockItem = Mage::getModel(
            'M2ePro/Listing_LockItem', array('id' => $id, 'component' => Ess_M2ePro_Helper_Component_Ebay::NICK)
        );
        if ($lockItem->isExist()) {
            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__('The listing is locked by another process. Please try again later.')
            );
        }
        //----------------------------

        $this->_initAction();

        //------------------------------
        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/AttributeSetHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Ebay/Listing/AutoActionHandler.js')
            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Template/SwitcherHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js')
            ->addJs('M2ePro/Ebay/Template/PaymentHandler.js')
            ->addJs('M2ePro/Ebay/Template/ReturnHandler.js')
            ->addJs('M2ePro/Ebay/Template/ShippingHandler.js')
            ->addJs('M2ePro/Ebay/Template/SellingFormatHandler.js')
            ->addJs('M2ePro/Ebay/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Ebay/Template/SynchronizationHandler.js')
        ;

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        //------------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

        // Set rule model
        // ---------------------------
        $this->setRuleData('ebay_rule_view_listing');
        // ---------------------------

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view'))
             ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model);

         // Set rule model
        // ---------------------------
        $this->setRuleData('ebay_rule_view_listing');
        // ---------------------------

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view')->getGridHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function deleteAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to remove.'));
            $this->_redirect('*/*/index');
            return;
        }

        $deleted = $locked = 0;
        foreach ($ids as $id) {
            $template = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $template->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%s listing(s) were successfully deleted', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString = Mage::helper('M2ePro')->__('%s listing(s) have listed items and can not be deleted', $locked);
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_ebay_listing/index');
    }

    public function saveTitleAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $title = $this->getRequest()->getParam('title');

        if (is_null($listingId)) {
            return;
        }

        $model = Mage::getModel('M2ePro/Listing')->loadInstance((int)$listingId);
        $model->setTitle($title)->save();

        Mage::getModel('M2ePro/Listing_Log')->updateListingTitle($listingId, $title);
    }

    //#############################################

    public function getEstimatedFeesAction()
    {
        session_write_close();

        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        //------------------------------
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('listing_id', $listingId);
        $listingProductCollection->addFieldToFilter('status', array('in' => array(
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED,
            Ess_M2ePro_Model_Listing_Product::STATUS_SOLD,
            Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
        )));
        $listingProductCollection->setPageSize(3);

        if ($listingProductId) {
            $listingProductCollection->addFieldToFilter('id', $listingProductId);
        }

        $listingProductCollection->load();
        //------------------------------

        //------------------------------
        if ($listingProductCollection->count() == 0) {
            $this->getResponse()->setBody(json_encode(array('error' => true)));
            return;
        }
        //------------------------------

        $this->loadLayout();

        $fees = $errors = array();
        $sourceProduct = NULL;

        foreach ($listingProductCollection as $product) {
            $fees = array();
            $connector = new Ess_M2ePro_Model_Connector_Ebay_Item_List_Verify(array(), $product);

            try {
                $fees = $connector->process();
            } catch (Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }

            $listing->getChildObject()->increaseEstimatedFeesObtainAttemptCount();

            if (!empty($fees)) {
                $sourceProduct = $product;
                break;
            }

            if (!$listingProductId) {
                // this is obtaining of estimated fee for random product from listing
                $listing->getChildObject()->increaseEstimatedFeesObtainAttemptCount();
            } else {
                $currentErrors = $connector->getCustomMessages(Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR);

                if (count($currentErrors) > 0) {
                    $errors = $currentErrors;
                }
            }
        }

        //------------------------------
        if (empty($fees)) {
            if (empty($errors)) {
                $this->getResponse()->setBody(json_encode(array('error' => true)));
            } else {
                $errorsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_errors');
                $errorsBlock->setData('errors', $errors);

                $this->getResponse()->setBody(json_encode(array('html' => $errorsBlock->toHtml())));
            }
            return;
        }
        //------------------------------

        //------------------------------
        if ($listingProductId) {
            $details = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_details');
            $details->setData('fees', $fees);
            $details->setData('product_name', $sourceProduct->getMagentoProduct()->getName());

            $this->getResponse()->setBody(json_encode(array('html' => $details->toHtml())));
            return;
        }
        //------------------------------

        //------------------------------
        $listing->getChildObject()->setEstimatedFeesObtainAttemptCount(0);
        $listing->getChildObject()->setEstimatedFeesObtainRequired(false);
        $listing->getChildObject()->setEstimatedFees($fees);
        $listing->getChildObject()->setEstimatedFeesSourceProductName($sourceProduct->getMagentoProduct()->getName());
        $listing->save();
        //------------------------------

        $preview = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_fee_preview');
        $preview->setData('fees', $fees);
        $preview->setData('product_name', $sourceProduct->getMagentoProduct()->getName());

        $this->getResponse()->setBody(json_encode(array('html' => $preview->toHtml())));
    }

    //#############################################

    public function motorSpecificGridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_motor_specific_grid')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    public function updateMotorsSpecificsAttributesAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequest()->getParam('listing_product_ids', '');
        $epids = $this->getRequest()->getParam('epids', '');
        $overwrite = $this->getRequest()->getParam('overwrite', '') == 'yes';

        $listingProductIds = explode(',', $listingProductIds);
        $epids = explode(',', $epids);

        $motorsSpecificsAttribute = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/motor/','motors_specifics_attribute'
        );

        if (empty($motorsSpecificsAttribute)) {
            $message = Mage::helper('M2ePro')->__('Compatibility Attribute is not selected.');
            return $this->getResponse()->setBody(json_encode(array(
                'ok' => false,
                'message' => Mage::helper('M2ePro')->escapeJs($message)
            )));
        }

        if (!$listingId || !$listingProductIds || !$epids) {
            $message = Mage::helper('M2ePro')->__('Required parameters were not selected.');
            return $this->getResponse()->setBody(json_encode(array(
                'ok' => false,
                'message' => Mage::helper('M2ePro')->escapeJs($message)
            )));
        }

        try {
            Mage::getResourceModel('M2ePro/Ebay_Listing')
                ->updateMotorsSpecificsAttributesData($listingId, $listingProductIds, $epids, $overwrite);
        } catch (Exception $e) {
            return $this->getResponse()->setBody(json_encode(array(
                'ok' => false,
                'message' => Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__($e->getMessage()))
            )));
        }

        $message = Mage::helper('M2ePro')->__('ePIDs were saved in Compatibility Attribute.');
        return $this->getResponse()->setBody(json_encode(array(
            'ok' => true,
            'message' => Mage::helper('M2ePro')->escapeJs($message))
        ));
    }

    //#############################################

    public function getCategoryChooserHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequestIds();
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $internalData = array();

        //------------------------------
        $categoryTemplateIds  = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getTemplateCategoryIds(
            $listingProductIds
        );
        $internalData = array_merge(
            $internalData,
            Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getSameTemplatesData($categoryTemplateIds)
        );
        //------------------------------
        $otherCategoryTemplateIds = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getTemplateOtherCategoryIds(
            $listingProductIds
        );

        $internalData = array_merge(
            $internalData,
            Mage::helper('M2ePro/Component_Ebay_Category_Store')->getSameTemplatesData($otherCategoryTemplateIds)
        );
        //------------------------------

        $this->loadLayout();

        /* @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('chooser_main_container');
        $chooserBlock->setAccountId($listing->getAccountId());
        $chooserBlock->setMarketplaceId($listing->getMarketplaceId());
        $chooserBlock->setInternalData($internalData);

        // ---------------------------------------------
        $wrapper = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_view_settings_category_chooser_wrapper'
        );
        $wrapper->setChild('chooser', $chooserBlock);
        // ---------------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    public function getCategorySpecificHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequestIds();
        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $this->loadLayout();

        /* @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());
        $specific->setDivId('specific_main_container');
        $specific->setCategoryMode($categoryMode);
        $specific->setCategoryValue($categoryValue);

        $categoryWasChanged = false;

        //------------------------------
        /* @var $template Ess_M2ePro_Model_Ebay_Template_Category|null */
        $template = NULL;

        $templateIds = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')
            ->getTemplateCategoryIds($listingProductIds);
        if (count($templateIds) == 1 && !is_null($templateId = reset($templateIds))) {
            $template = Mage::helper('M2ePro')->getCachedObject(
                'Ebay_Template_Category', (int)$templateId, NULL, array('template')
            );
        }

        if (!$template) {
            $categoryWasChanged = true;
        } else {
            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY &&
                $template->getData('category_main_id') != $categoryValue) {
                $categoryWasChanged = true;
            }

            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY &&
                $template->getData('category_main_id') != $categoryValue) {
                $categoryWasChanged = true;
            }
        }

        if ($categoryWasChanged) {
            $templateData = array(
                'category_main_id' => 0,
                'category_main_mode' => $categoryMode,
                'category_main_attribute' => ''
            );

            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $templateData['category_main_id'] = $categoryValue;
            } else if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
                $templateData['category_main_attribute'] = $categoryValue;
            }

            $existingTemplates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()
                ->getItemsByPrimaryCategories(array($templateData));

            $template = reset($existingTemplates);
        }

        if ($template) {
            $specific->setInternalData($template->getData());
            $specific->setSelectedSpecifics($template->getSpecifics());
        }

        // ---------------------------------------------
        $wrapper = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_view_settings_category_specific_wrapper'
        );
        $wrapper->setChild('specific', $specific);
        // ---------------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    public function saveCategoryTemplateAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return;
        }

        if (!isset($post['template_category_data'])) {
            return;
        }

        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequestIds();
        $categoryTemplateData = $post['template_category_data'];
        $categoryTemplateData = json_decode($categoryTemplateData, true);
        //------------------------------

        //------------------------------
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        //------------------------------
        Mage::helper('M2ePro/Component_Ebay_Category')->fillCategoriesPaths($categoryTemplateData, $listing);

        $builderData = $categoryTemplateData;
        $builderData['account_id'] = $listing->getAccountId();
        $builderData['marketplace_id'] = $listing->getMarketplaceId();

        //------------------------------
        $builder = Mage::getModel('M2ePro/Ebay_Template_Category_Builder');
        $categoryTemplate = $builder->build($builderData);
        //------------------------------
        $builder = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder');
        $otherCategoryTemplate = $builder->build($builderData);
        //------------------------------

        $this->assignTemplatesToProducts($categoryTemplate->getId(),$otherCategoryTemplate->getId(),$listingProductIds);
    }

    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select products';
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Item_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return json_encode(array('result'=>'error','action_id'=>$actionId));
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return json_encode(array('result'=>'warning','action_id'=>$actionId));
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            return json_encode(array('result'=>'success','action_id'=>$actionId));
        }

        return json_encode(array('result'=>'error','action_id'=>$actionId));
    }

    //---------------------------------------------

    public function runListProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_LIST)
        );
    }

    public function runReviseProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE)
        );
    }

    public function runRelistProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_RELIST)
        );
    }

    public function runStopProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_STOP)
        );
    }

    public function runStopAndRemoveProductsAction()
    {
        return $this->getResponse()->setBody($this->processConnector(
            Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_STOP, array('remove' => true)
        ));
    }

    //#############################################

    protected function setRuleData($prefix)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Ebay_Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix,
                'store_id' => $storeId,
            )
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                $prefix, $ruleModel->getSerializedFromPost($this->getRequest()->getPost())
            );
        } elseif (!is_null($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('rule_model', $ruleModel);
    }

    //#############################################

    public function reviewAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');

        $ids = Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids');

        if (empty($ids) && !$this->getRequest()->getParam('disable_list')) {
            return $this->_redirect('*/*/view', array('id' => $listingId));
        }

        $data = array(
            'products_count'=>count($ids)
        );

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Listing Review'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_review', '', $data))
             ->renderLayout();
    }

    //#############################################

    private function assignTemplatesToProducts($categoryTemplateId, $otherCategoryTemplateId, $productsIds)
    {
        if (empty($productsIds)) {
            return;
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $productsIds));
        //------------------------------

        if ($collection->getSize() == 0) {
            return;
        }

        $transaction = Mage::getModel('core/resource_transaction');

        $snapshots = array();

        try {
            foreach ($collection->getItems() as $listingProduct) {
                $snapshots[$listingProduct->getId()] = $listingProduct->getChildObject()->getDataSnapshot();

                $listingProduct->setData('template_category_id', $categoryTemplateId);
                $listingProduct->setData('template_other_category_id', $otherCategoryTemplateId);

                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (Exception $e) {
            $snapshots = false;
            $transaction->rollback();
        }

        if (!$snapshots) {
            return;
        }

        foreach ($collection->getItems() as $listingProduct) {
            $listingProduct->getChildObject()->setSynchStatusNeed(
                $listingProduct->getChildObject()->getDataSnapshot(),
                $snapshots[$listingProduct->getId()]
            );
        }
    }

    //#############################################
}
