<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Amazon_ListingController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('m2epro/listings')
            ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
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
            ->addJs('M2ePro/Template/AttributeSetHandler.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/Listing/ItemGridHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Amazon/Listing/SettingsHandler.js')
            ->addJs('M2ePro/Amazon/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Amazon/Listing/CategoryHandler.js')
            ->addJs('M2ePro/Listing/MoveToListingHandler.js')
            ->addJs('M2ePro/Amazon/Listing/ProductSearchHandler.js')
            ->addJs('M2ePro/Listing/AddListingHandler.js')
            ->addJs('M2ePro/Listing/Product/VariationHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/listings/listing');
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_listing/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Listing */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing');
        $block->enableAmazonTab();

        $this->getResponse()->setBody($block->getAmazonTabHtml());
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function searchAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_search'))
            ->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function createListingAction()
    {
        $sessionData = Mage::helper('M2ePro')->getSessionValue('temp_data');

        if (!empty($sessionData['synchronization_start_date'])) {
            $sessionData['synchronization_start_date'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                $sessionData['synchronization_start_date']
            );
        }
        if (!empty($sessionData['synchronization_stop_date'])) {
            $sessionData['synchronization_stop_date'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                $sessionData['synchronization_stop_date']
            );
        }

        $categoriesAddAction = $this->getRequest()->getParam('categories_add_action');
        $categoriesDeleteAction = $this->getRequest()->getParam('categories_delete_action');

        !empty($categoriesAddAction) && $sessionData['categories_add_action'] = $categoriesAddAction;
        !empty($categoriesDeleteAction) && $sessionData['categories_delete_action'] = $categoriesDeleteAction;
        //---------------

        // Add new General Template
        //---------------
        $generalTemplate = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_General');
        $generalTemplate->addData($sessionData)->save();
        //---------------

        $tempCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Description');
        if ($tempCollection->getSize() > 0) {
            $descriptionTemplate = $tempCollection->getFirstItem();
        } else {
            $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_Description');
            $descriptionTemplateData = array(
                'title' => 'Default',
                'component_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK
            );
            $descriptionTemplate->addData($descriptionTemplateData)->save();
        }

        // Add new Listing
        //---------------
        $sessionData['template_general_id'] = $generalTemplate->getId();
        $sessionData['template_description_id'] = $descriptionTemplate->getId();

        $listing = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')
            ->addData($sessionData)
            ->save();

        // Clear old description template attribute sets
        //--------------------
        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
        $oldAttributeSets = Mage::getModel('M2ePro/AttributeSet')
            ->getCollection()
            ->addFieldToFilter('object_type',$temp)
            ->addFieldToFilter('object_id',(int)$descriptionTemplate->getId())
            ->getItems();

        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
            $oldAttributeSet->deleteInstance();
        }

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

            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL,
                'object_id' => (int)$generalTemplate->getId(),
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

        foreach (Mage::helper('M2ePro/Magento')->getAttributeSets() as $attributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION,
                'object_id' => (int)$descriptionTemplate->getId(),
                'attribute_set_id' => (int)$attributeSet['attribute_set_id']
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }

        //--------------------

        $categories = $this->getRequest()->getParam('categories');
        $sessionCategories = Mage::helper('M2ePro')->getSessionValue('temp_listing_categories');

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
            Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
            NULL,
            Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
            // Parser hack -> Mage::helper('M2ePro')->__('Listing was successfully added');
            'Listing was successfully added',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

        //---------------

        $isEmptyListing = $this->getRequest()->getParam('empty_listing');
        if ($isEmptyListing == 1) {
            if ($this->getRequest()->getParam('back') == 'list') {
                $backUrl = $this->getUrl('*/adminhtml_listing/index', array('tab' => 'amazon'));
            } else {
                $backUrl = $this->getUrl('*/*/view', array('id' => $listing->getId()));
            }

            exit($backUrl);
        }

        //---------------

        exit($listing->getId());
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

        $tempProducts = Mage::helper('M2ePro')->getSessionValue('temp_products');
        $tempProducts = array_merge((array)$tempProducts, $listingProductIds);
        Mage::helper('M2ePro')->setSessionValue('temp_products', $tempProducts);

        $isLastPart = $this->getRequest()->getParam('is_last_part');
        if ($isLastPart == 'yes') {
            if ($this->getRequest()->getParam('do_list') == 'yes') {
                $tempProducts = Mage::helper('M2ePro')->getSessionValue('temp_products');
                Mage::helper('M2ePro')->setSessionValue('products_ids_for_list', implode(',',$tempProducts));
            }

            Mage::helper('M2ePro')->setSessionValue('temp_data', array());
            Mage::helper('M2ePro')->setSessionValue('temp_listing_categories', array());
            Mage::helper('M2ePro')->setSessionValue('temp_products', array());

            if ($this->getRequest()->getParam('back') == 'list') {
                $backUrl = $this->getUrl('*/adminhtml_listing/index', array('tab' => 'amazon'));
            } else {
                $backUrl = $this->getUrl('*/*/view', array('id' => $listingId));
            }

            $response = array('redirect' => $backUrl);
            exit(json_encode($response));
        }

        $response = array('redirect' => '');
        exit(json_encode($response));
    }

    public function getProductsFromCategoriesAction()
    {
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

            $tempProducts = $listing->getProductsFromCategory($categoryId);
            !empty($tempProducts) && $products = array_merge($products, $tempProducts);
        }

        if (!empty($products)) {
            echo implode(',', $products);
        }

        exit();
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
                Mage::helper('M2ePro')->setSessionValue('temp_data', array());
                Mage::helper('M2ePro')->setSessionValue('temp_listing_categories', array());
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
        if (is_null(Mage::helper('M2ePro')->getSessionValue('temp_data')) ||
            is_null(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories'))) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            if ($post['synchronization_start_type'] != Ess_M2ePro_Model_Listing::SYNCHRONIZATION_START_TYPE_DATE) {
                $synchronizationStartDate = Mage::helper('M2ePro')->getCurrentGmtDate();
            } else {
                $synchronizationStartDate = $post['synchronization_start_date'];
            }
            if ($post['synchronization_stop_type'] != Ess_M2ePro_Model_Listing::SYNCHRONIZATION_START_TYPE_THROUGH) {
                $synchronizationStopDate = Mage::helper('M2ePro')->getCurrentGmtDate();
            } else {
                $synchronizationStopDate = $post['synchronization_stop_date'];
            }

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
                    ->getTitle(),
                'synchronization_start_type' => $post['synchronization_start_type'],
                'synchronization_start_through_metric' => $post['synchronization_start_through_metric'],
                'synchronization_start_through_value' => $post['synchronization_start_through_value'],
                'synchronization_start_date' => $synchronizationStartDate,

                'synchronization_stop_type' => $post['synchronization_stop_type'],
                'synchronization_stop_through_metric' => $post['synchronization_stop_through_metric'],
                'synchronization_stop_through_value' => $post['synchronization_stop_through_value'],
                'synchronization_stop_date' => $synchronizationStopDate,
            );

            $sessionData = Mage::helper('M2ePro')->getSessionValue('temp_data');
            is_null($sessionData) && $sessionData = array();

            Mage::helper('M2ePro')->setSessionValue('temp_data', array_merge($sessionData, $temp));

            $this->_redirect('*/*/add',array('step'=>'2'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro')->setGlobalValue(
            'temp_data', Mage::helper('M2ePro')->getSessionValue('temp_data')
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'temp_listing_categories', Mage::helper('M2ePro')->getSessionValue('temp_listing_categories')
        );

        Mage::helper('M2ePro')->setSessionValue('temp_listing_categories', array());

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_stepOne'))
            ->renderLayout();
    }

    public function addStepTwo()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro')->getSessionValue('temp_data')) ||
            count(Mage::helper('M2ePro')->getSessionValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories'))) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        // If it post request
        //----------------------------
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            $temp = array(
                'account_id' => $post['account_id'],
                'marketplace_id' => $post['marketplace_id'],
                'sku_mode' => $post['sku_mode'],
                'sku_custom_attribute' => $post['sku_custom_attribute'],
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

            $sessionData = Mage::helper('M2ePro')->getSessionValue('temp_data');
            Mage::helper('M2ePro')->setSessionValue('temp_data', array_merge($sessionData, $temp));

            $this->_redirect('*/*/add',array('step'=>'3'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro')->setGlobalValue('temp_data', Mage::helper('M2ePro')->getSessionValue('temp_data'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_stepTwo'))
            ->renderLayout();
    }

    public function addStepThree()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro')->getSessionValue('temp_data')) ||
            count(Mage::helper('M2ePro')->getSessionValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories'))) {
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
                'hide_products_others_listings' => $post['hide_products_others_listings']
            );

            $sessionData = Mage::helper('M2ePro')->getSessionValue('temp_data');
            is_null($sessionData) && $sessionData = array();

            Mage::helper('M2ePro')->setSessionValue('temp_data', array_merge($sessionData, $temp));

            $this->_redirect('*/*/add',array('step'=>'4'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro')->setGlobalValue('temp_data', Mage::helper('M2ePro')->getSessionValue('temp_data'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_stepThree'))
            ->renderLayout();
    }

    public function addStepFour()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro')->getSessionValue('temp_data')) ||
            count(Mage::helper('M2ePro')->getSessionValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories'))) {
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
                $m2eProData = Mage::helper('M2ePro')->getSessionValue('temp_data');
                $m2eProData['categories_add_action'] = $this->getRequest()->getParam('categories_add_action');
                $m2eProData['categories_delete_action'] = $this->getRequest()->getParam('categories_delete_action');
                Mage::helper('M2ePro')->setSessionValue('temp_data', $m2eProData);
                Mage::helper('M2ePro')->setSessionValue('temp_listing_categories', $selectedCategoriesIds);
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

        // Get save param
        //----------------------------
        $save = $this->getRequest()->getParam('save');

        if (!is_null($save)) {
            if ($save == 'yes') {

                // Get selected_products param
                //---------------
                $selectedProductsIds = array();

                $selectedProducts = $this->getRequest()->getParam('selected_products');
                if (!is_null($selectedProducts)) {
                    $selectedProductsIds = explode(',',$selectedProducts);
                }
                $selectedProductsIds = array_unique($selectedProductsIds);
                //---------------

                // Get selected_categories param
                //---------------
                $selectedCategoriesIds = array();

                $selectedCategories = $this->getRequest()->getParam('selected_categories');
                if (!is_null($selectedCategories)) {
                    $selectedCategoriesIds = explode(',',$selectedCategories);
                    $m2eProData = Mage::helper('M2ePro')->getSessionValue('temp_data');
                    $m2eProData['categories_add_action'] = $this->getRequest()->getParam('categories_add_action');
                    $m2eProData['categories_delete_action'] = $this->getRequest()->getParam('categories_delete_action');
                    Mage::helper('M2ePro')->setSessionValue('temp_data', $m2eProData);
                }
                $selectedCategoriesIds = array_unique($selectedCategoriesIds);
                //---------------

                // Get session selected_categories
                //---------------
                $selectedSessionCategoriesIds = Mage::helper('M2ePro')->getSessionValue('temp_listing_categories');
                $selectedSessionCategoriesIds = array_unique($selectedSessionCategoriesIds);
                //---------------

                // Prepare listing data
                //---------------
                $sessionData = Mage::helper('M2ePro')->getSessionValue('temp_data');

                if (!empty($sessionData['synchronization_start_date'])) {
                    $sessionData['synchronization_start_date'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                        $sessionData['synchronization_start_date']
                    );
                }
                if (!empty($sessionData['synchronization_stop_date'])) {
                    $sessionData['synchronization_stop_date'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                        $sessionData['synchronization_stop_date']
                    );
                }

                Mage::helper('M2ePro')->setSessionValue('temp_data', $sessionData);
                //---------------

                // Add new listing
                //---------------
                $generalTemplate = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_General');
                $generalTemplate->addData($sessionData)->save();

                $sessionData['template_general_id'] = $generalTemplate->getId();

                $listing = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')
                    ->addData($sessionData)
                    ->save();
                //---------------

                // Attribute sets
                //--------------------
                $oldListingAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                    ->getCollection()
                    ->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_LISTING)
                    ->addFieldToFilter('object_id',(int)$listing->getId())
                    ->getItems();

                foreach ($oldListingAttributeSets as $oldAttributeSet) {
                    /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
                    $oldAttributeSet->deleteInstance();
                }

                $oldGeneralTemplateAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                    ->getCollection()
                    ->addFieldToFilter('object_type',Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL)
                    ->addFieldToFilter('object_id',(int)$generalTemplate->getId())
                    ->getItems();

                foreach ($oldGeneralTemplateAttributeSets as $oldAttributeSet) {
                    /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
                    $oldAttributeSet->deleteInstance();
                }

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

                    $dataForAdd = array(
                        'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL,
                        'object_id' => (int)$generalTemplate->getId(),
                        'attribute_set_id' => (int)$newAttributeSet
                    );
                    Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
                }
                //--------------------

                // Set message to log
                //---------------
                $tempLog = Mage::getModel('M2ePro/Listing_Log');
                $tempLog->setComponentMode($listing->getComponentMode());
                $tempLog->addListingMessage(
                    $listing->getId(),
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_ADD_LISTING,
                    // Parser hack -> Mage::helper('M2ePro')->__('Listing was successfully added');
                    'Listing was successfully added',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                );
                //---------------

                // Add products
                //---------------
                if (count($selectedProductsIds) > 0 &&
                    count($selectedCategoriesIds) == 0 &&
                    count($selectedSessionCategoriesIds) == 0) {
                    foreach ($selectedProductsIds as $productId) {
                        $listing->addProduct($productId);
                    }
                }
                //---------------

                // Add categories
                //---------------
                if (count($selectedProductsIds) == 0 &&
                    count($selectedCategoriesIds) > 0 &&
                    count($selectedSessionCategoriesIds) == 0) {
                    foreach ($selectedCategoriesIds as $categoryId) {
                        $listing->addProductsFromCategory($categoryId);
                        Mage::getModel('M2ePro/Listing_Category')
                            ->setData(array('listing_id'=>$listing->getId(),'category_id'=>$categoryId))
                            ->save();
                    }
                }
                //---------------

                // Add categories and products
                //---------------
                if (count($selectedProductsIds) > 0 &&
                    count($selectedCategoriesIds) == 0 &&
                    count($selectedSessionCategoriesIds) > 0) {
                    foreach ($selectedSessionCategoriesIds as $categoryId) {
                        Mage::getModel('M2ePro/Listing_Category')
                            ->setData(array('listing_id'=>$listing->getId(),'category_id'=>$categoryId))
                            ->save();
                    }
                    foreach ($selectedProductsIds as $productId) {
                        $listing->addProduct($productId);
                    }
                }
                //---------------

                // Clear session data
                //---------------
                Mage::helper('M2ePro')->setSessionValue('temp_data', array());
                Mage::helper('M2ePro')->setSessionValue('temp_listing_categories', array());
                //---------------

                $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Listing was successfully added.'));

                if ($this->getRequest()->getParam('back') == 'list') {
                    $this->_redirect('*/*/index');
                } else {
                    $this->_redirect('*/*/view',array('id'=>$listing->getId(),'new'=>'yes'));
                }

                return;

            } else {
                $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
                return;
            }
        }
        //----------------------------

        Mage::helper('M2ePro')->setGlobalValue(
            'temp_data', Mage::helper('M2ePro')->getSessionValue('temp_data')
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'temp_listing_categories', Mage::helper('M2ePro')->getSessionValue('temp_listing_categories')
        );

        Mage::helper('M2ePro')->setSessionValue('temp_listing_categories', array());

        // Load layout and start render
        //----------------------------
        $this->_initAction();

        $temp = Mage::helper('M2ePro')->getSessionValue('temp_data');
        if ($temp['source_products'] == Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CUSTOM) {
            $blockContent = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_stepFourProduct');
        } else if ($temp['source_products'] == Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CATEGORIES) {
            $blockContent = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_stepFourCategory');
        } else {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }

        // Set rule model
        // ---------------------------
        $this->setRuleData('amazon_rule_add_listing_product');
        // ---------------------------

        $this->_addContent($blockContent);

        $this->renderLayout();
        //----------------------------
    }

    public function addStepFive()
    {
        // Check exist temp data
        //----------------------------
        if (is_null(Mage::helper('M2ePro')->getSessionValue('temp_data')) ||
            count(Mage::helper('M2ePro')->getSessionValue('temp_data')) == 0 ||
            is_null(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories')) ||
            count(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories')) == 0) {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }
        //----------------------------

        Mage::helper('M2ePro')->setGlobalValue(
            'temp_data', Mage::helper('M2ePro')->getSessionValue('temp_data')
        );
        Mage::helper('M2ePro')->setGlobalValue(
            'temp_listing_categories', Mage::helper('M2ePro')->getSessionValue('temp_listing_categories')
        );

        // Set rule model
        // ---------------------------
        $this->setRuleData('amazon_rule_add_listing_product_categories');
        // ---------------------------

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_StepFive'))
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
            return $this->_redirect('*/adminhtml_listing/index');
        }

        // Check listing lock item
        //----------------------------
        $lockItem = Mage::getModel(
            'M2ePro/Listing_LockItem',array('id' => $id, 'component' => Ess_M2ePro_Helper_Component_Amazon::NICK)
        );
        if ($lockItem->isExist()) {
            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__('The listing is locked by another process. Please try again later.')
            );
        }
        //----------------------------

        // Check listing lock object
        //----------------------------
        if ($model->isLockedObject('products_in_action')) {
            $this->_getSession()->addNotice(
                Mage::helper('M2ePro')->__('Some Amazon request(s) are being processed now.')
            );
        }
        //----------------------------

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model->getData());
        Mage::helper('M2ePro')->setGlobalValue('marketplace_id', $model->getGeneralTemplate()->getMarketplaceId());

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_view'))
            ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model->getData());
        Mage::helper('M2ePro')->setGlobalValue('marketplace_id', $model->getGeneralTemplate()->getMarketplaceId());

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_listing/index');
        }

        $generalTemplate = $model->getGeneralTemplate();

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
            'account_id' => $generalTemplate->getData('account_id'),
            'marketplace_id' => $generalTemplate->getData('marketplace_id'),
            'sku_mode' => $generalTemplate->getData('sku_mode'),
            'sku_custom_attribute' => $generalTemplate->getData('sku_custom_attribute'),
            'generate_sku_mode' => $generalTemplate->getData('generate_sku_mode'),
            'general_id_mode' => $generalTemplate->getData('general_id_mode'),
            'general_id_custom_attribute' => $generalTemplate->getData('general_id_custom_attribute'),
            'worldwide_id_mode' => $generalTemplate->getData('worldwide_id_mode'),
            'worldwide_id_custom_attribute' => $generalTemplate->getData('worldwide_id_custom_attribute'),
            'search_by_magento_title_mode' => $generalTemplate->getData('search_by_magento_title_mode'),
            'attribute_sets' => $model->getAttributeSetsIds(),
            'condition_mode' => $generalTemplate->getData('condition_mode'),
            'condition_value' => $generalTemplate->getData('condition_value'),
            'condition_custom_attribute' => $generalTemplate->getData('condition_custom_attribute'),
            'condition_note_mode' => $generalTemplate->getData('condition_note_mode'),
            'condition_note_value' => $generalTemplate->getData('condition_note_value'),
            'condition_note_custom_attribute' => $generalTemplate->getData('condition_note_custom_attribute'),
            'handling_time_mode' => $generalTemplate->getData('handling_time_mode'),
            'handling_time_value' => $generalTemplate->getData('handling_time_value'),
            'handling_time_custom_attribute' => $generalTemplate->getData('handling_time_custom_attribute'),
            'restock_date_mode' => $generalTemplate->getData('restock_date_mode'),
            'restock_date_value' => $generalTemplate->getData('restock_date_value'),
            'restock_date_custom_attribute' => $generalTemplate->getData('restock_date_custom_attribute')
        );

        Mage::helper('M2ePro')->setGlobalValue('temp_data', array_merge($model->getData(), $additionalData));

        $this->_initAction();
        $this->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_edit_tabs'));
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_edit'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_listing/index');
        }

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        // tab: settings
        //--------------------
        $keys = array(
            'title',
            'template_selling_format_id',
            'template_description_id',
            'template_synchronization_id',

            'synchronization_start_type',
            'synchronization_start_through_metric',
            'synchronization_start_through_value',
            'synchronization_start_date',

            'synchronization_stop_type',
            'synchronization_stop_through_metric',
            'synchronization_stop_through_value',
            'synchronization_stop_date',

            'categories_add_action',
            'categories_delete_action'
        );
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }
        //--------------------

        // Prepare listing data
        //---------------
        if (!empty($data['synchronization_start_date'])) {
            $data['synchronization_start_date'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                $data['synchronization_start_date']
            );
        }
        if (!empty($data['synchronization_stop_date'])) {
            $data['synchronization_stop_date'] = Mage::helper('M2ePro')->timezoneDateToGmt(
                $data['synchronization_stop_date']
            );
        }
        //---------------

        // Prepare listing data
        //---------------
        if ($model->getData('template_synchronization_id') != $data['template_synchronization_id']) {

            $model->setSynchronizationAlreadyStart(false);
            $model->setSynchronizationAlreadyStop(false);
        }

        if ($model->getData('synchronization_start_type') != $data['synchronization_start_type'] ||
            $model->getData('synchronization_start_through_metric') != $data['synchronization_start_through_metric'] ||
            $model->getData('synchronization_start_through_value') != $data['synchronization_start_through_value'] ||
            $model->getData('synchronization_start_date') != $data['synchronization_start_date']) {

            $model->setSynchronizationAlreadyStart(false);
        }

        if ($model->getData('synchronization_stop_type') != $data['synchronization_stop_type'] ||
            $model->getData('synchronization_stop_through_metric') != $data['synchronization_stop_through_metric'] ||
            $model->getData('synchronization_stop_through_value') != $data['synchronization_stop_through_value'] ||
            $model->getData('synchronization_stop_date') != $data['synchronization_stop_date']) {

            $model->setSynchronizationAlreadyStop(false);
        }
        //---------------

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
        $generalTemplate = $model->getGeneralTemplate();
        $generalTemplate->addData($templateData)->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing was successfully saved.'));

        Mage::getModel('M2ePro/Listing_Log')->updateListingTitle($id,$data['title']);

        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to remove'));
            return $this->_redirect('*/*/index');
        }

        $idsForDelete = array();
        !is_null($id) && $idsForDelete[] = (int)$id;
        !is_null($ids) && $idsForDelete = array_merge($idsForDelete,(array)$ids);

        $deleted = $locked = 0;
        foreach ($idsForDelete as $id) {
            $template = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$id);
            if ($template->isLocked()) {
                $locked++;
            } else {
                $tempGeneralTemplate = $template->getGeneralTemplate();
                $template->deleteInstance();
                $tempGeneralTemplate->deleteInstance();
                $deleted++;
            }
        }

        $tempString = Mage::helper('M2ePro')->__('%s listing(s) were successfully deleted', $deleted);
        $deleted && $this->_getSession()->addSuccess($tempString);

        $tempString = Mage::helper('M2ePro')->__('%s listing(s) have listed items and can not be deleted', $locked);
        $locked && $this->_getSession()->addError($tempString);

        $this->_redirect('*/adminhtml_listing/index');
    }

    //#############################################

    public function productAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist..'));
            return $this->_redirect('*/adminhtml_listing/index');
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
                $redirectUrl = $this->getUrl('*/adminhtml_amazon_listing/view', array('id'=>$id));
                Mage::helper('M2ePro')->setSessionValue('products_ids_for_list', implode(',',$idsToListAction));
            }

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The products were added to listing.'));
            $this->_redirectUrl($redirectUrl);
            return;
        }
        //----------------------------

        $tempData = $model->getData();
        $tempData['attribute_sets'] = $model->getAttributeSetsIds();
        $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Amazon::NICK;
        Mage::helper('M2ePro')->setGlobalValue('temp_data', $tempData);
        Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', array());

        // Set rule model
        // ---------------------------
        $this->setRuleData('amazon_rule_listing_product');
        // ---------------------------

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_product'))
            ->renderLayout();
    }

    public function categoryProductAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist..'));
            return $this->_redirect('*/adminhtml_listing/index');
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

            Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', $categoriesIds);
            $tempData = $model->getData();
            $tempData['attribute_sets'] = $model->getAttributeSetsIds();
            $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Amazon::NICK;
            Mage::helper('M2ePro')->setGlobalValue('temp_data', $tempData);

            // Set rule model
            // ---------------------------
            $this->setRuleData('amazon_rule_listing_product_categories');
            // ---------------------------

            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_product'));
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
        $listingData['hide_products_others_listings'] = $model->isHideProductsOthersListings();
        $listingData['is_source_categories'] = $model->isSourceCategories();

        $listingData['save_categories'] = $categoriesSave;

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $listingData);

        Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', $categoriesIds);

        $this->_initAction();

        $blockContent = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_product_category'
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
                Mage::helper('M2ePro')->setGlobalValue('temp_data', $tempData);
            } else {
                Mage::helper('M2ePro')->setGlobalValue('temp_data', array());
            }

            $categories = $this->getRequest()->getParam('selected_categories');
            $categoriesIds = explode(',', $categories);
            $categoriesIds = array_unique($categoriesIds);
            $categoriesIds = array_filter($categoriesIds);

            Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories',$categoriesIds);
        } else {
            if (!is_null(Mage::helper('M2ePro')->getSessionValue('temp_data'))) {
                Mage::helper('M2ePro')->setGlobalValue(
                    'temp_data', Mage::helper('M2ePro')->getSessionValue('temp_data')
                );
            } else {
                Mage::helper('M2ePro')->setGlobalValue('temp_data', array());
            }
            if (!is_null(Mage::helper('M2ePro')->getSessionValue('temp_listing_categories'))) {
                Mage::helper('M2ePro')->setGlobalValue(
                    'temp_listing_categories', Mage::helper('M2ePro')->getSessionValue('temp_listing_categories')
                );
            } else {
                Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', array());
            }
        }

        $rulePrefix = 'amazon_rule_';
        $rulePrefix .= is_null($id) ? 'add_' : '';
        $rulePrefix .= 'listing_product';
        $rulePrefix .= count(Mage::helper('M2ePro')->getGlobalValue('temp_listing_categories')) > 0
            ? '_categories' : '';

        // Set rule model
        // ------------------------------------
        $this->setRuleData($rulePrefix);
        // ------------------------------------

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_product_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function getMarketplacesForAccountAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');

        if (is_null($accountId)) {
            exit(json_encode(array()));
        }

        /** @var $account Ess_M2ePro_Model_Amazon_Account */
        $account = Mage::helper('M2ePro/Component_Amazon')
                                ->getCachedObject('Account',$accountId)
                                ->getChildObject();

        $marketplacesCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace');
        $marketplacesCollection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('title', 'ASC');

        if ($marketplacesCollection->getSize() <= 0) {
            exit(json_encode(array()));
        }

        $marketplaces = array();
        foreach ($marketplacesCollection->getItems() as $marketplace) {
            if ($account->isExistMarketplaceItem($marketplace->getId())) {
                $marketplaces[] = array(
                    'code'  => $marketplace->getId(),
                    'label' => $marketplace->getTitle()
                );
            }
        }

        exit(json_encode($marketplaces));
    }

    //#############################################

    public function tryToMoveToListingAction()
    {
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$listingId);

        $failedProducts = array();
        foreach ($selectedProducts as $selectedProduct) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Amazon')
                ->getModel('Listing_Product')
                ->load($selectedProduct);

            if (!$listingInstance->addProduct($listingProductInstance->getProductId(),true)) {
                $failedProducts[] = $listingProductInstance->getProductId();
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

        $listingInstance = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Listing',$listingId);

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);

        $errors = 0;
        foreach ($selectedProducts as $listingProductId) {

            $listingProductInstance = Mage::helper('M2ePro/Component_Amazon')
                ->getModel('Listing_Product')
                ->load($listingProductId);

            if ($listingProductInstance->isLockedObject() ||
                $listingProductInstance->isLockedObject('in_action')) {

                $logModel->addProductMessage(
                    $listingProductInstance->getListingId(),
                    $listingProductInstance->getProductId(),
                    $listingProductInstance->getId(),
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was not moved');
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
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                    // Parser hack -> Mage::helper('M2ePro')->__('Item was not moved');
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
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully moved');
                'Item was successfully moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $logModel->addProductMessage(
                $listingProductInstance->getListingId(),
                $listingProductInstance->getProductId(),
                $listingProductInstance->getId(),
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_MOVE_TO_LISTING,
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully moved');
                'Item was successfully moved',
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            $productId = $listingProductInstance->setData('listing_id', $listingId)->save()->getProductId();

            // Set listing store id to Amazon Item
            //---------------------------------
            if (!$listingProductInstance->isNotListed()) {
                $itemsCollection = Mage::getModel('M2ePro/Amazon_Item')->getCollection();
                $itemsCollection->addFieldToFilter(
                    'account_id', $listingProductInstance->getGeneralTemplate()->getAccountId()
                );
                $itemsCollection->addFieldToFilter(
                    'marketplace_id', $listingProductInstance->getGeneralTemplate()->getMarketplaceId()
                );
                $itemsCollection->addFieldToFilter(
                    'sku', $listingProductInstance->getChildObject()->getSku()
                );
                $itemsCollection->addFieldToFilter(
                    'product_id', $productId
                );

                if ($itemsCollection->getSize() > 0) {
                    $itemsCollection->getFirstItem()->setData('store_id', $listingInstance->getStoreId())->save();
                } else {
                    $dataForAdd = array(
                        'account_id' => $listingProductInstance->getGeneralTemplate()->getAccountId(),
                        'marketplace_id' => $listingProductInstance->getGeneralTemplate()->getMarketplaceId(),
                        'sku' => $listingProductInstance->getChildObject()->getSku(),
                        'product_id' => $productId,
                        'store_id' => $listingInstance->getStoreId()
                    );
                    Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();
                }
            }
            //---------------------------------
        };

        ($errors == 0)
            ? exit(json_encode(array('result'=>'success')))
            : exit(json_encode(array('result'=>'error',
            'errors'=>$errors)));
    }

    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select products';
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getProductDispatcher();
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

        if ($result == Ess_M2ePro_Model_Amazon_Connector_Product_Requester::STATUS_ERROR) {
            return json_encode(
                array('result'=>'error','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        if ($result == Ess_M2ePro_Model_Amazon_Connector_Product_Requester::STATUS_WARNING) {
            return json_encode(
                array('result'=>'warning','action_id'=>$actionId,'is_processing_items'=>$isProcessingItems)
            );
        }

        if ($result == Ess_M2ePro_Model_Amazon_Connector_Product_Requester::STATUS_SUCCESS) {
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
        exit($this->processConnector(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_LIST));
    }

    public function runReviseProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE));
    }

    public function runRelistProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_RELIST));
    }

    public function runStopProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_STOP));
    }

    public function runStopAndRemoveProductsAction()
    {
        exit($this->processConnector(
            Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_STOP, array('remove' => true)
        ));
    }

    public function runDeleteAndRemoveProductsAction()
    {
        exit($this->processConnector(
            Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_DELETE, array('remove' => true)
        ));
    }

    //#############################################

    public function getSuggestedAsinGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            exit('ERROR: No product id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);

        $marketplaceId = $listingProduct->getGeneralTemplate()->getMarketplaceId();

        $suggestedData = $listingProduct->getData('general_id_search_suggest_data');
        if (!empty($suggestedData)) {
            Mage::helper('M2ePro')->setGlobalValue('product_id',$productId);
            Mage::helper('M2ePro')->setGlobalValue('is_suggestion',true);
            Mage::helper('M2ePro')->setGlobalValue('marketplace_id',$marketplaceId);
            Mage::helper('M2ePro')->setGlobalValue('temp_data',@json_decode($suggestedData,true));
            $response = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_grid')->toHtml();
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
            exit('No product_id!');
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product',$productId);
        $marketplaceObj = $listingProduct->getGeneralTemplate()->getMarketplace();
        $accountObj = $listingProduct->getGeneralTemplate()->getAccount();

        $tempCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $tempCollection->getSelect()->join(
            array('l'=>Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '`main_table`.`listing_id` = `l`.`id`',
            null
        );
        $tempCollection->getSelect()->join(
            array('tg'=>Mage::getResourceModel('M2ePro/Template_General')->getMainTable()),
            '`l`.`template_general_id` = `tg`.`id`',
            null
        );
        $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_PROCESSING;
        $tempCollection->addFieldToFilter('general_id_search_status',$temp)
            ->addFieldToFilter('tg.marketplace_id', $marketplaceObj->getId())
            ->addFieldToFilter('tg.account_id', $accountObj->getId());

        $isThereActiveSearch = $tempCollection->getSize();

        $message  = Mage::helper('M2ePro')->__('There is another active automatic ASIN/ISBN search task ');
        $message .= Mage::helper('M2ePro')->__('for some item(s). Please wait until this search process is finished.');

        if ($isThereActiveSearch) {
            $response = array('result' => 'error','data' => $message);
            exit(json_encode($response));
        }

        $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
        if ($listingProduct->isNotListed() &&
            !$listingProduct->isLockedObject('in_action') &&
            !$listingProduct->getData('template_new_product_id') &&
            !$listingProduct->getData('general_id') &&
            $listingProduct->getData('general_id_search_status') == $temp) {

            $marketplaceObj = $listingProduct->getGeneralTemplate()->getMarketplace();
            $accountObj = $listingProduct->getGeneralTemplate()->getAccount();

            /** @var $dispatcher Ess_M2ePro_Model_Amazon_Search_Dispatcher */
            $dispatcher = Mage::getModel('M2ePro/Amazon_Search_Dispatcher');
            $result = $dispatcher->runManual($listingProduct,$query,$marketplaceObj,$accountObj);

            $message = Mage::helper('M2ePro')->__('Server is currently unavailable. Please try again later.');
            if ($result === false) {
                $response = array('result' => 'error','data' => $message);
                exit(json_encode($response));
            }

            Mage::helper('M2ePro')->setGlobalValue('temp_data',$result);
            Mage::helper('M2ePro')->setGlobalValue('product_id',$productId);
            Mage::helper('M2ePro')->setGlobalValue('marketplace_id',$marketplaceObj->getId());
        } else {
            Mage::helper('M2ePro')->setGlobalValue('temp_data',array());
        }

        $data = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_productSearch_grid')->toHtml();

        $response = array(
            'result' => 'success',
            'data' => $data
        );

        exit(json_encode($response));
    }

    public function searchAsinAutoAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            exit ('You should select one or more products');
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
                exit('1');
            }
        }

        exit('0');
    }

    //--------------------------------------------

    public function mapToAsinAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $generalId = $this->getRequest()->getParam('general_id');

        if (empty($productId) || empty($generalId) ||
            (!Mage::helper('M2ePro/Component_Amazon')->isASIN($generalId) &&
                !Mage::helper('M2ePro/Component_Amazon')->isISBN($generalId))) {
            exit('You should provide correct parameters.');
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
        exit('0');
    }

    public function unmapFromAsinAction()
    {
        $productIds = $this->getRequest()->getParam('product_ids');

        if (empty($productIds)) {
            exit('You should provide correct parameters.');
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

        exit(json_encode(array(
            'type' => $type,
            'message' => $message
        )));
    }

    //#############################################

    public function confirmTutorialAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cache/amazon/listing/', 'tutorial_shown',1);
        $this->_redirect('*/adminhtml_listing/index',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_AMAZON));
    }

    //#############################################

    protected function setRuleData($prefix)
    {
        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro')->setGlobalValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix,
                'store_id' => $storeId,
                'attribute_criteria' =>
                                    Ess_M2ePro_Model_Magento_Product_Rule::LOAD_ATTRIBUTES_CRITERIA_BY_ATTRIBUTE_SETS,
                'attribute_sets' => $listingData['attribute_sets']
            )
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            Mage::helper('M2ePro')->setSessionValue(
                $prefix, $ruleModel->getSerializedFromPost($this->getRequest()->getPost())
            );
        } elseif (!is_null($ruleParam)) {
            Mage::helper('M2ePro')->setSessionValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro')->getSessionValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro')->setGlobalValue('rule_model', $ruleModel);
    }

    //#############################################
}