<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_ListingController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/listings')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('eBay Listings'));

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
            ->addJs('M2ePro/Listing/ItemGridHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Ebay/Listing/CategoryHandler.js')
            ->addJs('M2ePro/Listing/MoveToListingHandler.js')
            ->addJs('M2ePro/Ebay/Listing/EditHandler.js')
            ->addJs('M2ePro/Listing/AddListingHandler.js')
            ->addJs('M2ePro/Ebay/Motor/SpecificHandler.js');

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
        $block->enableEbayTab();

        $this->getResponse()->setBody($block->getEbayTabHtml());
    }

    public function listingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    public function goToEbayAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (is_null($itemId)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested eBay Item ID is not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::getModel('M2ePro/Ebay_Listing_Product')->getParentInstanceByEbayItem($itemId);

        if (is_null($listingProduct)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Requested eBay Item ID is not found.'));
            $this->_redirect('*/*/index');
            return;
        }

        $generalTemplate = $listingProduct->getGeneralTemplate();

        $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
            $itemId,
            $generalTemplate->getAccount()->getChildObject()->getMode(),
            $generalTemplate->getData('marketplace_id')
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

        // Add new listing
        //---------------
        $listing = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')
            ->addData($sessionData)
            ->save();

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
                $backUrl = $this->getUrl('*/adminhtml_listing/index', array('tab' => 'ebay'));
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
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

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
                $backUrl = $this->getUrl('*/adminhtml_listing/index', array('tab' => 'ebay'));
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
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

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

        echo implode(',', $products);
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
                'store_id' => $post['store_id'],
                'attribute_sets' => $post['attribute_sets'],

                'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,

                'template_selling_format_id'    => $post['template_selling_format_id'],
                'template_selling_format_title' => Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_SellingFormat',
                                      (int)$post['template_selling_format_id'], NULL,
                                      array('template'))
                    ->getTitle(),
                'template_general_id'     => $post['template_general_id'],
                'template_general_title'  => Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_General',
                                      (int)$post['template_general_id'], NULL,
                                      array('template'))
                    ->getTitle(),
                'template_description_id' => $post['template_description_id'],
                'template_description_title' => Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_Description',
                                      (int)$post['template_description_id'], NULL,
                                      array('template'))
                    ->getTitle(),
                'template_synchronization_id'    => $post['template_synchronization_id'],
                'template_synchronization_title' => Mage::helper('M2ePro/Component_Ebay')
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

                'source_products' => $post['source_products'],
                'hide_products_others_listings' => $post['hide_products_others_listings']
            );

            Mage::helper('M2ePro')->setSessionValue('temp_data', $temp);

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
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_add_stepOne'))
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

                // Goto step three
                //---------------
                $this->_redirect('*/*/add',array('step'=>'3'));
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
                $listing = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')
                                                                ->addData($sessionData)
                                                                ->save();
                //---------------

                // Attribute sets
                //--------------------
                $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_LISTING;
                $oldAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                                            ->getCollection()
                                            ->addFieldToFilter('object_type',$temp)
                                            ->addFieldToFilter('object_id',(int)$listing->getId())
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
            $blockContent = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_add_stepTwoProduct');
        } else if ($temp['source_products'] == Ess_M2ePro_Model_Listing::SOURCE_PRODUCTS_CATEGORIES) {
            $blockContent = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_add_stepTwoCategory');
        } else {
            $this->_redirect('*/*/add',array('step'=>'1','clear'=>'yes'));
            return;
        }

        // Set rule model
        // ---------------------------
        $this->setRuleData('ebay_rule_add_listing_product');
        // ---------------------------

        $this->_addContent($blockContent);

        $this->renderLayout();
        //----------------------------
    }

    public function addStepThree()
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
        $this->setRuleData('ebay_rule_add_listing_product_categories');
        // ---------------------------

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_add_StepThree'))
             ->renderLayout();
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$id);
        } catch (LogicException $e) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_listing/index');
        }

        // Check listing lock item
        //----------------------------
        $lockItem = Mage::getModel(
            'M2ePro/Listing_LockItem',array('id' => $id, 'component' => Ess_M2ePro_Helper_Component_Ebay::NICK)
        );
        if ($lockItem->isExist()) {
            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__('The listing is locked by another process. Please try again later.')
            );
        }
        //----------------------------

        $this->_initAction();

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view'))
             ->renderLayout();
    }

    public function viewGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$id);

        if (!$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_listing/index');
        }

        $attributeSets = $model->getAttributeSetsIds();

        $additionalData = array(
            'template_selling_format_title'  => Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Template_SellingFormat',
                                  $model->getData('template_selling_format_id'),NULL,
                                  array('template'))
                ->getTitle(),
            'template_general_title'         => Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Template_General',
                                  $model->getData('template_general_id'),NULL,
                                  array('template'))
                ->getTitle(),
            'template_description_title'     => Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Template_Description',
                                  $model->getData('template_description_id'),NULL,
                                  array('template'))
                ->getTitle(),
            'template_synchronization_title' => Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Template_Synchronization',
                                  $model->getData('template_synchronization_id'),NULL,
                                  array('template'))
                ->getTitle(),
            'attribute_sets' => array_shift($attributeSets)
        );

        Mage::helper('M2ePro')->setGlobalValue('temp_data', array_merge($model->getData(), $additionalData));

        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_edit'))
             ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/adminhtml_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/adminhtml_listing/index');
        }

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        //--------------------
        $keys = array(
            'title',

            'template_selling_format_id',
            'template_general_id',
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

        Mage::getModel('M2ePro/Listing_Log')->updateListingTitle($id,$data['title']);

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The listing was successfully saved.'));

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

        $this->_redirect('*/adminhtml_listing/index');
    }

    //---------------------------------------------

    public function productAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')->load($id);

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
                $redirectUrl = $this->getUrl('*/adminhtml_ebay_listing/view', array('id'=>$id));
                Mage::helper('M2ePro')->setSessionValue('products_ids_for_list', implode(',',$idsToListAction));
            }

            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('The products were added to listing.'));
            $this->_redirectUrl($redirectUrl);
            return;
        }
        //----------------------------

        $tempData = $model->getData();
        $tempData['attribute_sets'] = $model->getAttributeSetsIds();
        $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Ebay::NICK;
        Mage::helper('M2ePro')->setGlobalValue('temp_data', $tempData);
        Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', array());

        // Set rule model
        // ---------------------------
        $this->setRuleData('ebay_rule_listing_product');
        // ---------------------------

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product'))
             ->renderLayout();
    }

    public function categoryProductAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $model Ess_M2ePro_Model_Listing */
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')->load($id);

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
            $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Ebay::NICK;
            Mage::helper('M2ePro')->setGlobalValue('temp_data', $tempData);

            // Set rule model
            // ---------------------------
            $this->setRuleData('ebay_rule_listing_product_categories');
            // ---------------------------

            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product'));
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
        $listingData['component_mode'] = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $listingData['store_id'] = $model->getStoreId();
        $listingData['hide_products_others_listings'] = $model->isHideProductsOthersListings();
        $listingData['is_source_categories'] = $model->isSourceCategories();

        $listingData['save_categories'] = $categoriesSave;

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $listingData);

        Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', $categoriesIds);

        $this->_initAction();

        $blockContent = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_product_category');

        $this->_addContent($blockContent);

        $this->renderLayout();
    }

    public function productGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Listing')->load($id);

        if (!is_null($id)) {
            if (!is_null($model->getId())) {
                $tempData = $model->getData();
                $tempData['attribute_sets'] = $model->getAttributeSetsIds();
                $tempData['component_mode'] = Ess_M2ePro_Helper_Component_Ebay::NICK;
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

        $rulePrefix = 'ebay_rule_';
        $rulePrefix .= is_null($id) ? 'add_' : '';
        $rulePrefix .= 'listing_product';
        $rulePrefix .= count(Mage::helper('M2ePro')->getGlobalValue('temp_listing_categories')) > 0
            ? '_categories' : '';

        // Set rule model
        // ---------------------------
        $this->setRuleData($rulePrefix);
        // ---------------------------

        $response = $this->loadLayout()->getLayout()
                         ->createBlock('M2ePro/adminhtml_ebay_listing_product_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function tryToMoveToListingAction()
    {
        $selectedProducts = (array)json_decode($this->getRequest()->getParam('selectedProducts'));
        $listingId = (int)$this->getRequest()->getParam('listingId');

        $listingInstance = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $failedProducts = array();
        foreach ($selectedProducts as $selectedProduct) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Ebay')
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

        $listingInstance = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $errors = 0;
        foreach ($selectedProducts as $listingProductId) {

            $listingProductInstance = Mage::helper('M2ePro/Component_Ebay')
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

            $listingProductInstance->setData('listing_id', $listingId)->save();

            // Set listing store id to Ebay Item
            //---------------------------------
            if (!$listingProductInstance->isNotListed()) {
                $listingProductInstance->getChildObject()
                    ->getEbayItem()
                    ->setData('store_id', $listingInstance->getStoreId())
                    ->save();
            }
            //---------------------------------
        };

        ($errors == 0)
            ? exit(json_encode(array('result'=>'success')))
            : exit(json_encode(array('result'=>'error',
            'errors'=>$errors)));
    }

    //#############################################

    public function motorSpecificGridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_motor_specific_grid')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    public function updateProductsAttributeAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listingProductIds = $this->getRequest()->getParam('listing_product_ids', '');
        $epids = $this->getRequest()->getParam('epids', '');
        $overwrite = $this->getRequest()->getParam('overwrite', '') == 'yes';

        $listingProductIds = explode(',', $listingProductIds);
        $epids = explode(',', $epids);

        if (!$listingId || !$listingProductIds || !$epids) {
            $message = Mage::helper('M2ePro')->__('Required parameters were not selected');
            exit(json_encode(array(
                'ok' => false,
                'message' => Mage::helper('M2ePro')->escapeJs($message)
            )));
        }

        $attributeCode = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId)
            ->getGeneralTemplate()->getChildObject()->getMotorsSpecificsAttribute();
        $attributeSeparator = Ess_M2ePro_Model_Ebay_Template_General::MOTORS_SPECIFICS_VALUE_SEPARATOR;
        $attributeValue = implode($attributeSeparator, $epids);

        $listingProductsCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $listingProductsCollection->addFieldToFilter('id', array('in' => $listingProductIds));
        $listingProductsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductsCollection->getSelect()->columns(array('product_id'));

        $productsCollection = Mage::getModel('catalog/product')->getCollection();
        $productsCollection->addFieldToFilter('entity_id', $listingProductsCollection->getColumnValues('product_id'));
        $productsCollection->addAttributeToSelect($attributeCode);

        try {
            foreach ($productsCollection as $product) {
                $product->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
                /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
                $magentoProduct->setProduct($product);
                $magentoProduct->saveAttribute($attributeCode, $attributeValue, $overwrite, $attributeSeparator);
            }
        } catch (Exception $e) {
            exit(json_encode(array(
                'ok' => false,
                'message' => Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__($e->getMessage()))
            )));
        }

        $message = Mage::helper('M2ePro')->__('ePIDs were saved in Compatibility Attribute.');
        exit(json_encode(array(
            'ok' => true,
            'message' => Mage::helper('M2ePro')->escapeJs($message))
        ));
    }

    //#############################################

    protected function processConnector($action, array $params = array())
    {
        if (!$listingsProductsIds = $this->getRequest()->getParam('selected_products')) {
            return 'You should select products';
        }

        $params['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER;

        $listingsProductsIds = explode(',', $listingsProductsIds);

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Dispatcher');
        $result = (int)$dispatcherObject->process($action, $listingsProductsIds, $params);
        $actionId = (int)$dispatcherObject->getLogsActionId();

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR) {
            return json_encode(array('result'=>'error','action_id'=>$actionId));
        }

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING) {
            return json_encode(array('result'=>'warning','action_id'=>$actionId));
        }

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_SUCCESS) {
            return json_encode(array('result'=>'success','action_id'=>$actionId));
        }

        return json_encode(array('result'=>'error','action_id'=>$actionId));
    }

    //---------------------------------------------

    public function runListProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST));
    }

    public function runReviseProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_REVISE));
    }

    public function runRelistProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST));
    }

    public function runStopProductsAction()
    {
        exit($this->processConnector(Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP));
    }

    public function runStopAndRemoveProductsAction()
    {
        exit($this->processConnector(
            Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_STOP, array('remove' => true)
        ));
    }

    //#############################################

    public function confirmTutorialAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cache/ebay/listing/', 'tutorial_shown',1);
        $this->_redirect('*/adminhtml_listing/index',
            array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY));
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