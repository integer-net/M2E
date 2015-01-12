<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Amazon Listings'));

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
            ->addJs('M2ePro/AttributeSetHandler.js')
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

            ->addJs('M2ePro/TemplateHandler.js')
            ->addJs('M2ePro/Common/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Common/Listing/Category/Handler.js')
            ->addJs('M2ePro/Common/Listing/AddListingHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/ProductsFilterHandler.js')

            ->addJs('M2ePro/Common/Listing/Product/VariationHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings/listing');
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing');
        $block->enableAmazonTab();

        $this->getResponse()->setBody($block->getAmazonTabHtml());
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function searchAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_search'))
            ->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function createListingAction()
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue('temp_data');

        $categoriesAddAction = $this->getRequest()->getParam('categories_add_action');
        $categoriesDeleteAction = $this->getRequest()->getParam('categories_delete_action');

        !empty($categoriesAddAction) && $sessionData['categories_add_action'] = $categoriesAddAction;
        !empty($categoriesDeleteAction) && $sessionData['categories_delete_action'] = $categoriesDeleteAction;
        //---------------

        // Add new Listing
        //---------------
        $listing = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')
            ->addData($sessionData)
            ->save();
        //---------------

        if (!is_array($sessionData['attribute_sets'])) {
            $sessionData['attribute_sets'] = explode(',', $sessionData['attribute_sets']);
        }
        foreach ($sessionData['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_LISTING,
                'object_id' => (int)$listing->getId(),
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

        //--------------------

        $categories = $this->getRequest()->getParam('categories');
        $sessionCategories = Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories');

        if (!empty($categories) || !empty($sessionCategories)) {

            // Get selected_categories param
            //---------------
            if (!empty($categories)) {
                $categoriesIds = explode(',',$categories);
                $categoriesIds = array_unique($categoriesIds);
            } else {
                $categoriesIds = $sessionCategories;
            }
            //---------------

            // Save selected categories
            //---------------
            foreach ($categoriesIds as $categoryId) {
                Mage::getModel('M2ePro/Listing_Category')
                    ->setData(array('listing_id'=>$listing->getId(),'category_id'=>$categoryId))
                    ->save();
            }
            //---------------
        }

        // Set message to log
        //---------------
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($listing->getComponentMode());
        $tempLog->addListingMessage(
            $listing->getId(),
            Ess_M2ePro_Helper_Data::INITIATOR_USER,
            NULL,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            // M2ePro_TRANSLATIONS
            // Listing was successfully added
            'Listing was successfully added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

        //---------------

        $isEmptyListing = $this->getRequest()->getParam('empty_listing');
        if ($isEmptyListing == 1) {
            if ($this->getRequest()->getParam('back') == 'list') {
                $backUrl = $this->getUrl('*/adminhtml_common_listing/index', array('tab' => 'amazon'));
            } else {
                $backUrl = $this->getUrl('*/*/view', array('id' => $listing->getId()));
            }

            return $this->getResponse()->setBody($backUrl);
        }

        //---------------

        return $this->getResponse()->setBody($listing->getId());
    }

    public function addProductsAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$listingId);

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);

        $listingProductIds = array();
        if (count($productsIds) > 0) {
            foreach ($productsIds as $productId) {
                if ($productId == '') {
                    continue;
                }

                $tempResult = $listing->addProduct($productId);
                if ($tempResult instanceof Ess_M2ePro_Model_Listing_Product) {
                    $listingProductIds[] = $tempResult->getId();
                }
            }
        }

        $tempProducts = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');
        $tempProducts = array_merge((array)$tempProducts, $listingProductIds);
        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', $tempProducts);

        $isLastPart = $this->getRequest()->getParam('is_last_part');
        if ($isLastPart == 'yes') {
            if ($this->getRequest()->getParam('do_list') == 'yes') {
                $tempProducts = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');
                Mage::helper('M2ePro/Data_Session')->setValue('products_ids_for_list', implode(',',$tempProducts));
            }

            Mage::helper('M2ePro/Data_Session')->setValue('temp_data', array());
            Mage::helper('M2ePro/Data_Session')->setValue('temp_listing_categories', array());
            Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());

            if ($this->getRequest()->getParam('back') == 'list') {
                $backUrl = $this->getUrl('*/adminhtml_common_listing/index', array('tab' => 'amazon'));
            } else {
                $backUrl = $this->getUrl('*/*/view', array('id' => $listingId));
            }

            $response = array('redirect' => $backUrl);
            return $this->getResponse()->setBody(json_encode($response));
        }

        $response = array('redirect' => '');
        return $this->getResponse()->setBody(json_encode($response));
    }

    public function getProductsFromCategoriesAction()
    {
        $hideProductsOthersListings = (bool)$this->getRequest()->getParam('hide_products_others_listings', true);
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$listingId);

        $categories = $this->getRequest()->getParam('categories');
        $categoriesIds = explode(',', $categories);
        $categoriesIds = array_unique($categoriesIds);

        $categoriesSave = $this->getRequest()->getParam('categories_save');
        if ($listing->isSourceProducts()) {
            $categoriesSave = 0;
        }

        $oldCategories = $listing->getCategories();
        $oldCategoriesIds = array();
        foreach ($oldCategories as $oldCategory) {
            $oldCategoriesIds[] = $oldCategory['category_id'];
        }

        $products = array();
        foreach ($categoriesIds as $categoryId) {
            if ($categoriesSave && !in_array($categoryId, $oldCategoriesIds)) {
                Mage::getModel('M2ePro/Listing_Category')
                    ->setData(array('listing_id'=>$listing->getId(),'category_id'=>$categoryId))
                    ->save();
            }

            $tempProducts = $listing->getProductsFromCategory($categoryId,$hideProductsOthersListings);
            !empty($tempProducts) && $products = array_merge($products, $tempProducts);
        }

        if (!empty($products)) {
            echo implode(',', $products);
        }
    }

    //#############################################

    public function addAction()
    {
        // Get step param
        //----------------------------
        $step = $this->getRequest()->getParam('step');

        if (is_null($step)) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // Switch step param
        //----------------------------
        switch ($step) {
            case '1':
                $this->addStepOne();
                break;
            case '2':
                $this->addStepTwo();
                break;
            case '3':
                $this->addStepThree();
                break;
            case '4':
                $this->addStepFour();
                break;
            case '5':
                $this->addStepFive();
                break;
            default:
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                break;
        }
        //----------------------------
    }

    public function addStepOne()
    {
        // Check clear param
        //----------------------------
        $clearAction = $this->getRequest()->getParam('clear');

        if (!is_null($clearAction)) {
            if ($clearAction == 'yes') {
                Mage::helper('M2ePro/Data_Session')->setValue('temp_data', array());
                Mage::helper('M2ePro/Data_Session')->setValue('temp_listing_categories', array());
                $this->_redirect('*/*/add',array('step'=>'1'));
                return;
            } else {
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                return;
            }
        }
        //----------------------------

        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) ||
            is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories'))) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $temp = array(
                'title' => strip_tags($post['title']),
                'attribute_sets' => $post['attribute_sets'],

                'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK,

                'template_selling_format_id'    => $post['template_selling_format_id'],
                'template_selling_format_title' => Mage::helper('M2ePro/Component_Amazon')
                    ->getCachedObject('Template_SellingFormat',
                                      (int)$post['template_selling_format_id'], NULL,
                                      array('template'))
                    ->getTitle(),
                'template_synchronization_id'    => $post['template_synchronization_id'],
                'template_synchronization_title' => Mage::helper('M2ePro/Component_Amazon')
                    ->getCachedObject('Template_Synchronization',
                                      (int)$post['template_synchronization_id'], NULL,
                                      array('template'))
                    ->getTitle()
            );

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue('temp_data');
            is_null($sessionData) && $sessionData = array();

            Mage::helper('M2ePro/Data_Session')->setValue('temp_data', array_merge($sessionData, $temp));

            $this->_redirect('*/*/add',array('step'=>'2'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data', Mage::helper('M2ePro/Data_Session')->getValue('temp_data')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_listing_categories', Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories')
        );

        Mage::helper('M2ePro/Data_Session')->setValue('temp_listing_categories', array());

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_stepOne'))
            ->renderLayout();
    }

    public function addStepTwo()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) ||
            count(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories'))) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $accountObj = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account',(int)$post['account_id']);

            $temp = array(
                'account_id' => $post['account_id'],
                'marketplace_id' => (int)$accountObj->getMarketplaceId(),
                'sku_mode' => $post['sku_mode'],
                'sku_custom_attribute' => $post['sku_custom_attribute'],
                'generate_sku_mode' => $post['generate_sku_mode'],
                'general_id_mode' => $post['general_id_mode'],
                'general_id_custom_attribute' => $post['general_id_custom_attribute'],
                'worldwide_id_mode' => $post['worldwide_id_mode'],
                'worldwide_id_custom_attribute' => $post['worldwide_id_custom_attribute'],
                'search_by_magento_title_mode' => $post['search_by_magento_title_mode'],
                'condition_mode' => $post['condition_mode'],
                'condition_value' => $post['condition_value'],
                'condition_custom_attribute' => $post['condition_custom_attribute'],
                'condition_note_mode' => $post['condition_note_mode'],
                'condition_note_value' => $post['condition_note_value'],
                'condition_note_custom_attribute' => $post['condition_note_custom_attribute'],
                'handling_time_mode' => $post['handling_time_mode'],
                'handling_time_value' => $post['handling_time_value'],
                'handling_time_custom_attribute' => $post['handling_time_custom_attribute'],
                'restock_date_mode' => $post['restock_date_mode'],
                'restock_date_value' => $post['restock_date_value'],
                'restock_date_custom_attribute' => $post['restock_date_custom_attribute']
            );

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue('temp_data');
            Mage::helper('M2ePro/Data_Session')->setValue('temp_data', array_merge($sessionData, $temp));

            $this->_redirect('*/*/add',array('step'=>'3'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data', Mage::helper('M2ePro/Data_Session')->getValue('temp_data')
        );

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_stepTwo'))
            ->renderLayout();
    }

    public function addStepThree()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) ||
            count(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories'))) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $temp = array(
                'store_id' => $post['store_id'],
                'source_products' => $post['source_products'],
            );

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue('temp_data');
            is_null($sessionData) && $sessionData = array();

            Mage::helper('M2ePro/Data_Session')->setValue('temp_data', array_merge($sessionData, $temp));

            $this->_redirect('*/*/add',array('step'=>'4'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data', Mage::helper('M2ePro/Data_Session')->getValue('temp_data')
        );

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_stepThree'))
            ->renderLayout();
    }

    public function addStepFour()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) ||
            count(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories'))) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // Get remember param
        //----------------------------
        $rememberCategories = $this->getRequest()->getParam('remember_categories');

        if (!is_null($rememberCategories)) {
            if ($rememberCategories == 'yes') {

                // Get selected_categories param
                //---------------
                $selectedCategoriesIds = array();

                $selectedCategories = $this->getRequest()->getParam('selected_categories');
                if (!is_null($selectedCategories)) {
                    $selectedCategoriesIds = explode(',',$selectedCategories);
                }
                $selectedCategoriesIds = array_unique($selectedCategoriesIds);
                //---------------

                // Save selected categories
                //---------------
                $m2eProData = Mage::helper('M2ePro/Data_Session')->getValue('temp_data');
                $m2eProData['categories_add_action'] = $this->getRequest()->getParam('categories_add_action');
                $m2eProData['categories_delete_action'] = $this->getRequest()->getParam('categories_delete_action');
                Mage::helper('M2ePro/Data_Session')->setValue('temp_data', $m2eProData);
                Mage::helper('M2ePro/Data_Session')->setValue('temp_listing_categories', $selectedCategoriesIds);
                //---------------

                // Goto step four
                //---------------
                $this->_redirect('*/*/add',array('step'=>'5'));
                //---------------

                return;

            } else {
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                return;
            }
        }
        //----------------------------

        //----------------------------
        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data', Mage::helper('M2ePro/Data_Session')->getValue('temp_data')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_listing_categories', Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories')
        );

        Mage::helper('M2ePro/Data_Session')->setValue('temp_listing_categories', array());

        // Load layout and start render
        //----------------------------
        $this->_initAction();

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('temp_data');
        if ($temp['source_products'] == Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CUSTOM) {
            $blockContent = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_amazon_listing_add_stepFourProduct'
            );
        } else if ($temp['source_products'] == Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CATEGORIES) {
            $blockContent = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_amazon_listing_add_stepFourCategory'
            );
        } else {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }

        // Set rule model
        // ---------------------------
        $this->setRuleData('amazon_rule_add_listing_product');
        // ---------------------------

        // Set Hide Products In Other Listings
        // ---------------------------
        $prefix = $this->getHideProductsInOtherListingsPrefix();
        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
        // ---------------------------

        $this->_addContent($blockContent);

        $this->renderLayout();
        //----------------------------
    }

    public function addStepFive()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) ||
            count(Mage::helper('M2ePro/Data_Session')->getValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories')) ||
            count(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories')) == 0) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data', Mage::helper('M2ePro/Data_Session')->getValue('temp_data')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_listing_categories', Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories')
        );

        // Set rule model
        // ---------------------------
        $this->setRuleData('amazon_rule_add_listing_product_categories');
        // ---------------------------

        // Set Hide Products In Other Listings
        // ---------------------------
        $prefix = $this->getHideProductsInOtherListingsPrefix();
        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
        // ---------------------------

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_add_StepFive'))
            ->renderLayout();
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        /* @var $model Ess_M2ePro_Model_Listing */

        try {
            $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        // Check listing lock object
        //----------------------------
        if ($model->isLockedObject('products_in_action')) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__('Some Amazon request(s) are being processed now.')
            );
        }
        //----------------------------

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());
        Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $model->getMarketplaceId());

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());
        Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id', $model->getMarketplaceId());

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        $additionalData = array(
            'template_selling_format_title'  => Mage::helper('M2ePro/Component_Amazon')
                ->getCachedObject('Template_SellingFormat',
                                  $model->getData('template_selling_format_id'), NULL,
                                  array('template'))
                ->getTitle(),
            'template_synchronization_title' => Mage::helper('M2ePro/Component_Amazon')
                ->getCachedObject('Template_Synchronization',
                                  $model->getData('template_synchronization_id'), NULL,
                                  array('template'))
                ->getTitle(),
            'account_id' => $model->getData('account_id'),
            'marketplace_id' => $model->getData('marketplace_id'),
            'sku_mode' => $model->getData('sku_mode'),
            'sku_custom_attribute' => $model->getData('sku_custom_attribute'),
            'generate_sku_mode' => $model->getData('generate_sku_mode'),
            'general_id_mode' => $model->getData('general_id_mode'),
            'general_id_custom_attribute' => $model->getData('general_id_custom_attribute'),
            'worldwide_id_mode' => $model->getData('worldwide_id_mode'),
            'worldwide_id_custom_attribute' => $model->getData('worldwide_id_custom_attribute'),
            'search_by_magento_title_mode' => $model->getData('search_by_magento_title_mode'),
            'attribute_sets' => $model->getAttributeSetsIds(),
            'condition_mode' => $model->getData('condition_mode'),
            'condition_value' => $model->getData('condition_value'),
            'condition_custom_attribute' => $model->getData('condition_custom_attribute'),
            'condition_note_mode' => $model->getData('condition_note_mode'),
            'condition_note_value' => $model->getData('condition_note_value'),
            'condition_note_custom_attribute' => $model->getData('condition_note_custom_attribute'),
            'handling_time_mode' => $model->getData('handling_time_mode'),
            'handling_time_value' => $model->getData('handling_time_value'),
            'handling_time_custom_attribute' => $model->getData('handling_time_custom_attribute'),
            'restock_date_mode' => $model->getData('restock_date_mode'),
            'restock_date_value' => $model->getData('restock_date_value'),
            'restock_date_custom_attribute' => $model->getData('restock_date_custom_attribute')
        );

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', array_merge($model->getData(), $additionalData));

        $this->_initAction();
        $this->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_edit_tabs'));
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        $oldData = $model->getDataSnapshot();

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        // tab: settings
        //--------------------
        $keys = array(
            'title',
            'template_selling_format_id',
            'template_synchronization_id',

            'categories_add_action',
            'categories_delete_action'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        $model->addData($data)->save();

        // Delete old categories
        //---------------
        $oldCategories = (array)$model->getCategories(true);
        foreach ($oldCategories as $oldCategory) {
            $oldCategory->deleteInstance();
        }

        // Save selected categories
        //---------------
        if (!empty($post['selected_categories'])) {
            $categoriesIds = explode(',',$post['selected_categories']);
            $categoriesIds = array_unique($categoriesIds);

            foreach ($categoriesIds as $categoryId) {
                Mage::getModel('M2ePro/Listing_Category')
                    ->setData(array('listing_id'=> $id,'category_id'=>(int)$categoryId))
                    ->save();
            }
        }
        //---------------

        $templateData = array();

        // tab: channel settings
        //---------------
        $keys = array(
            'account_id',
            'marketplace_id',

            'sku_mode',
            'sku_custom_attribute',
            'generate_sku_mode',

            'general_id_mode',
            'general_id_custom_attribute',
            'worldwide_id_mode',
            'worldwide_id_custom_attribute',

            'search_by_magento_title_mode',

            'condition_mode',
            'condition_value',
            'condition_custom_attribute',

            'condition_note_mode',
            'condition_note_value',
            'condition_note_custom_attribute',

            'handling_time_mode',
            'handling_time_value',
            'handling_time_custom_attribute',

            'restock_date_mode',
            'restock_date_value',
            'restock_date_custom_attribute'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $templateData[$key] = $post[$key];
            }
        }

        if ($templateData['restock_date_value'] === '') {
            $templateData['restock_date_value'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        } else {
            $templateData['restock_date_value'] = Mage::helper('M2ePro')
                                                    ->timezoneDateToGmt($templateData['restock_date_value']);
        }
        //---------------

        $model->addData($templateData)->save();
        $newData = $model->getDataSnapshot();

        $model->getChildObject()->setSynchStatusNeed($newData,$oldData);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing was successfully saved.'));

        Mage::getModel('M2ePro/Listing_Log')->updateListingTitle($id,$data['title']);

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

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
            $listing = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);
            if ($listing->isLocked()) {
                $locked++;
            } else {
                $listing->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%amount% listing(s) were successfully deleted', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString = Mage::helper('M2ePro')->__(
            '%amount% listing(s) have listed items and can not be deleted', $locked
        );
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_common_listing/index');
    }

    //#############################################

    public function productAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist..'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        // Get save param
        //----------------------------
        if ($this->getRequest()->isPost()) {

            // Get selected_products param
            //---------------
            $selectedProductsIds = array();

            $selectedProducts = $this->getRequest()->getParam('selected_products');
            if (!is_null($selectedProducts)) {
                $selectedProductsIds = explode(',',$selectedProducts);
            }
            $selectedProductsIds = array_unique($selectedProductsIds);
            //---------------

            // Add products
            //---------------
            $idsToListAction = array();

            foreach ($selectedProductsIds as $productId) {
                $productInstance = $model->addProduct($productId);
                if ($productInstance instanceof Ess_M2ePro_Model_Listing_Product) {
                    $idsToListAction[] = $productInstance->getId();
                }
            }
            //---------------

            $redirectUrl = Mage::helper('M2ePro')->getBackUrl('list');

            if ($this->getRequest()->getParam('do_list')) {
                $redirectUrl = $this->getUrl('*/*/view', array('id'=>$id));
                Mage::helper('M2ePro/Data_Session')->setValue('products_ids_for_list', implode(',',$idsToListAction));
            }

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The products were added to listing.'));
            $this->_redirectUrl($redirectUrl);
            return;
        }
        //----------------------------

        $tempData = $model->getData();
        $tempData['attribute_sets'] = $model->getAttributeSetsIds();
        $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Amazon::NICK;
        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $tempData);
        Mage::helper('M2ePro/Data_Global')->setValue('temp_listing_categories', array());

        // Set rule model
        // ---------------------------
        $this->setRuleData('amazon_rule_listing_product');
        // ---------------------------

        // Set Hide Products In Other Listings
        // ---------------------------
        $prefix = $this->getHideProductsInOtherListingsPrefix();
        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
        // ---------------------------

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_product'))
            ->renderLayout();
    }

    public function categoryProductAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist..'));
            return $this->_redirect('*/adminhtml_common_listing/index');
        }

        $categories = $this->getRequest()->getParam('selected_categories');
        $categoriesIds = explode(',', $categories);
        $categoriesIds = array_unique($categoriesIds);
        $categoriesIds = array_filter($categoriesIds);

        $categoriesSave = $this->getRequest()->getParam('save_categories',1);

        $addProducts = $this->getRequest()->getParam('add_products');
        if (!is_null($addProducts)) {

            if ($categoriesSave && $model->isSourceCategories()) {
                $oldCategories = $model->getCategories();

                $oldCategoriesIds = array();
                foreach ($oldCategories as $oldCategory) {
                    $oldCategoriesIds[] = $oldCategory['category_id'];
                }

                foreach ($categoriesIds as $categoryId) {
                    if (!in_array($categoryId, $oldCategoriesIds)) {
                        Mage::getModel('M2ePro/Listing_Category')
                            ->setData(array('listing_id'=>$model->getId(),'category_id'=>$categoryId))
                            ->save();
                    }
                }
            }

            Mage::helper('M2ePro/Data_Global')->setValue('temp_listing_categories', $categoriesIds);
            $tempData = $model->getData();
            $tempData['attribute_sets'] = $model->getAttributeSetsIds();
            $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Amazon::NICK;
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $tempData);

            // Set rule model
            // ---------------------------
            $this->setRuleData('amazon_rule_listing_product_categories');
            // ---------------------------

            // Set Hide Products In Other Listings
            // ---------------------------
            $prefix = $this->getHideProductsInOtherListingsPrefix();
            Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
            // ---------------------------

            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_listing_product'));
            $this->renderLayout();

            return;
        }

        $listingData = array();

        $listingData['id'] = $model->getId();
        $listingData['title'] = $model->getTitle();

        $attributeSets = $model->getAttributeSets();
        $attributeSetsIds = array();
        foreach ($attributeSets as $attributeSet) {
            $attributeSetsIds[] = $attributeSet->getAttributeSetId();
        }
        $listingData['attribute_sets'] = $attributeSetsIds;
        $listingData['component_mode'] = Ess_M2ePro_Helper_Component_Amazon::NICK;

        $listingData['store_id'] = $model->getStoreId();
        $listingData['is_source_categories'] = $model->isSourceCategories();

        $listingData['save_categories'] = $categoriesSave;

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listingData);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_listing_categories', $categoriesIds);

        $this->_initAction();

        $blockContent = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_amazon_listing_product_category'
        );

        $this->_addContent($blockContent);

        $this->renderLayout();
    }

    public function productGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!is_null($id)) {
            if (!is_null($model->getId())) {
                $tempData = $model->getData();
                $tempData['attribute_sets'] = $model->getAttributeSetsIds();
                $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Amazon::NICK;
                Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $tempData);
            } else {
                Mage::helper('M2ePro/Data_Global')->setValue('temp_data', array());
            }

            $categories = $this->getRequest()->getParam('selected_categories');
            $categoriesIds = explode(',', $categories);
            $categoriesIds = array_unique($categoriesIds);
            $categoriesIds = array_filter($categoriesIds);

            Mage::helper('M2ePro/Data_Global')->setValue('temp_listing_categories',$categoriesIds);
        } else {
            if (!is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_data'))) {
                Mage::helper('M2ePro/Data_Global')->setValue(
                    'temp_data', Mage::helper('M2ePro/Data_Session')->getValue('temp_data')
                );
            } else {
                Mage::helper('M2ePro/Data_Global')->setValue('temp_data', array());
            }
            if (!is_null(Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories'))) {
                Mage::helper('M2ePro/Data_Global')->setValue(
                    'temp_listing_categories', Mage::helper('M2ePro/Data_Session')->getValue('temp_listing_categories')
                );
            } else {
                Mage::helper('M2ePro/Data_Global')->setValue('temp_listing_categories', array());
            }
        }

        $rulePrefix = 'amazon_rule_';
        $rulePrefix .= is_null($id) ? 'add_' : '';
        $rulePrefix .= 'listing_product';
        $rulePrefix .= count(Mage::helper('M2ePro/Data_Global')->getValue('temp_listing_categories')) > 0
            ? '_categories' : '';

        // Set rule model
        // ------------------------------------
        $this->setRuleData($rulePrefix);
        // ------------------------------------

        // Set Hide Products In Other Listings
        // ---------------------------
        $prefix = $this->getHideProductsInOtherListingsPrefix();

        $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
        Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);
        // ---------------------------

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_product_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select products';
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Product_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        $listingProductObject = Mage::helper('M2ePro/Component_Amazon')
            ->getModel('Listing_Product')
            ->load($listingsProductsIds[0]);

        $isProcessingItems = false;
        if (!is_null($listingProductObject->getId())) {
            $isProcessingItems = (bool)$listingProductObject->getListing()
                ->isLockedObject('products_in_action');
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            return json_encode(
                array('result'=>'error','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {
            return json_encode(
                array('result'=>'warning','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            return json_encode(
                array('result'=>'success','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        return json_encode(
            array('result'=>'error','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
        );
    }

    //---------------------------------------------

    public function runListProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_LIST)
        );
    }

    public function runReviseProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE)
        );
    }

    public function runRelistProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST)
        );
    }

    public function runStopProductsAction()
    {
        return $this->getResponse()->setBody(
            $this->processConnector(Ess_M2ePro_Model_Listing_Product::ACTION_STOP)
        );
    }

    public function runStopAndRemoveProductsAction()
    {
        return $this->getResponse()->setBody($this->processConnector(
            Ess_M2ePro_Model_Listing_Product::ACTION_STOP, array('remove' => true)
        ));
    }

    public function runDeleteAndRemoveProductsAction()
    {
        return $this->getResponse()->setBody($this->processConnector(
            Ess_M2ePro_Model_Listing_Product::ACTION_DELETE, array('remove' => true)
        ));
    }

    //#############################################

    public function getSuggestedAsinGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('ERROR: No product id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        $marketplaceId = $listingProduct->getListing()->getMarketplaceId();

        $suggestedData = $listingProduct->getData('general_id_search_suggest_data');
        if (!empty($suggestedData)) {
            Mage::helper('M2ePro/Data_Global')->setValue('product_id',$productId);
            Mage::helper('M2ePro/Data_Global')->setValue('is_suggestion',true);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id',$marketplaceId);
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',@json_decode($suggestedData,true));
            $response = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_grid')->toHtml();
        } else {
            $response = Mage::helper('M2ePro')->__('NO DATA');
        }

        $this->getResponse()->setBody($response);
    }

    //--------------------------------------------

    public function searchAsinManualAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $query = $this->getRequest()->getParam('query');

        if (empty($productId)) {
            return $this->getResponse()->setBody('No product_id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);
        $marketplaceObj = $listingProduct->getListing()->getMarketplace();
        $accountObj = $listingProduct->getListing()->getAccount();

        $tempCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $tempCollection->getSelect()->join(
            array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '`main_table`.`listing_id` = `l`.`id`',
            null
        );
        $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING;
        $tempCollection->addFieldToFilter('general_id_search_status',$temp)
            ->addFieldToFilter('l.marketplace_id', $marketplaceObj->getId())
            ->addFieldToFilter('l.account_id', $accountObj->getId());

        $isThereActiveSearch = $tempCollection->getSize();

        $message  = Mage::helper('M2ePro')->__('There is another active automatic ASIN/ISBN search task ');
        $message .= Mage::helper('M2ePro')->__('for some item(s). Please wait until this search process is finished.');

        if ($isThereActiveSearch) {
            $response = array('result' => 'error','data' => $message);
            return $this->getResponse()->setBody(json_encode($response));
        }

        $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
        if ($listingProduct->isNotListed() &&
            !$listingProduct->isLockedObject('in_action') &&
            !$listingProduct->getData('template_new_product_id') &&
            !$listingProduct->getData('general_id') &&
            $listingProduct->getData('general_id_search_status') == $temp) {

            $marketplaceObj = $listingProduct->getListing()->getMarketplace();

            /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
            $result = $dispatcher->runManual($listingProduct,$query);

            $message = Mage::helper('M2ePro')->__('Server is currently unavailable. Please try again later.');
            if ($result === false) {
                $response = array('result' => 'error','data' => $message);
                return $this->getResponse()->setBody(json_encode($response));
            }

            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$result);
            Mage::helper('M2ePro/Data_Global')->setValue('product_id',$productId);
            Mage::helper('M2ePro/Data_Global')->setValue('marketplace_id',$marketplaceObj->getId());
        } else {
            Mage::helper('M2ePro/Data_Global')->setValue('temp_data',array());
        }

        $data = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_listing_productSearch_grid')->toHtml();

        $response = array(
            'result' => 'success',
            'data' => $data
        );

        return $this->getResponse()->setBody(json_encode($response));
    }

    public function searchAsinAutoAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            return $this->getResponse()->setBody('You should select one or more products');
        }

        $productIds = explode(',', $productIds);

        $productsToSearch = array();
        foreach ($productIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

            $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
            if ($listingProduct->isNotListed() &&
                !$listingProduct->isLockedObject('in_action') &&
                !$listingProduct->getData('template_new_product_id')
                && !$listingProduct->getData('general_id') &&
                $listingProduct->getData('general_id_search_status') == $temp) {

                $productsToSearch[] = $listingProduct;
            }
        }

        if (!empty($productsToSearch)) {
            /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
            $result = $dispatcher->runAutomatic($productsToSearch);

            if ($result === false) {
                return $this->getResponse()->setBody('1');
            }
        }

        return $this->getResponse()->setBody('0');
    }

    //--------------------------------------------

    public function mapToAsinAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $generalId = $this->getRequest()->getParam('general_id');

        if (empty($productId) || empty($generalId) ||
            (
                !Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId) &&
                !Mage::helper('M2ePro')->isISBN($generalId)
            )) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
        if ($listingProduct->isNotListed() &&
            !$listingProduct->isLockedObject('in_action') &&
            !$listingProduct->getData('template_new_product_id') &&
            $listingProduct->getData('general_id_search_status') == $temp) {

            $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_SET_MANUAL;
            $listingProduct->setData('general_id',$generalId);
            $listingProduct->setData('template_new_product_id',NULL);
            $listingProduct->setData('general_id_search_status',$temp);
            $listingProduct->setData('general_id_search_suggest_data',NULL);

            $listingProduct->save();
        }
        return $this->getResponse()->setBody('0');
    }

    public function unmapFromAsinAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $productIds = explode(',', $productIds);

        $message = Mage::helper('M2ePro')->__('ASIN(s) was successfully unassigned.');
        $type = 'success';

        foreach ($productIds as $productId) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

            if (!$listingProduct->isNotListed() ||
                $listingProduct->isLockedObject('in_action')) {
                $type = 'error';
                $message = Mage::helper('M2ePro')->__(
                    'Some ASIN(s) were not unassigned as their listing status is other than "Not Listed".'
                );
                continue;
            }

            $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
            $listingProduct->setData('general_id',NULL);
            $listingProduct->setData('template_new_product_id',NULL);
            $listingProduct->setData('general_id_search_status',$temp);
            $listingProduct->setData('general_id_search_suggest_data',NULL);

            $listingProduct->save();
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => $type,
            'message' => $message
        )));
    }

    //#############################################

    protected function setRuleData($prefix)
    {
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
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

    protected function getHideProductsInOtherListingsPrefix()
    {
        $id = $this->getRequest()->getParam('id');

        $prefix = 'amazon_hide_products_others_listings_';
        $prefix .= is_null($id) ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //#############################################
}