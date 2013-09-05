<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_CategorySettingsController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected $sessionKey = 'ebay_listing_category_settings';

    //#############################################

    protected function _initAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js')
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
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Exception('Listing is not defined');
        }

        if (!$this->checkProductAddIds()) {
            return $this->_redirect('*/adminhtml_ebay_listing_productAdd',array('listing_id' => $listingId,
                                                                                '_current' => true));
        }

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data',
            Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId)
        );

        $step = (int)$this->getRequest()->getParam('step');

        if (is_null($this->getSessionValue('mode'))) {
            $step = 1;
        }

        switch ($step) {
            case 1:
                return $this->stepOne();
                break;
            case 2:
                $action = 'stepTwo';
                break;
            case 3:
                $action = 'stepThree';
                break;
            // ....
            default:
                return $this->_redirect('*/*/', array('_current' => true,'step' => 1));
        }

        $action .= 'Mode'. ucfirst($this->getSessionValue('mode'));

        return $this->$action();
    }

    //#############################################

    private function stepOne()
    {
        if ($this->getRequest()->isPost()) {
            $this->setSessionValue('mode', $this->getRequest()->getParam('mode'));

            return $this->_redirect('*/*/', array(
                'step' => 2,
                '_current' => true,
                'skip_get_suggested' => NULL
            ));
        }

        $this->setWizardStep('categoryStepOne');

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_mode'))
             ->renderLayout();
    }

    //#############################################

    private function stepTwoModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $categoryParam = $this->getRequest()->getParam('category_data');
            $categoryData = array();
            if (!empty($categoryParam)) {
                $categoryData = json_decode($categoryParam, true);
            }

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

            $data = array();
            $keys = array(
                'category_main_mode',
                'category_main_id',
                'category_main_attribute',

                'category_secondary_mode',
                'category_secondary_id',
                'category_secondary_attribute',

                'store_category_main_mode',
                'store_category_main_id',
                'store_category_main_attribute',

                'store_category_secondary_mode',
                'store_category_secondary_id',
                'store_category_secondary_attribute',

                'tax_category_mode',
                'tax_category_value',
                'tax_category_attribute'
            );
            foreach ($categoryData as $key => $value) {
                if (!in_array($key, $keys)) {
                    continue;
                }

                $data[$key] = $value;
            }

            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

            $this->addCategoriesPath($data,$listing);

            $attributes = array();
            if (!empty($categoryData['attributes'])) {
                $attributes = explode(',', $categoryData['attributes']);
            }

            $sessionData['mode_same'] = array(
                'attributes' => $attributes,
                'category' => $data,
                'specific' => array()
            );
            Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

            return;
        }

        $this->setWizardStep('categoryStepTwo');

        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        if (empty($sessionData['mode_same']['attributes'])) {
            $productAddIds = array_unique(json_decode($listingData['product_add_ids'], true));
            $listingProductCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
            $listingProductCollection->addFieldToFilter('id', array('in' => $productAddIds));

            $products = array();
            foreach ($listingProductCollection->getData() as $listingProduct) {
                $products[] = $listingProduct['product_id'];
            }

            $attributesTemp = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromProducts($products);

            $attributes = array();
            foreach ($attributesTemp as $attribute) {
                $attributes[] = $attribute['code'];
            }
        } else {
            $attributes = $sessionData['mode_same']['attributes'];
        }

        $this->_initAction()
            ->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_same_chooser', '',
                array(
                    'attributes' => $attributes,
                    'internal_data' => !empty($sessionData['mode_same']['category']) ?
                        $sessionData['mode_same']['category'] : array()
                )
            ))->renderLayout();
    }

    private function stepTwoModeCategory()
    {
        $categoriesIds = $this->getCategoriesIdsByListingProductsIds(
            $this->getListingFromRequest()->getAddedListingProductsIds()
        );

        if (empty($categoriesIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'Magento Categories are not specified on products you are adding.')
            );
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',
                                                                          $this->getRequest()->getParam('listing_id'));

        if ($this->getRequest()->isXmlHttpRequest()) {

            $block = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_listing_category_category_grid'
            );
            $block->setStoreId($listing->getStoreId());
            $block->setData('categories_ids', $categoriesIds);

            return $this->getResponse()->setBody($block->toHtml());
        }

        $this->setWizardStep('categoryStepTwo');

        $this->initSessionData($categoriesIds);

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_category'

        );
        $block->getChild('grid')->setData('categories_ids', $categoriesIds);
        $block->getChild('grid')->setStoreId($listing->getStoreId());

        $this->_initAction();

        $this->_title(Mage::helper('M2ePro')->__('Select Products (eBay Categories)'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Ebay/Listing/Category/Category/GridHandler.js');

         $this->_addContent($block)
              ->renderLayout();
    }

    private function stepTwoModeManually()
    {
        $this->stepTwoModeProduct(false);
    }

    private function stepTwoModeProduct($getSuggested = true)
    {
        $this->setWizardStep('categoryStepTwo');

        $this->_initAction();

        //------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $listingProductAddIds = (array)json_decode($listing->getData('product_add_ids'), true);
        //------------------------------

        //------------------------------
        if (!$this->getRequest()->getParam('skip_get_suggested')) {
            Mage::helper('M2ePro/Data_Global')->setValue('get_suggested', $getSuggested);
        }
        $this->initSessionData($listingProductAddIds);
        //------------------------------

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/SuggestedSearchHandler.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
        ;

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product'));
        $this->renderLayout();
    }

    //---------------------------------------------

    public function stepTwoModeProductGridAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        //------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listing);
        //------------------------------

        $this->loadLayout();

        $body = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }

    //---------------------------------------------

    public function stepTwoGetSuggestedCategoryAction()
    {
        $this->loadLayout();

        //------------------------------
        $listingProductIds = $this->getRequestIds();
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $marketplaceId = (int)$listing->getData('marketplace_id');
        //------------------------------

        //------------------------------
        $collection = Mage::getResourceModel('M2ePro/Ebay_Listing')->getCatalogProductCollection($listingId);
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->where('lp.id IN (?)', $listingProductIds);
        $collection->load();
        //------------------------------

        if ($collection->count() == 0) {
            $this->getResponse()->setBody(json_encode(array()));
            return;
        }

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        $result = array('failed' => 0, 'succeeded' => 0);

        //------------------------------
        foreach ($collection as $product) {
            if (($query = $product->getData('name')) == '') {
                $result['failed']++;
                continue;
            }

            $attributeSetId = $product->getData('attribute_set_id');
            if (!Mage::helper('M2ePro/Magento_AttributeSet')->isDefault($attributeSetId)) {
                $query .= ' ' . Mage::helper('M2ePro/Magento_AttributeSet')->getName($attributeSetId);
            }

            try {
                $suggestions = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                    ->processConnector(
                        'category',
                        'get',
                        'suggested',
                        array('query' => $query),
                        $marketplaceId
                    );
            } catch (Exception $e) {
                $result['failed']++;
                continue;
            }

            if (!empty($suggestions)) {
                foreach ($suggestions as $key => $suggestion) {
                    if (!is_numeric($key)) {
                        unset($suggestions[$key]);
                    }
                }
            }

            if (empty($suggestions)) {
                $result['failed']++;
                continue;
            }

            $suggestedCategory = reset($suggestions);

            $categoryExists = Mage::helper('M2ePro/Component_Ebay_Category')
                ->exists(
                    $suggestedCategory['category_id'],
                    $marketplaceId
                );

            if (!$categoryExists) {
                $result['failed']++;
                continue;
            }

            $listingProductId = $product->getData('listing_product_id');
            $listingProductData = $sessionData['mode_product'][$listingProductId];
            $listingProductData['category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY;
            $listingProductData['category_main_id'] = $suggestedCategory['category_id'];
            $listingProductData['category_main_path'] = implode(' -> ', $suggestedCategory['category_path']);

            $sessionData['mode_product'][$listingProductId] = $listingProductData;

            $result['succeeded']++;
        }
        //------------------------------

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        $this->getResponse()->setBody(json_encode($result));
    }

    //---------------------------------------------

    public function stepTwoSuggestedResetAction()
    {
        //------------------------------
        $listingProductIds = $this->getRequestIds();
        //------------------------------

        $this->initSessionData($listingProductIds, true);
    }

    //---------------------------------------------

    public function stepTwoSaveToSessionAction()
    {
        $ids = $this->getRequestIds();
        $templateData = $this->getRequest()->getParam('template_data');
        $templateData = (array)json_decode($templateData, true);

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $this->addCategoriesPath($templateData,$listing);

        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        if ($this->getSessionValue('mode') == 'category') {

            foreach ($ids as $categoryId) {
                $sessionData[$categoryId]['listing_products_ids'] = $this->getSelectedListingProductsIdsByCategoriesIds(
                    array($categoryId)
                );
            }

        }

        $idsWherePrimaryCategoryNotSet = array();

        foreach ($ids as $id) {
            $sessionData[$id] = array_merge($sessionData[$id], $templateData);

            if ($sessionData[$id]['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
                $idsWherePrimaryCategoryNotSet[] = $id;
            }
        }

        $this->setSessionValue($key, $sessionData);

        if (count($idsWherePrimaryCategoryNotSet)) {
            $this->initSessionData($idsWherePrimaryCategoryNotSet, true);
        }
    }

    //---------------------------------------------

    public function stepTwoModeProductValidateAction()
    {
        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $this->clearSpecificsSession();

        if (empty($sessionData)) {
            return $this->getResponse()->setBody(json_encode(array(
                'validation' => false,
                'message' => Mage::helper('M2ePro')->__(
                    'There are no items to continue. Please, go back and select the item(s).'
                )
            )));
        }

        $failedCount = 0;
        foreach ($sessionData as $categoryData) {

            if ($categoryData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $key = 'category_main_id';
            } else {
                $key = 'category_main_attribute';
            }

            if (!$categoryData[$key]) {
                $failedCount++;
            }
        }

        $this->getResponse()->setBody(json_encode(array(
            'validation' => $failedCount == 0,
            'total_count' => count($sessionData),
            'failed_count' => $failedCount
        )));
    }

    //---------------------------------------------

    public function stepTwoModeCategoryValidateAction()
    {
        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $this->clearSpecificsSession();

        if (empty($sessionData)) {
            return $this->getResponse()->setBody(json_encode(array(
                'validation' => false,
                'message' => Mage::helper('M2ePro')->__(
                    'Magento Categories are not specified on products you are adding.'
                )
            )));
        }

        $isValid = true;
        foreach ($sessionData as $categoryData) {

            if ($categoryData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $key = 'category_main_id';
            } else {
                $key = 'category_main_attribute';
            }

            if (!$categoryData[$key]) {
                $isValid = false;
            }
        }

        $this->getResponse()->setBody(json_encode(array(
            'validation' => $isValid,
            'message' => Mage::helper('M2ePro')->__(
                'You have not selected the Primary eBay Category for some Magento Categories.'
            )
        )));
    }

    //---------------------------------------------

    public function deleteModeProductAction()
    {
        $ids = $this->getRequestIds();
        $ids = array_map('intval',$ids);

        $sessionData = $this->getSessionValue('mode_product');
        foreach ($ids as $id) {
            unset($sessionData[$id]);
        }
        $this->setSessionValue('mode_product', $sessionData);

        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $listingProductAddIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        if (empty($listingProductAddIds)) {
            return;
        }
        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds,$ids);

        $listing->setData('product_add_ids',json_encode($listingProductAddIds))->save();
    }

    //#############################################

    private function stepThreeModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $specificParam = $this->getRequest()->getParam('specific_data');

            $specificData = array();
            if (!empty($specificParam)) {
                $specificData = json_decode($specificParam, true);
            }

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

            $data = array();

            $data['motors_specifics_attribute'] = isset($specificData['motors_specifics_attribute'])
                ? $specificData['motors_specifics_attribute'] : '';

            $data['specifics'] = array();
            if (!empty($specificData['specifics'])) {
                $data['specifics'] = $specificData['specifics'];
            }

            $modeEbay = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY;
            if ($sessionData['mode_same']['category']['category_main_mode'] == $modeEbay) {

                $listingId = $this->getRequest()->getParam('listing_id');
                $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

                $data['variation_enabled'] = (int)Mage::helper('M2ePro/Component_Ebay_Category')
                    ->isVariationEnabledByCategoryId(
                        $sessionData['mode_same']['category']['category_main_id'],
                        $listing->getMarketplaceId()
                    );
            } else {
                $data['variation_enabled'] = 1;
            }

            $sessionData['mode_same']['specific'] = $data;

            // save category template & specifics
            //------------------------------
            $builderData = $sessionData['mode_same']['category'];
            $builderData['motors_specifics_attribute'] = $data['motors_specifics_attribute'];
            $builderData['variation_enabled'] = $data['variation_enabled'];
            $builderData['specifics'] = $sessionData['mode_same']['specific']['specifics'];

            $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
            $templateId = $categoryTemplate->getId();
            //------------------------------

            $productsCollection = Mage::getModel('M2ePro/Ebay_Listing_Product')->getCollection();
            $productsCollection->addFieldToFilter(
                'listing_product_id',
                array('in' => $this->getListingFromRequest()->getAddedListingProductsIds())
            );
            foreach ($productsCollection->getItems() as $listingProduct) {
                $listingProduct->setData('template_category_id', $templateId)->save();
            }

            $this->endWizard();
            $this->endListingCreation();

            return;
        }

        $this->setWizardStep('categoryStepThree');

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);
        $selectedCategoryMode = $sessionData['mode_same']['category']['category_main_mode'];
        if ($selectedCategoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $selectedCategoryValue = $sessionData['mode_same']['category']['category_main_id'];
        } else {
            $selectedCategoryValue = $sessionData['mode_same']['category']['category_main_attribute'];
        }

        $internalData = $sessionData['mode_same']['specific'];

        $specifics = array();
        if (!empty($internalData['specifics'])) {
            $specifics = $internalData['specifics'];
            unset($internalData['specifics']);
        }

        $specificBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_same_specific', '',
            array(
                'attributes' => $sessionData['mode_same']['attributes'],
                'category_mode' => $selectedCategoryMode,
                'category_value' => $selectedCategoryValue,
                'internal_data' => $sessionData['mode_same']['specific'],
                'specifics' => $specifics
            )
        );

        $this->_initAction()
            ->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent($specificBlock)
            ->renderLayout();
    }

    private function stepThreeModeCategory()
    {
        $this->stepThree();
    }

    private function stepThreeModeProduct()
    {
        $this->stepThree();
    }

    private function stepThreeModeManually()
    {
        $this->stepThree();
    }

    private function stepThree()
    {
        $this->setWizardStep('categoryStepThree');

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $templatesData = $this->getTemplatesData();

        if (count($templatesData) <= 0) {

            $this->endWizard();
            $this->endListingCreation();

            return $this->_redirect('*/adminhtml_ebay_listing/review', array(
                'disable_list' => true,
                '_current' => true
            ));
        }

        $this->initSpecificsSessionData($templatesData);

        $currentPrimaryCategory = $this->getCurrentPrimaryCategory();
        $this->setSessionValue('current_primary_category', $currentPrimaryCategory);

        $wrapper = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific_wrapper');
        $wrapper->setData('store_id', $listing->getStoreId());
        $wrapper->setData('categories', $templatesData);
        $wrapper->setData('current_category', $currentPrimaryCategory);

        $wrapper->setChild('specific', $this->getSpecificBlock());

        $this->_initAction();

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Specific/WrapperHandler.js');

        $this->_title(Mage::helper('M2ePro')->__('Specifics'));

        $this->_addContent($wrapper)
              ->renderLayout();
    }

    //---------------------------------------------

    public function stepThreeGetCategorySpecificsAction()
    {
        $category = $this->getRequest()->getParam('category');
        $templateData = $this->getTemplatesData();
        $templateData = $templateData[$category];

        if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {

            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

            $hasRequiredSpecifics = Mage::helper('M2ePro/Component_Ebay_Category')->isCategoryHasRequiredSpecifics(
                $templateData['category_main_id'],
                $listing->getMarketplaceId()
            );

        } else {
            // todo
            $hasRequiredSpecifics = true;
        }

        $this->setSessionValue('current_primary_category', $category);

        $this->getResponse()->setBody(json_encode(array(
            'text' => $this->getSpecificBlock()->toHtml(),
            'hasRequiredSpecifics' => $hasRequiredSpecifics
        )));
    }

    //---------------------------------------------

    public function stepThreeSaveCategorySpecificsToSessionAction()
    {
        $category = $this->getRequest()->getParam('category');
        $categorySpecificsData = json_decode($this->getRequest()->getParam('data'), true);

        $sessionSpecificsData = $this->getSessionValue('specifics');

        $templateData = $this->getTemplatesData();
        $templateData = $templateData[$category];

        if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {

            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

            $variationEnabled = (int)Mage::helper('M2ePro/Component_Ebay_Category')->isVariationEnabledByCategoryId(
                $templateData['category_main_id'],
                $listing->getMarketplaceId()
            );

        } else {
            $variationEnabled = 1;
        }

        $sessionSpecificsData[$category] = array_merge(
            $sessionSpecificsData[$category],
            array(
                'variation_enabled' => $variationEnabled,
                'motors_specifics_attribute' => $categorySpecificsData['motors_specifics_attribute'],
                'specifics' => $categorySpecificsData['specifics'],
            )
        );

        $this->setSessionValue('specifics', $sessionSpecificsData);
    }

    //#############################################

    private function checkProductAddIds()
    {
        return count($this->getListingFromRequest()->getAddedListingProductsIds()) > 0;
    }

    //#############################################

    private function initSessionData($ids, $override = false)
    {
        $key = $this->getSessionDataKey();

        $sessionData = $this->getSessionValue($key);
        !$sessionData && $sessionData = array();

        foreach ($ids as $id) {

            if (!empty($sessionData[$id]) && !$override) {
                continue;
            }

            $sessionData[$id] = array(
                'category_main_id' => NULL,
                'category_main_path' => NULL,
                'category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'category_main_attribute' => NULL,

                'category_secondary_id' => NULL,
                'category_secondary_path' => NULL,
                'category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'category_secondary_attribute' => NULL,

                'store_category_main_id' => NULL,
                'store_category_main_path' => NULL,
                'store_category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_NONE,
                'store_category_main_attribute' => NULL,

                'store_category_secondary_id' => NULL,
                'store_category_secondary_path' => NULL,
                'store_category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_NONE,
                'store_category_secondary_attribute' => NULL,

                'tax_category_mode' => Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_NONE,
                'tax_category_value' => NULL,
                'tax_category_attribute' => NULL,
            );

        }

        if (!$override) {
            foreach (array_diff(array_keys($sessionData),$ids) as $id) {
                unset($sessionData[$id]);
            }
        }

        $this->setSessionValue($key, $sessionData);
    }

    //#############################################

    private function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->sessionKey, $sessionData);

        return $this;
    }

    private function getSessionValue($key = NULL)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey);

        if (is_null($sessionData)) {
            $sessionData = array();
        }

        if (is_null($key)) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : NULL;
    }

    private function getSessionDataKey()
    {
        $key = '';

        switch (strtolower($this->getSessionValue('mode'))) {
            case 'same':
                $key = 'mode_same';
                break;
            case 'category':
                $key = 'mode_category';
                break;
            case 'product':
            case 'manually':
                $key = 'mode_product';
                break;
        }

        return $key;
    }

    private function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->getValue($this->sessionKey, true);
    }

    //#############################################

    private function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,$step);
    }

    private function endWizard()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStatus(
            Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,
            Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();
    }

    //#############################################

    private function endListingCreation()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        Mage::helper('M2ePro/Data_Session')->setValue(
            'added_products_ids', $listing->getChildObject()->getAddedListingProductsIds()
        );

        $listing->setData('product_add_ids',json_encode(array()))->save();

        $this->clearSession();
    }

    //#############################################

    private function getSpecificBlock()
    {
        $templatesData = $this->getTemplatesData();
        $currentPrimaryCategory = $this->getCurrentPrimaryCategory();

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        /* @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());

        $currentTemplateData = $templatesData[$currentPrimaryCategory];

        $categoryMode = $currentTemplateData['category_main_mode'];
        $specific->setCategoryMode($categoryMode);

        if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $specific->setCategoryValue($currentTemplateData['category_main_id']);
        } elseif($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $specific->setCategoryValue($currentTemplateData['category_main_attribute']);
        }

        $specificsData = $this->getSessionValue('specifics');

        $specific->setInternalData($specificsData[$currentPrimaryCategory]);
        $specific->setSelectedSpecifics($specificsData[$currentPrimaryCategory]['specifics']);

        $ids = array();
        foreach ($this->getSessionValue($this->getSessionDataKey()) as $id => $templateData) {

            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $tempKey = 'category_main_id';
            } else {
                $tempKey = 'category_main_attribute';
            }

            if ($templateData[$tempKey] == $currentPrimaryCategory) {
                $ids[] = $id;
            }
        }

        return $specific;
    }

    //#############################################

    public function getChooserBlockHtmlAction()
    {
        $ids = $this->getRequestIds();

        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $neededData = array();

        foreach ($ids as $id) {
            $neededData[$id] = $sessionData[$id];
        }

        // ----------------------------------------------

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        $accountId = $listing->getAccountId();
        $marketplaceId = $listing->getMarketplaceId();
        $attributes    = $this->getAttributes($ids);
        $internalData  = $this->getInternalDataForChooserBlock($neededData);

        /* @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('chooser_main_container');
        $chooserBlock->setAccountId($accountId);
        $chooserBlock->setMarketplaceId($marketplaceId);
        $chooserBlock->setAttributes($attributes);
        $chooserBlock->setInternalData($internalData);

        // ---------------------------------------------
        $wrapper = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_wrapper');
        $wrapper->setChild('chooser', $chooserBlock);
        // ---------------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    //#############################################

    private function getAttributes($ids)
    {
        if (in_array($this->getSessionValue('mode'), array('product', 'manually'))) {
            $listingProductsIds = $ids;
        } else  {
            $listingProductsIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        }

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $listingProductsIds));

        $productsIds = array();
        foreach ($collection->getData() as $item) {
            $productsIds[] = $item['product_id'];
        }

        if ($this->getSessionValue('mode') == 'category') {
            $productsIds = array_values(array_intersect(
                $productsIds,
                Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories($ids)
            ));
        }

        $attributes =  Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromProducts($productsIds);

        $codes = array();
        foreach ($attributes as $attribute) {
            $codes[] = $attribute['code'];
        }

        return array_values(array_unique($codes));
    }

    //#############################################

    private function getInternalDataForChooserBlock($data)
    {
        $resultData = array();

        $firstData = reset($data);

        $tempKeys = array('category_main_id',
                          'category_main_path',
                          'category_main_mode',
                          'category_main_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!$this->isDataTheSameForAll($data,$tempKeys)) {
            $resultData['category_main_id'] = 0;
            $resultData['category_main_path'] = NULL;
            $resultData['category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['category_main_attribute'] = NULL;
            $resultData['category_main_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('category_secondary_id',
                          'category_secondary_path',
                          'category_secondary_mode',
                          'category_secondary_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!$this->isDataTheSameForAll($data,$tempKeys)) {
            $resultData['category_secondary_id'] = 0;
            $resultData['category_secondary_path'] = NULL;
            $resultData['category_secondary_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['category_secondary_attribute'] = NULL;
            $resultData['category_secondary_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('store_category_main_id',
                          'store_category_main_path',
                          'store_category_main_mode',
                          'store_category_main_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!$this->isDataTheSameForAll($data,$tempKeys)) {
            $resultData['store_category_main_id'] = 0;
            $resultData['store_category_main_path'] = NULL;
            $resultData['store_category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_NONE;
            $resultData['store_category_main_attribute'] = NULL;
            $resultData['store_category_main_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('store_category_secondary_id',
                          'store_category_secondary_path',
                          'store_category_secondary_mode',
                          'store_category_secondary_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!$this->isDataTheSameForAll($data,$tempKeys)) {
            $resultData['store_category_secondary_id'] = 0;
            $resultData['store_category_secondary_path'] = NULL;
            $resultData['store_category_secondary_mode'] =
                Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_NONE;
            $resultData['store_category_secondary_attribute'] = NULL;
            $resultData['store_category_secondary_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen products.'
            );
        }

        // ---------------------------------------------

        $tempKeys = array('tax_category_mode',
                          'tax_category_value',
                          'tax_category_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!$this->isDataTheSameForAll($data,$tempKeys)) {
            $resultData['tax_category_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_NONE;
            $resultData['tax_category_value'] = NULL;
            $resultData['tax_category_attribute'] = NULL;
        }

        // ---------------------------------------------

        return $resultData;
    }

    // ---------------------------------------------

    private function isDataTheSameForAll($itemsData, $keys)
    {
        if (count($itemsData) > 200) {
            return false;
        }

        $preparedData = array();

        foreach ($keys as $key) {
            $preparedData[$key] = array();
        }

        foreach ($itemsData as $itemData) {
            foreach ($keys as $key) {
                $preparedData[$key][] = $itemData[$key];
            }
        }

        foreach ($keys as $key) {
            $preparedData[$key] = array_unique($preparedData[$key]);
            if (count($preparedData[$key]) > 1) {
                return false;
            }
        }

        return true;
    }

    //#############################################

    private function clearSpecificsSession()
    {
        $this->setSessionValue('specifics', null);
        $this->setSessionValue('current_primary_category', null);
    }

    //#############################################

    private function getCurrentPrimaryCategory()
    {
        $currentPrimaryCategory = $this->getSessionValue('current_primary_category');

        if (is_null($currentPrimaryCategory)) {

            $firstTemplateData = $this->getTemplatesData();
            $firstTemplateData = reset($firstTemplateData);

            if ($firstTemplateData['category_main_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $currentPrimaryCategory = $firstTemplateData['category_main_id'];
            } else {
                $currentPrimaryCategory = $firstTemplateData['category_main_attribute'];
            }
        }

        return $currentPrimaryCategory;
    }

    private function getTemplatesData()
    {
        $templatesData = array();
        foreach ($this->getSessionValue($this->getSessionDataKey()) as $templateData) {

            if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $id = $templateData['category_main_id'];
            } else {
                $id = $templateData['category_main_attribute'];
            }

            if (empty($id)) {
                continue;
            }

            $templatesData[$id] = $templateData;
        }

        ksort($templatesData);
        $templatesData = array_reverse($templatesData, true);

        return $templatesData;
    }

    //#############################################

    private function initSpecificsSessionData($templatesData)
    {
        $specificsData = $this->getSessionValue('specifics');
        is_null($specificsData) && $specificsData = array();

        foreach ($templatesData as $id => $templateData) {

            if (!empty($specificsData[$id])) {
                continue;
            }

            $specificsData[$id] = array(
                'motors_specifics_attribute' => NULL,
                'specifics' => array()
            );
        }

        $this->setSessionValue('specifics', $specificsData);
    }

    //#############################################

    private function getSelectedListingProductsIdsByCategoriesIds($categoriesIds)
    {
        $productsIds = Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories($categoriesIds);

        $listingProductIds = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
                ->addFieldToFilter('product_id', array('in' => $productsIds))->getAllIds();

        return array_values(array_intersect(
            $this->getListingFromRequest()->getAddedListingProductsIds(),
            $listingProductIds
        ));
    }

    //#############################################

    public function saveAction()
    {
        $sessionData = $this->getSessionValue($this->getSessionDataKey());

        if ($this->getSessionValue('mode') == 'category') {
            foreach ($sessionData as $categoryId => $data) {

                $listingProductsIds = $data['listing_products_ids'];
                unset($data['listing_products_ids']);

                foreach ($listingProductsIds as $listingProductId) {
                    $sessionData[$listingProductId] = $data;
                }

                unset($sessionData[$categoryId]);
            }
        }

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $templatesData = $this->getUniqueTemplatesData($sessionData);

        $specificsData = $this->getSessionValue('specifics');

        foreach ($templatesData as $templateData) {

            $listingProductsIds = $templateData['listing_products_ids'];
            $listingProductsIds = array_unique($listingProductsIds);

            if (empty($listingProductsIds)) {
                continue;
            }

            // save category template & specifics
            //------------------------------
            $builderData = $templateData;
            $builderData['motors_specifics_attribute'] = $specificsData[$templateData['identifier']]['motors_specifics_attribute'];
            $builderData['variation_enabled'] = $specificsData[$templateData['identifier']]['variation_enabled'];
            $builderData['specifics'] = $specificsData[$templateData['identifier']]['specifics'];

            unset($builderData['identifier']);
            unset($builderData['listing_products_ids']);

            $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
            //------------------------------

            $connWrite->update(
                Mage::getSingleton('core/resource')->getTableName('M2ePro/Ebay_Listing_Product'),
                array('template_category_id' => $categoryTemplate->getId()),
                'listing_product_id IN ('.implode(',',$listingProductsIds).')'
            );
        }

        $this->endWizard();
        $this->endListingCreation();

        exit;
    }

    private function getUniqueTemplatesData($templatesData)
    {
        $unique = array();

        foreach ($templatesData as $iListingProductId => $templateData) {

            $hash = md5(json_encode($templateData));

            if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $templateData['identifier'] = $templateData['category_main_id'];
            } else {
                $templateData['identifier'] = $templateData['category_main_attribute'];
            }

            if (empty($templateData['identifier'])) {
                continue;
            }

            !isset($unique[$hash]) && $unique[$hash] = array();

            $unique[$hash] = array_merge($unique[$hash], $templateData);
            $unique[$hash]['listing_products_ids'][] = $iListingProductId;
        }

        return array_values($unique);
    }

    //#############################################

    private function getCategoriesIdsByListingProductsIds($listingProductsIds)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('in' => $listingProductsIds));

        $productsIds = array();
        foreach ($listingProductCollection->getData() as $item) {
            $productsIds[] = $item['product_id'];
        }
        $productsIds = array_unique($productsIds);

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        return Mage::helper('M2ePro/Magento_Category')->getLimitedCategoriesByProducts(
            $productsIds,
            $listing->getStoreId()
        );
    }

    //#############################################

    private function addCategoriesPath(&$templateData,Ess_M2ePro_Model_Listing $listing)
    {
        $marketplaceId = $listing->getData('marketplace_id');
        $accountId = $listing->getAccountId();

        if (isset($templateData['category_main_mode'])) {
            if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $templateData['category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category')->getPathById(
                    $templateData['category_main_id'],
                    $marketplaceId
                );
            } else {
                $templateData['category_main_path'] = null;
            }
        }

        if (isset($templateData['category_secondary_mode'])) {
            if ($templateData['category_secondary_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $templateData['category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category')->getPathById(
                    $templateData['category_secondary_id'],
                    $marketplaceId
                );
            } else {
                $templateData['category_secondary_path'] = null;
            }
        }

        if (isset($templateData['store_category_main_mode'])) {
            if ($templateData['store_category_main_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY) {
                $templateData['store_category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                    ->getStorePathById(
                        $templateData['store_category_main_id'],
                        $accountId
                    );
            } else {
                $templateData['store_category_main_path'] = null;
            }
        }

        if (isset($templateData['store_category_secondary_mode'])) {
            if ($templateData['store_category_secondary_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY) {
                $templateData['store_category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                    ->getStorePathById(
                        $templateData['store_category_secondary_id'],
                        $accountId
                    );
            } else {
                $templateData['store_category_secondary_path'] = null;
            }
        }
    }

    //#############################################

    /** @return Ess_M2ePro_Model_Ebay_Listing
     * @throws Exception
     */
    private function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId)->getChildObject();
    }

    //#############################################
}