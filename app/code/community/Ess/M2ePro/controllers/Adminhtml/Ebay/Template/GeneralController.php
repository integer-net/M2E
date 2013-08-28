<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Template_GeneralController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/templates')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('General Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Template/AttributeSetHandler.js')
             ->addJs('M2ePro/Ebay/Template/General/TabHandler.js')
             ->addJs('M2ePro/Ebay/Template/General/CategoryHandler.js')
             ->addJs('M2ePro/Ebay/Template/General/SpecificHandler.js')
             ->addJs('M2ePro/Ebay/Template/General/ShippingHandler.js')
             ->addJs('M2ePro/Ebay/Template/GeneralHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/templates/general');
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/adminhtml_template_general/index');
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        // Check Exist Marketplaces
        //-------------------------
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace');
        if ($collection->addFieldToFilter('status',1)->getSize() == 0) {
            $url = $this->getUrl(
                '*/adminhtml_marketplace/index', array('tab' => Ess_M2ePro_Helper_Component_Ebay::NICK)
            );
            $error  = 'Please select and update eBay <a href="%s" target="_blank">';
            $error .= 'marketplaces</a> before adding new General Templates.';
            $this->_getSession()->addError(Mage::helper('M2ePro')->__($error, $url));
            return $this->_redirect('*/adminhtml_template_general/index');
        }
        //-------------------------

        // Check Exist Accounts
        //-------------------------
        if (Mage::getModel('M2ePro/Ebay_Account')->getCollection()->getSize() == 0) {
            $url = $this->getUrl('*/adminhtml_ebay_account/new');
//->__('Please add eBay <a href="%s" target="_blank">accounts</a> before adding new General Templates.')
            $errorMessage  = 'Please add eBay <a href="%s" target="_blank">accounts</a>';
            $errorMessage .= ' before adding new General Templates.';
            $this->_getSession()->addError(Mage::helper('M2ePro')->__($errorMessage, $url));
            return $this->_redirect('*/adminhtml_template_general/index');
        }
        //-------------------------

        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_General')->load($id);

        if ($id && !$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist.'));
            return $this->_redirect('*/adminhtml_template_general/index');
        }

        $data = array();

        // Parse product details
        //-------------------------
        $tempProductsDetails = $model->getData('product_details');
        if ($tempProductsDetails != '') {
            $data = array_merge($data,json_decode($tempProductsDetails,true));
        }
        //-------------------------

        // Load payments
        //-------------------------
        $payments = Mage::getModel('M2ePro/Ebay_Template_General_Payment')
                                ->getCollection()
                                ->addFieldToFilter('template_general_id', $id);
        $data['payments'] = $payments->getColumnValues('payment_id');
        //-------------------------

        // Load shipping methods
        //-------------------------
        $shippings = Mage::getModel('M2ePro/Ebay_Template_General_Shipping')
                                ->getCollection()
                                ->addFieldToFilter('template_general_id', $id)
                                ->setOrder('priority', 'ASC')
                                ->toArray();
        $data['shippings'] = $shippings['items'];
        //-------------------------

        // Load calculated shipping
        //-------------------------
        $calculatedShipping = Mage::getModel('M2ePro/Ebay_Template_General_CalculatedShipping')->load($id)->toArray();
        $data = array_merge($data, $calculatedShipping);
        //-------------------------

        // Load International Trade
        //-------------------------
        $internationalTrade = $model->getData('international_trade');
        if (!is_null($internationalTrade)) {
            $internationalTrade = json_decode($model->getData('international_trade'), true);
            $data = array_merge($data, $internationalTrade);
        }

        // Load item specifics
        //-------------------------
        $itemSpecifics = Mage::getModel('M2ePro/Ebay_Template_General_Specific')
                                ->getCollection()
                                ->addFieldToFilter('template_general_id', $id)
                                ->toArray();
        $data['item_specifics'] = array();
        foreach ($itemSpecifics['items'] as $specific) {

            $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_EBAY_RECOMMENDED;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = json_decode($specific['value_ebay_recommended'],true);
            }
            unset($specific['value_ebay_recommended']);

            if ($specific['value_mode'] == Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_VALUE) {
                $specific['value_data'] = $specific['value_custom_value'];
            }
            unset($specific['value_custom_value']);

            $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
            if ($specific['value_mode'] == $temp) {
                $specific['value_data'] = $specific['value_custom_attribute'];
            }
            unset($specific['value_custom_attribute']);

            unset($specific['id']);
            unset($specific['template_general_id']);
            unset($specific['update_date']);
            unset($specific['create_date']);

            $data['item_specifics'][] = $specific;
        }
        //-------------------------

        $model->addData($data);

        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL;
        $templateAttributeSetsCollection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('object_id', $id)
                                        ->addFieldToFilter('object_type', $temp);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');

        $model->setData('attribute_sets', $templateAttributeSetsCollection->getColumnValues('attribute_set_id'));

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_general_edit'))
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_general_edit_tabs'))
             ->renderLayout();
    }

    //#############################################

    public function getMarketplaceInfoAction()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');
        $tableDictShipping = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_shipping');
        $tableDictShippingCategory = Mage::getSingleton('core/resource')
            ->getTableName('m2epro_ebay_dictionary_shipping_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplace,'*')
                             ->where('`marketplace_id` = ?',(int)$id);
        $marketplace = $connRead->fetchRow($dbSelect);

        $dbSelect = $connRead->select()
                             ->from($tableDictShipping,'*')
                             ->where('`marketplace_id` = ?',(int)$id)
                             ->order(array('title ASC'));
        $shippings = $connRead->fetchAll($dbSelect);

        $dbSelect = $connRead->select()
                             ->from($tableDictShippingCategory,'*')
                             ->where('`marketplace_id` = ?',(int)$id)
                             ->order(array('title ASC'));
        $shippingCategories = $connRead->fetchAll($dbSelect);

        $dataShippings = array();
        foreach ($shippingCategories as $category) {
            $dataShippings[$category['ebay_id']] = array(
                'title'   => $category['title'],
                'methods' => array(),
            );
        }

        foreach ($shippings as $shipping) {
            $shipping['data'] = json_decode($shipping['data'], true);
            $dataShippings[$shipping['category']]['methods'][] = $shipping;
        }

        exit(json_encode(array(
            'dispatch'           => json_decode($marketplace['dispatch'], true),
            'packages'           => json_decode($marketplace['packages'], true),
            'return_policy'      => json_decode($marketplace['return_policy'], true),
            'listing_features'   => json_decode($marketplace['listing_features'], true),
            'payments'           => json_decode($marketplace['payments'], true),
            'shipping'           => $dataShippings,
            'shipping_locations' => json_decode($marketplace['shipping_locations'], true),
            'shipping_locations_exclude' => json_decode($marketplace['shipping_locations_exclude'], true),
            'tax_categories'     => json_decode($marketplace['tax_categories'], true)
        )));
    }

    //#############################################

    public function getChildCategoriesAction()
    {
        $marketplaceId  = $this->getRequest()->getParam('marketplace_id',0);
        $parentCategoryId  = $this->getRequest()->getParam('parent_id',0);

        $data = Mage::helper('M2ePro/Component_Ebay')
                        ->getCachedObject('Marketplace',$marketplaceId)
                        ->getChildObject()
                        ->getChildCategories($parentCategoryId);

        exit(json_encode($data));
    }

    public function getCategoriesTreeByCategoryIdAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id',0);
        $categoryId  = $this->getRequest()->getParam('category_id');

        $data = array();
        $selectedIds = array();

        for ($i = 1; $i < 8; $i++) {

            $category = Mage::helper('M2ePro/Component_Ebay')
                        ->getCachedObject('Marketplace',$marketplaceId)
                        ->getChildObject()
                        ->getCategory($categoryId);

            if (!$category) {
                $data['error']  = Mage::helper('M2ePro')->__('Category with ID: %s is not found.', $categoryId);
                $data['error'] .= ' ' . Mage::helper('M2ePro')->__('Ensure that you entered correct ID.');
                break;
            }

            if ($i == 1 && !$category['is_leaf']) {
                $data['error'] = Mage::helper('M2ePro')->__('Category with ID: %s is non leaf and cannot be used.',
                                                            $categoryId);
                break;
            }

            $selectedIds[] = (int)$category['category_id'];

            if (!$category['parent_id']) {
                break;
            }

            $categoryId = (int)$category['parent_id'];
        }

        $data['selected'] = array_reverse($selectedIds);

        exit(json_encode($data));
    }

    public function getCategoryInformationAction()
    {
        $customOnly = (int)$this->getRequest()->getParam('only_custom', 0);
        if ($customOnly != 0) {
            $generalId = $this->getRequest()->getParam('general_id', '');

            if ($generalId !== '') {
                $generalId = (int)$generalId;
                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_CUSTOM_ITEM_SPECIFICS;
                $customSpecifics = Mage::getModel('M2ePro/Ebay_Template_General_Specific')
                    ->getCollection()
                    ->addFieldToFilter('template_general_id', $generalId)
                    ->addFieldToFilter('mode', $temp)
                    ->toArray();

                if (count($customSpecifics['items']) > 0) {
                    $response = array('specifics' => $customSpecifics['items']);
                    exit(json_encode($response));
                } else {
                    exit(json_encode(array()));
                }
            }
        }
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');
        $tableDictMarketplaces =Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        // Prepare input data
        //------------------------
        $marketplaceId = (int)$this->getRequest()->getParam('marketplace_id', 0);
        $selectedCategoriesIds = (string)$this->getRequest()->getParam('category_id', 0);
        $selectedCategoriesIds = explode(',', $selectedCategoriesIds);

        if (count($selectedCategoriesIds) == 0) {
            return '';
        }
        //------------------------

        // Get categories features defaults
        //------------------------
        $dbSelect = $connRead->select()
                             ->from($tableDictMarketplaces,'categories_features_defaults')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId);
        $categoriesFeaturesDefaults = $connRead->fetchRow($dbSelect);
        $categoriesFeaturesDefaults = json_decode($categoriesFeaturesDefaults['categories_features_defaults']);
        //------------------------

        // Get categories features
        //------------------------
        $dbSelect = $connRead->select()
                             ->from($tableDictCategories,'*')
                             ->where('`marketplace_id` = ?',(int)$marketplaceId);

        $sqlClauseCategories = '';
        foreach ($selectedCategoriesIds as $categoryId) {
            if ($sqlClauseCategories != '') {
                $sqlClauseCategories .= ' OR ';
            }
            $sqlClauseCategories .= ' `category_id` = '.(int)$categoryId;
        }

        $dbSelect->where('('.$sqlClauseCategories.')')
                 ->order(array('level ASC'));

        $resultCategoriesRows = $connRead->fetchAll($dbSelect);
        //------------------------

        // Merge features defaults with categories
        //------------------------
        $rowLeafCategory = NULL;
        $response = (array)$categoriesFeaturesDefaults;

        foreach ($resultCategoriesRows as $rowCategory) {
            if (!is_null($rowCategory['features'])) {
                $response =  array_merge($response, (array)json_decode($rowCategory['features'], true));
            }
            if ((bool)$rowCategory['is_leaf']) {
                $rowLeafCategory = $rowCategory;
            }
        }
        //------------------------

        // Get Item specifics
        //------------------------
        $itemSpecific = array(
            'mode' => Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_ITEM_SPECIFICS,
            'mode_relation_id' => 0,
            'specifics' => array()
        );

        if (!is_null($rowLeafCategory)) {

            if (isset($response['item_specifics_enabled'])) {
                if ((bool)$response['item_specifics_enabled']) {

                    $itemSpecific['mode'] = Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_ITEM_SPECIFICS;
                    $itemSpecific['mode_relation_id'] = (int)$rowLeafCategory['category_id'];

                    //---------
                    if (!is_null($rowLeafCategory['item_specifics'])) {
                        $itemSpecific['specifics'] = json_decode($rowLeafCategory['item_specifics'],true);
                    } else {
                        $itemSpecific['specifics'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                            ->processVirtualAbstract(
                                'marketplace','get','categorySpecifics',
                                array('category_id'=>$rowLeafCategory['category_id']),'specifics',
                                $rowLeafCategory['marketplace_id'],NULL,NULL
                        );

                        if (!is_null($itemSpecific['specifics'])) {

                            $tempData = array(
                                'marketplace_id' => (int)$rowLeafCategory['marketplace_id'],
                                'category_id' => (int)$rowLeafCategory['category_id'],
                                'item_specifics' => json_encode($itemSpecific['specifics'])
                            );

                            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
                            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                            $connWrite->insertOnDuplicate($tableDictCategories, $tempData);

                        } else {
                            $itemSpecific['specifics'] = array();
                        }
                    }
                    //---------
                    $generalId = $this->getRequest()->getParam('general_id', '');

                    if ($generalId !== '') {
                        $generalId = (int)$generalId;
                        $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_CUSTOM_ITEM_SPECIFICS;
                        $customSpecifics = Mage::getModel('M2ePro/Ebay_Template_General_Specific')
                            ->getCollection()
                            ->addFieldToFilter('template_general_id', $generalId)
                            ->addFieldToFilter('mode', $temp)
                            ->toArray();

                        if (count($customSpecifics['items']) > 0) {
                            $itemSpecific['specifics'] = array_merge(
                                $itemSpecific['specifics'], $customSpecifics['items']
                            );
                        }
                    }

                }
            }

            if (count($itemSpecific['specifics']) == 0) {
                if (isset($response['attribute_conversion_enabled'])) {
                    if ((bool)$response['attribute_conversion_enabled']) {

                        $itemSpecific['mode'] = Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_ATTRIBUTE_SET;
                        $itemSpecific['mode_relation_id'] = (int)$rowLeafCategory['attribute_set_id'];

                        //---------
                        if (!is_null($rowLeafCategory['attribute_set'])) {
                            $itemSpecific['specifics'] = json_decode($rowLeafCategory['attribute_set'],true);
                        } else {
                            $itemSpecific['specifics'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                                ->processVirtualAbstract(
                                    'marketplace','get','attributesCS',
                                    array('attribute_set_id'=>(int)$rowLeafCategory['attribute_set_id']),'specifics',
                                    $rowLeafCategory['marketplace_id'],NULL,NULL
                            );

                            if (!is_null($itemSpecific['specifics'])) {

                                $tempData = array(
                                    'marketplace_id' => (int)$rowLeafCategory['marketplace_id'],
                                    'category_id' => (int)$rowLeafCategory['category_id'],
                                    'attribute_set' => json_encode($itemSpecific['specifics'])
                                );

                                /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
                                $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                                $connWrite->insertOnDuplicate($tableDictCategories, $tempData);

                            } else {
                                $itemSpecific['specifics'] = array();
                            }
                        }
                        //---------
                    }
                }
            }
        }

        $response['item_specifics'] = $itemSpecific;
        //------------------------
        exit(json_encode($response));
    }

    //#############################################

    public function getEbayStoreByAccountAction()
    {
        // Get selected account
        //------------------------------
        $accountId = $this->getRequest()->getParam('account_id');
        /** @var $account Ess_M2ePro_Model_Ebay_Account */
        $account = Mage::helper('M2ePro/Component_Ebay')
                                ->getCachedObject('Account',$accountId)
                                ->getChildObject();
        //------------------------------

        // Get eBay store information
        //------------------------------
        $store = array(
            'title'              => Mage::helper('M2ePro')->escapeHtml($account->getEbayStoreTitle()),
            'url'                => $account->getEbayStoreUrl(),
            'subscription_level' => Mage::helper('M2ePro')->escapeHtml($account->getEbayStoreSubscriptionLevel()),
            'description'        => Mage::helper('M2ePro')->escapeHtml($account->getEbayStoreDescription())
        );
        //------------------------------

        // Get eBay store categories
        //------------------------------
        $categories = $account->getEbayStoreCategories();
        $treeTemp = array(); $treeFinal = array();
        foreach ($categories as $category) {
            $treeTemp[$category['parent_id']][$category['category_id']] = $category;
        }
        $this->ebayStoreBuildTree($treeTemp, $treeFinal);
        //------------------------------

        exit(json_encode(array(
            'information'   => $store,
            'categories' => $treeFinal
        )));
    }

    public function updateEbayStoreByAccountAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        /** @var $account Ess_M2ePro_Model_Ebay_Account */
        $account = Mage::helper('M2ePro/Component_Ebay')
                                ->getCachedObject('Account',$accountId)
                                ->getChildObject();

        $account->updateEbayStoreInfo();
    }

    private function ebayStoreBuildTree($tree, &$flatTree, $pid = 0, $level = 0)
    {
        if (empty($tree[$pid])) {
            return;
        }

        foreach ($tree[$pid] as $i => $c) {

            $id = $tree[$pid][$i]['category_id'];
            $isLeaf = empty($tree[$id]) ? 1 : 0;

            $prefix = '';
            if($level > 0) {
                $prefix = str_repeat('&nbsp;', $isLeaf ? 2:  4);
                $prefix .= str_repeat('|---&nbsp;&nbsp;', $level);
            }

            $flatTree[] = array(
                'id'    => $id,
                'title' => $prefix . $tree[$pid][$i]['title'],
                'is_leaf' => $isLeaf
            );

            $this->ebayStoreBuildTree($tree, $flatTree, $tree[$pid][$i]['category_id'], $level + 1);
        }
    }

    public function updateShippingDiscountProfilesByAccountAction()
    {
        $accountId = $this->getRequest()->getParam('account_id');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        /** @var $account Ess_M2ePro_Model_Ebay_Account */
        $account = Mage::helper('M2ePro/Component_Ebay')
                                ->getCachedObject('Account',$accountId)
                                ->getChildObject();
        $account->updateShippingDiscountProfiles($marketplaceId);

        $accountProfiles = json_decode($account->getData('ebay_shipping_discount_profiles'), true);

        $profiles = array();
        if (is_array($accountProfiles)) {
            foreach ($accountProfiles as $profileMarketplaceId => $marketplaceProfiles) {
                if (!isset($marketplaceProfiles['profiles'])) {
                    continue;
                }

                foreach ($marketplaceProfiles['profiles'] as $profile) {
                    $profiles[] = array(
                        'marketplace_id' => $profileMarketplaceId,
                        'type' => Mage::helper('M2ePro')->escapeHtml($profile['type']),
                        'profile_id' => Mage::helper('M2ePro')->escapeHtml($profile['profile_id']),
                        'profile_name' => Mage::helper('M2ePro')->escapeHtml($profile['profile_name'])
                    );
                }
            }
        }

        exit(json_encode(array(
            'id' => $accountId,
            'profiles' => $profiles
        )));
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_template_general/index');
        }

        $id = $this->getRequest()->getParam('id');
        $coreRes = Mage::getSingleton('core/resource');
        $connWrite = $coreRes->getConnection('core_write');

        // Base prepare
        //--------------------
        $data = array();
        //--------------------

        // tab: general
        //--------------------
        $keys = array(
            'title',

            'account_id',
            'marketplace_id',

            'categories_mode',
            'categories_main_id',
            'categories_main_attribute',
            'categories_secondary_id',
            'categories_secondary_attribute',
            'tax_category',
            'tax_category_attribute',

            'store_categories_main_mode',
            'store_categories_main_attribute',
            'store_categories_secondary_mode',
            'store_categories_secondary_attribute',

            'sku_mode',

            'variation_enabled',
            'variation_ignore'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);

        $data['store_categories_main_id'] = isset($post['store_categories_main_id'])
            ? $post['store_categories_main_id'] : '';
        $data['store_categories_secondary_id'] = isset($post['store_categories_secondary_id'])
            ? $post['store_categories_secondary_id'] : '';

        $tempMultivariationMarketplacesIds = Mage::getModel('M2ePro/Ebay_Marketplace')->getMultivariationIds();
        if (!in_array((int)$data['marketplace_id'],$tempMultivariationMarketplacesIds)) {
            $data['variation_enabled'] = Ess_M2ePro_Model_Ebay_Template_General::VARIATION_DISABLED;
            $data['variation_ignore'] = Ess_M2ePro_Model_Ebay_Template_General::VARIATION_IGNORE_DISABLED;
        } elseif ((int)$data['categories_mode'] == Ess_M2ePro_Model_Ebay_Template_General::CATEGORIES_MODE_ATTRIBUTE) {
            $data['variation_enabled'] = Ess_M2ePro_Model_Ebay_Template_General::VARIATION_ENABLED;
        }

        //--------------------

        // tab: specifics
        //--------------------
        $keys = array(
            'product_details_isbn_mode',
            'product_details_isbn_cv',
            'product_details_isbn_ca',

            'product_details_epid_mode',
            'product_details_epid_cv',
            'product_details_epid_ca',

            'product_details_upc_mode',
            'product_details_upc_cv',
            'product_details_upc_ca',

            'product_details_ean_mode',
            'product_details_ean_cv',
            'product_details_ean_ca'
        );

        $data['product_details'] = array();
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data['product_details'][$key] = $post[$key];
            }
        }
        $data['product_details'] = json_encode($data['product_details']);

        $data['condition_value'] = isset($post['condition_value']) ? $post['condition_value'] : '';
        $data['condition_attribute'] = isset($post['condition_attribute']) ? $post['condition_attribute'] : '';
        $data['condition_mode'] = isset($post['item_condition_mode']) ? $post['item_condition_mode'] : '';

        $data['motors_specifics_attribute'] = isset($post['motors_specifics_attribute'])
            ? $post['motors_specifics_attribute'] : '';

        if ($data['categories_mode'] == Ess_M2ePro_Model_Ebay_Template_General::CATEGORIES_MODE_ATTRIBUTE) {
            $data['condition_mode'] = Ess_M2ePro_Model_Ebay_Template_General::CONDITION_MODE_ATTRIBUTE;
        }
        //--------------------

        // tab: listing upgrades
        //--------------------
        isset($post['enhancement']) || $post['enhancement'] = array();
        $data['enhancement'] = implode(',', $post['enhancement']);
        $data['gallery_type'] = $post['gallery_type'];
        //--------------------

        // tab: shipping
        //--------------------
        $data['country'] = $post['country'];
        $data['postal_code'] = $post['postal_code'];
        $data['address'] = $post['address'];

        $data['use_ebay_local_shipping_rate_table'] = isset($post['use_ebay_local_shipping_rate_table'])
            ? $post['use_ebay_local_shipping_rate_table'] : 0;
        $data['use_ebay_international_shipping_rate_table'] = isset($post['use_ebay_international_shipping_rate_table'])
            ? $post['use_ebay_international_shipping_rate_table'] : 0;
        $data['use_ebay_tax_table'] = isset($post['use_ebay_tax_table'])
            ? $post['use_ebay_tax_table'] : 0;
        $data['vat_percent'] = isset($post['vat_percent'])
            ? str_replace(',', '.', $post['vat_percent']) : 0;

        $data['get_it_fast'] = isset($post['get_it_fast'])
            ? $post['get_it_fast'] : Ess_M2ePro_Model_Ebay_Template_General::GET_IT_FAST_DISABLED;
        $data['dispatch_time'] = isset($post['dispatch_time'])
            ? $post['dispatch_time'] : '';

        $data['local_shipping_mode'] = $post['local_shipping_mode'];
        $data['local_shipping_discount_mode'] = empty($post['local_shipping_discount_mode']) ? 0 : 1;

        $profileId = empty($post['local_shipping_combined_discount_profile_id'])
            ? ''
            : $post['local_shipping_combined_discount_profile_id'];
        $data['local_shipping_combined_discount_profile_id'] = $profileId;

        $data['local_shipping_cash_on_delivery_cost_mode'] = isset($post['local_shipping_cash_on_delivery_cost_mode'])
            ? $post['local_shipping_cash_on_delivery_cost_mode']
            : Ess_M2ePro_Model_Ebay_Template_General::CASH_ON_DELIVERY_COST_MODE_NONE;
        $data['local_shipping_cash_on_delivery_cost_value'] = isset($post['local_shipping_cash_on_delivery_cost_value'])
            ? str_replace(',', '.', $post['local_shipping_cash_on_delivery_cost_value'])
            : 0;
        $data['local_shipping_cash_on_delivery_cost_attribute'] = isset(
            $post['local_shipping_cash_on_delivery_cost_attribute']
        ) ? $post['local_shipping_cash_on_delivery_cost_attribute'] : '';

        $data['international_shipping_mode'] = isset($post['international_shipping_mode'])
            ? $post['international_shipping_mode']
            : Ess_M2ePro_Model_Ebay_Template_General::SHIPPING_TYPE_NO_INTERNATIONAL;
        $data['international_shipping_discount_mode'] = empty($post['international_shipping_discount_mode'])
            ? 0 : 1;

        $profileId = empty($post['international_shipping_combined_discount_profile_id'])
            ? ''
            : $post['international_shipping_combined_discount_profile_id'];
        $data['international_shipping_combined_discount_profile_id'] = $profileId;
        //--------------------

        // tab: payment
        //--------------------
        $data['pay_pal_email_address'] = $post['pay_pal_email_address'];
        $data['pay_pal_immediate_payment'] =  isset($post['pay_pal_immediate_payment'])
            ? $post['pay_pal_immediate_payment'] : 0;
        //--------------------

        // tab: return policy
        //--------------------
        $data['refund_accepted'] = $post['refund_accepted'];
        $data['refund_option'] = isset($post['refund_option']) ? $post['refund_option'] : '';
        $data['refund_within'] = isset($post['refund_within']) ? $post['refund_within'] : '';
        $data['refund_shippingcost'] =isset($post['refund_shippingcost']) ? $post['refund_shippingcost'] : '';
        $data['refund_restockingfee'] =isset($post['refund_restockingfee']) ? $post['refund_restockingfee'] : '';
        $data['refund_description'] = isset($post['refund_description']) ? $post['refund_description'] : '';
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_General');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        // International Trade
        //--------------------
        $connRead = $coreRes->getConnection('core_read');
        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');
        $dbSelect = $connRead->select()
            ->from($tableDictMarketplace,'categories_features_defaults')
            ->where('`marketplace_id` = ?',(int)$data['marketplace_id']);
        $categoriesFeatures = $connRead->fetchRow($dbSelect);
        $categoriesFeatures = json_decode($categoriesFeatures['categories_features_defaults'], true);

        $internationalTrade = array();
        if ((int)$post['categories_mode'] == Ess_M2ePro_Model_Ebay_Template_General::CATEGORIES_MODE_EBAY) {
            if ($categoriesFeatures['na_trade_enabled'] == 1 && isset($post['international_trade_na'])) {
                $internationalTrade['international_trade_na'] = (int)$post['international_trade_na'];
            }

            if ($categoriesFeatures['au_trade_enabled'] == 1 && isset($post['international_trade_au'])) {
                $internationalTrade['international_trade_au'] = (int)$post['international_trade_au'];
            }

            if ($categoriesFeatures['uk_trade_enabled'] == 1 && isset($post['international_trade_uk'])) {
                $internationalTrade['international_trade_uk'] = (int)$post['international_trade_uk'];
            }
        }

        $model->load($id)->setSettings('international_trade', $internationalTrade);
        $model->save();

        // Attribute sets
        //--------------------
        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL;
        $oldAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                                    ->getCollection()
                                    ->addFieldToFilter('object_type',$temp)
                                    ->addFieldToFilter('object_id',(int)$id)
                                    ->getItems();
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
            $oldAttributeSet->deleteInstance();
        }

        if (!is_array($post['attribute_sets'])) {
            $post['attribute_sets'] = explode(',', $post['attribute_sets']);
        }
        foreach ($post['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL,
                'object_id' => (int)$id,
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }
        //--------------------

        // tab: item specifics
        //--------------------
        $connWrite->delete(Mage::getResourceModel('M2ePro/Ebay_Template_General_Specific')->getMainTable(),
                           array('template_general_id = ?'=>(int)$id));

        $itemSpecifics = array();
        for ($i=0; true; $i++) {
            if (!isset($post['item_specifics_mode_'.$i])) {
                break;
            }
            if (!isset($post['custom_item_specifics_value_mode_'.$i])) {
                continue;
            }
            $ebayRecommendedTemp = array();
            if (isset($post['item_specifics_value_ebay_recommended_'.$i])) {
                $ebayRecommendedTemp = (array)$post['item_specifics_value_ebay_recommended_'.$i];
            }
            foreach ($ebayRecommendedTemp as $key=>$temp) {
                $tempParsed = explode('-|-||-|-',$temp);
                $ebayRecommendedTemp[$key] = array(
                    'id' => base64_decode($tempParsed[0]),
                    'value' => base64_decode($tempParsed[1])
                );
            }

            $attributeValue = '';
            $customAttribute = '';

            $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_ITEM_SPECIFICS;
            if ($post['item_specifics_mode_'.$i] == $temp) {
                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_VALUE;
                if ((int)$post['item_specifics_value_mode_' . $i] == $temp) {
                    $attributeValue = $post['item_specifics_value_custom_value_'.$i];
                    $customAttribute = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                    $attributeValue = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_EBAY_RECOMMENDED;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = '';
                    $attributeValue = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_NONE;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = '';
                    $attributeValue = '';
                }

                $itemSpecifics[] = array(
                    'template_general_id'    => (int)$id,
                    'mode'                   => (int)$post['item_specifics_mode_'.$i],
                    'mode_relation_id'       => (int)$post['item_specifics_mode_relation_id_'.$i],
                    'attribute_id'           => $post['item_specifics_attribute_id_'.$i],
                    'attribute_title'        => $post['item_specifics_attribute_title_'.$i],
                    'value_mode'             => (int)$post['item_specifics_value_mode_'.$i],
                    'value_ebay_recommended' => json_encode($ebayRecommendedTemp),
                    'value_custom_value'     => $attributeValue,
                    'value_custom_attribute' => $customAttribute
                );
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_CUSTOM_ITEM_SPECIFICS;
            if ($post['item_specifics_mode_'.$i] == $temp) {
                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_VALUE;
                if ((int)$post['custom_item_specifics_value_mode_' . $i] == $temp) {
                    $attributeTitle = $post['custom_item_specifics_label_custom_value_'.$i];
                    $attributeValue = $post['item_specifics_value_custom_value_'.$i];
                    $customAttribute = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
                if ((int)$post['custom_item_specifics_value_mode_'.$i] == $temp) {
                    $attributeTitle = $post['item_specifics_value_custom_attribute_'.$i];;
                    $attributeValue = '';
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_General_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
                if ((int)$post['custom_item_specifics_value_mode_'.$i] == $temp) {
                    $attributeTitle = $post['custom_item_specifics_label_custom_label_attribute_'.$i];
                    $attributeValue = '';
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                }

                $itemSpecifics[] = array(
                    'template_general_id'       => (int)$id,
                    'mode'                      => (int)$post['item_specifics_mode_' . $i],
                    'mode_relation_id'          => 0,
                    'attribute_id'              => 0,
                    'attribute_title'           => $attributeTitle,
                    'value_mode'                => (int)$post['custom_item_specifics_value_mode_' . $i],
                    'value_ebay_recommended'    => json_encode(array()),
                    'value_custom_value'        => $attributeValue,
                    'value_custom_attribute'    => $customAttribute
                );
            }
        }

        if (count($itemSpecifics) > 0) {
            $connWrite->insertMultiple($coreRes->getTableName('M2ePro/Ebay_Template_General_Specific'), $itemSpecifics);
        }
        //--------------------

        // tab: shipping
        //--------------------
        $connWrite->delete(Mage::getResourceModel('M2ePro/Ebay_Template_General_Shipping')->getMainTable(),
                           array('template_general_id = ?'=>(int)$id));

        $shippings = array();
        foreach ($post['cost_mode'] as $i => $costMode) {

            if ($i === '%i%') { // NB! do not remove 3rd "="
                continue; // this is template, not real data
            }

            isset($post['shippingLocation'][$i]) || $post['shippingLocation'][$i] = array();
            $locations = array();
            foreach ($post['shippingLocation'][$i] as $location) {
                $locations[] = $location;
            }

            $valA = isset($post['shipping_cost_attribute'][$i]) ? $post['shipping_cost_attribute'][$i] : '';
            $valC = isset($post['shipping_cost_value'][$i]) ? $post['shipping_cost_value'][$i] : '';

            $val2A = isset($post['shipping_cost_additional_attribute'][$i])
                ? $post['shipping_cost_additional_attribute'][$i] : '';
            $val2C = isset($post['cost_additional_items'][$i]) ? $post['cost_additional_items'][$i] : '';

            $shippings[] = array(
                'template_general_id'   => $id,
                'cost_mode'             => $costMode, // 0 - free, 1 - cv, 2 - ca, 3 - calc
                'cost_value'            => $costMode == 2 ? $valA : $valC,
                'shipping_value'        => $post['shipping_service'][$i],
                'shipping_type'         => $post['shipping_type'][$i] == 'local' ? 0 : 1,
                'cost_additional_items' => $costMode == 2 ? $val2A : $val2C,
                'priority'              => $post['shipping_priority'][$i],
                'locations'             => json_encode($locations)
            );
        }
        $shippings && $connWrite->insertMultiple(
            $coreRes->getTableName('M2ePro/Ebay_Template_General_Shipping'), $shippings
        );

        $connWrite->delete(Mage::getResourceModel('M2ePro/Ebay_Template_General_CalculatedShipping')->getMainTable(),
                           array('template_general_id = ?'=>(int)$id));

        // flat local shipping with enabled rate table allows to send measurement & weight data to eBay
        if ($post['local_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_General::SHIPPING_TYPE_CALCULATED
            || $post['international_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_General::SHIPPING_TYPE_CALCULATED
            || ($post['local_shipping_mode'] == Ess_M2ePro_Model_Ebay_Template_General::SHIPPING_TYPE_FLAT
                && !empty($post['use_ebay_local_shipping_rate_table']))
        ) {
            $keys = array(
                'measurement_system',
                'originating_postal_code',

                'package_size_mode',
                'package_size_ebay',
                'package_size_attribute',

                'dimension_mode',
                'dimension_width',
                'dimension_height',
                'dimension_depth',
                'dimension_width_attribute',
                'dimension_height_attribute',
                'dimension_depth_attribute',

                'weight_mode',
                'weight_minor',
                'weight_major',
                'weight_attribute',

                'local_handling_cost_mode',
                'local_handling_cost_value',
                'local_handling_cost_attribute',

                'international_handling_cost_mode',
                'international_handling_cost_value',
                'international_handling_cost_attribute'
            );

            $calculatedShipping = array('template_general_id' => (int)$id);
            foreach ($keys as $key) {
                $calculatedShipping[$key] = isset($post[$key]) ? $post[$key] : '';
            }

            Mage::getModel('M2ePro/Ebay_Template_General_CalculatedShipping')->setData($calculatedShipping)->save();
        }
        //--------------------

        // tab: payment
        //--------------------
        $connWrite->delete(Mage::getResourceModel('M2ePro/Ebay_Template_General_Payment')->getMainTable(),
                           array('template_general_id = ?'=>(int)$id));

        isset($post['payments']) || $post['payments'] = array();

        $payments = array();
        foreach ($post['payments'] as $payment) {
            $payments[] = array(
                'template_general_id' => $id,
                'payment_id' => $payment
            );
        }
        $payments && $connWrite->insertMultiple(
            $coreRes->getTableName('M2ePro/Ebay_Template_General_Payment'), $payments
        );
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################

    public function getAttributeTypeAction()
    {
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $attribute = Mage::getResourceModel('catalog/product')->getAttribute($attributeCode);

        if ($attribute === false) {
            exit(json_encode(array('type' => null)));
        }

        exit(json_encode(array('type' => $attribute->getBackendType())));
    }

    //#############################################
}