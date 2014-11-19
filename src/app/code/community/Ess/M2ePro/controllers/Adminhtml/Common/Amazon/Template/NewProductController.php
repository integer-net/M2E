<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Template_NewProductController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    private $listingProductIds = array();

    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();
        $this->listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('listing_product_ids');
    }

    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Amazon Listings'));

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/AttributeSetHandler.js')
            ->addJs('M2ePro/Common/Amazon/Template/NewProduct/DescriptionHandler.js')
            ->addJs('M2ePro/Common/Amazon/Template/NewProduct/Handler.js')
            ->addJs('M2ePro/Common/Amazon/Template/NewProduct/SpecificHandler.js');

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
        if ($this->getRequest()->isPost()) {
            $this->saveListingProductIds();
        }

        if (empty($this->listingProductIds)) {
            return $this->_redirect('*/adminhtml_common_listing/',array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::TAB_ID_AMAZON
            ));
        }

        $collection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ->addFieldToFilter('general_id',array('null' => true))
            ->addFieldToFilter('id',array('in' => $this->listingProductIds));

        if ($collection->getSize() < 1) {
            $listingId = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product',reset($this->listingProductIds))
                ->getListingId();

            $errorMessage = Mage::helper('M2ePro')->__('Please select Not Listed items without assigned ASIN.');
            $this->_getSession()->addError($errorMessage);
            return $this->_redirect('*/adminhtml_common_amazon_listing/view',array(
                'id' => $listingId
            ));
        }

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_newProduct'))
             ->renderLayout();
    }

    public function templateNewProductGridAction()
    {
        $block = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_template_newProduct_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function addAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->_forward('save');
        }

        $this->_initAction()
             ->_addLeft($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_newProduct_edit_tabs'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_newProduct_edit'))
             ->renderLayout();
    }

    public function mapAction()
    {
        $amazonTemplateNewProductInstance = Mage::getModel('M2ePro/Amazon_Template_NewProduct')->loadInstance(
            (int)$this->getRequest()->getParam('id')
        );

        return $this->map($amazonTemplateNewProductInstance);
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();

        // Saving general data
        //----------------------------
        /** @var $amazonTemplateNewProductInstance Ess_M2ePro_Model_Amazon_Template_NewProduct */
        $amazonTemplateNewProductInstance = Mage::getModel('M2ePro/Amazon_Template_NewProduct');
        if ($post['category']['id']) {
            $amazonTemplateNewProductInstance->loadInstance((int)$post['category']['id']);
        }

        $amazonTemplateNewProductInstance->addData(array(
            'title' => $post['category']['title'],

            'marketplace_id'                => (int)$this->getRequest()->getParam('marketplace_id'),
            'xsd_hash'                      => $post['category']['xsd_hash'],
            'node_title'                    => $post['category']['node_title'],
            'category_path'                 => $post['category']['path'],
            'identifiers'                   => $post['category']['identifiers'],
            'registered_parameter'          => $post['category']['registered_parameter'],

            'worldwide_id_mode'             => $post['category']['worldwide_id_mode'],
            'worldwide_id_custom_attribute' => $post['category']['worldwide_id_custom_attribute'],

            'item_package_quantity_mode'             => $post['category']['item_package_quantity_mode'],
            'item_package_quantity_custom_value'     => $post['category']['item_package_quantity_custom_value'],
            'item_package_quantity_custom_attribute' => $post['category']['item_package_quantity_custom_attribute'],

            'number_of_items_mode'             => $post['category']['number_of_items_mode'],
            'number_of_items_custom_value'     => $post['category']['number_of_items_custom_value'],
            'number_of_items_custom_attribute' => $post['category']['number_of_items_custom_attribute'],

        ));
        $amazonTemplateNewProductInstance->save();
        //----------------------------

        // Delete old New ASIN template Attribute sets
        //--------------------
        $oldAttributeSets = $amazonTemplateNewProductInstance->getAttributeSets();
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
            $oldAttributeSet->deleteInstance();
        }

        // Add new Attribute sets to New ASIN template
        if (!is_array($post['category']['attribute_sets'])) {
            $post['category']['attribute_sets'] = explode(',', $post['category']['attribute_sets']);
        }

        foreach ($post['category']['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_AMAZON_TEMPLATE_NEW_PRODUCT,
                'object_id' => (int)$amazonTemplateNewProductInstance->getId(),
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }
        //--------------------

        // Saving description info
        //----------------------------
        $data = array();

        $data['template_new_product_id'] = $amazonTemplateNewProductInstance->getId();

        $keys = array(
            'title_mode',
            'title_template',

            'brand_mode',
            'brand_template',

            'manufacturer_mode',
            'manufacturer_template',

            'manufacturer_part_number_mode',
            'manufacturer_part_number_custom_value',
            'manufacturer_part_number_custom_attribute',

            'package_weight_mode',
            'package_weight_custom_value',
            'package_weight_custom_attribute',

            'package_weight_unit_of_measure_mode',
            'package_weight_unit_of_measure_custom_value',
            'package_weight_unit_of_measure_custom_attribute',

            'shipping_weight_mode',
            'shipping_weight_custom_value',
            'shipping_weight_custom_attribute',

            'shipping_weight_unit_of_measure_mode',
            'shipping_weight_unit_of_measure_custom_value',
            'shipping_weight_unit_of_measure_custom_attribute',

            'target_audience_mode',
            'target_audience_custom_value',
            'target_audience_custom_attribute',

            'search_terms_mode',
            'search_terms',

            'image_main_mode',
            'image_main_attribute',

            'gallery_images_mode',
            'gallery_images_attribute',
            'gallery_images_limit',

            'bullet_points_mode',
            'bullet_points',

            'description_mode',
            'description_template',
        );

        foreach ($keys as $key) {
            if (isset($post['description'][$key])) {
                $data[$key] = $post['description'][$key];
            }
        }

        $data['title'] = $post['category']['path'];
        $data['title'] .= '('.substr(md5(Mage::helper('M2ePro')->getCurrentGmtDate(true)),0,5).')';
        $data['search_terms'] = json_encode(array_filter($post['description']['search_terms']));
        $data['bullet_points'] = json_encode(array_filter($post['description']['bullet_points']));
        //----------------------------

        // Add or update model
        //--------------------
        /* @var $templateDescriptionInstance Ess_M2ePro_Model_Amazon_Template_NewProduct_Description */
        $templateDescriptionInstance = Mage::getModel('M2ePro/Amazon_Template_NewProduct_Description');
        $templateDescriptionInstance->load($amazonTemplateNewProductInstance->getId());
        $templateDescriptionInstance->addData($data)->save();
        //----------------------------

        // Saving specifics info
        //----------------------------
        $amazonTemplateNewProductInstance->deleteSpecifics();

        $this->sort($post['specifics'],$post['category']['xsd_hash']);

        foreach ($post['specifics'] as $xpath => $specificData) {

            if (empty($specificData['mode'])) {
                continue;
            }

            if (empty($specificData['recommended_value']) &&
                !in_array($specificData['mode'],array('none','custom_value','custom_attribute'))) {
                continue;
            }
            if (empty($specificData['custom_value']) &&
                !in_array($specificData['mode'],array('none','recommended_value','custom_attribute'))) {
                continue;
            }
            if (empty($specificData['custom_attribute']) &&
                !in_array($specificData['mode'],array('none','recommended_value','custom_value'))) {
                continue;
            }

            /** @var $amazonTemplateNewProductSpecificInstance Ess_M2ePro_Model_Amazon_Template_NewProduct_Specific */
            $amazonTemplateNewProductSpecificInstance = Mage::getModel('M2ePro/Amazon_Template_NewProduct_Specific');

            $type = isset($specificData['type']) ? $specificData['type'] : '';
            $attributes = isset($specificData['attributes']) ? json_encode($specificData['attributes']) : '[]';

            $recommendedValue = $specificData['mode'] == 'recommended_value' ? $specificData['recommended_value'] : '';
            $customValue = $specificData['mode'] == 'custom_value' ? $specificData['custom_value'] : '';
            $customAttribute = $specificData['mode'] == 'custom_attribute' ? $specificData['custom_attribute'] : '';

            $amazonTemplateNewProductSpecificInstance->addData(array(
                'template_new_product_id' => $amazonTemplateNewProductInstance->getId(),
                'xpath'             => $xpath,
                'mode'              => $specificData['mode'],
                'recommended_value' => $recommendedValue,
                'custom_value'      => $customValue,
                'custom_attribute'  => $customAttribute,
                'type'              => $type,
                'attributes'        => $attributes
            ));
            $amazonTemplateNewProductSpecificInstance->save();
        }
        //----------------------------

        if ($this->getRequest()->getParam('do_map')) {
            return $this->map($amazonTemplateNewProductInstance);
        }

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template has been successfully saved.'));

        if ($listingProductId = $this->getRequest()->getParam('listing_product_id')) {

            $listingId = Mage::helper('M2ePro/Component_Amazon')
            ->getObject('Listing_Product',$listingProductId)
            ->getListingId();

            return $this->_redirect('*/adminhtml_common_amazon_listing/view',array(
                'id' => $listingId
            ));
        }

        return $this->_redirect('*/adminhtml_common_amazon_template_newProduct/index',array(
            'marketplace_id' => $this->getRequest()->getParam('marketplace_id')
        ));
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return '';
        }

        /* @var  $amazonTemplateNewProductInstance Ess_M2ePro_Model_Amazon_Template_NewProduct */
        $amazonTemplateNewProductInstance = Mage::getModel('M2ePro/Amazon_Template_NewProduct')->loadInstance($id);

        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_AMAZON_TEMPLATE_NEW_PRODUCT;
        $templateAttributeSetsCollection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('object_id', $id)
                                        ->addFieldToFilter('object_type', $temp);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');

        $amazonTemplateNewProductInstance->setData(
            'attribute_sets',
            $templateAttributeSetsCollection->getColumnValues('attribute_set_id')
        );

        $formData['category']  = $amazonTemplateNewProductInstance->getData();
        $formData['description']  = $amazonTemplateNewProductInstance->getDescription()->getData();
        $formData['specifics'] = $amazonTemplateNewProductInstance->getSpecifics();

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$formData);

        return $this->_forward('add');
    }

    public function deleteAction()
    {
        $ids = $this->getRequest()->getParam('ids','');
        !is_array($ids) && $ids = array($ids);

        if (empty($ids)) {
            return;
        }

        $amazonTemplateNewProductInstances = Mage::getModel('M2ePro/Amazon_Template_NewProduct')
            ->getCollection()
            ->addFieldToFilter('id',array('in' => $ids))
            ->getItems();

        $countOfSuccessfullyDeletedTemplates = 0;

        foreach ($amazonTemplateNewProductInstances as $amazonTemplateNewProductInstance) {
            if ($amazonTemplateNewProductInstance->deleteInstance()) {
                ++$countOfSuccessfullyDeletedTemplates;
                continue;
            }
        }

        if (!$countOfSuccessfullyDeletedTemplates) {
            $this->_getSession()->addError(
                'New ASIN template(s) cannot be deleted as it has assigned product(s)'
            );
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        if ($countOfSuccessfullyDeletedTemplates == count($amazonTemplateNewProductInstances)) {
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__(
                '%amount% record(s) were successfully deleted.', $countOfSuccessfullyDeletedTemplates
            ));
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $this->_getSession()->addError(Mage::helper('M2ePro')->__(
            'Some of the New ASIN template(s) cannot be deleted as they have assigned product(s)'
        ));
        return $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################

    public function getCategoriesAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $nodeHash = $this->getRequest()->getParam('node_hash');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        return $this->getResponse()->setBody(json_encode($connRead->select()
                                  ->from($table,'*')
                                  ->where('marketplace_id = ?', $marketplaceId)
                                  ->where('node_hash = ?', $nodeHash)
                                  ->query()
                                  ->fetchAll()));
    }

    //#############################################

    public function getSpecificsAction()
    {
        $tempSpecifics = $this->getSpecifics($this->getRequest()->getParam('xsd_hash'));

        $specifics = array();
        foreach ($tempSpecifics as $tempSpecific) {
            $specifics[$tempSpecific['specific_id']] = $tempSpecific;
        }

        return $this->getResponse()->setBody(json_encode($specifics));
    }

    //#############################################

    public function getXsdsAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $nodeHash = $this->getRequest()->getParam('node_hash');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');

        $nodes = json_decode($connRead->select()
                                      ->from($table,'nodes')
                                      ->where('marketplace_id = ?', $marketplaceId)
                                      ->query()
                                      ->fetchColumn(),true);

        $xsds = array();
        foreach ($nodes as $node) {
            if ($node['hash'] == $nodeHash) {
                $xsds = $node['xsds'];
                break;
            }
        }

        return $this->getResponse()->setBody(json_encode($xsds));
    }

    //#############################################

    private function getSpecifics($xsdHash)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        // todo filter variations

        return $connRead->select()
                        ->from($table,'*')
                        ->where("title not like '%variat%'", $xsdHash)
                        ->where('xsd_hash = ?', $xsdHash)
                        ->query()
                        ->fetchAll();
    }

    //#############################################

    private function map(Ess_M2ePro_Model_Amazon_Template_NewProduct $amazonTemplateNewProductInstance)
    {
        if (count($this->listingProductIds) < 1) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('There are no items to assign.'));
            return $this->_redirect('*/adminhtml_common_listing');
        }

        $result = $amazonTemplateNewProductInstance->map($this->listingProductIds);

        $type = 'addSuccess';
        $message = Mage::helper('M2ePro')->__('Template has been successfully assigned.');

        if (!$result) {
            $type = 'addError';
            $message = Mage::helper('M2ePro')->__('Some products were not assigned.');
        }

        $listingId = Mage::helper('M2ePro/Component_Amazon')
            ->getObject('Listing_Product',reset($this->listingProductIds))
            ->getListingId();

        $this->_getSession()->$type($message);

        return $this->_redirect('*/adminhtml_common_amazon_listing/view',array(
            'id' => $listingId
        ));
    }

    //#############################################

    private function sort(&$specifics,$xsdHash)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $table =  Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        $dictionarySpecifics = $connRead->select()
                                        ->from($table,array('specific_id','xpath'))
                                        ->where('xsd_hash = ?',$xsdHash)
                                        ->query()
                                        ->fetchAll();

        foreach ($dictionarySpecifics as $key => $specific) {
            $xpath = $specific['xpath'];
            unset($dictionarySpecifics[$key]);
            $dictionarySpecifics[$xpath] = $specific['specific_id'];
        }

        Mage::helper('M2ePro/Data_Global')->setValue('dictionary_specifics',$dictionarySpecifics);

        function callback($aXpath,$bXpath)
        {
            $dictionarySpecifics = Mage::helper('M2ePro/Data_Global')->getValue('dictionary_specifics');

            $aXpathParts = explode('/',$aXpath);
            foreach ($aXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/','',$part);
            }
            unset($part);
            $aXpath = implode('/',$aXpathParts);

            $bXpathParts = explode('/',$bXpath);
            foreach ($bXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/','',$part);
            }
            unset($part);
            $bXpath = implode('/',$bXpathParts);

            $aIndex = $dictionarySpecifics[$aXpath];
            $bIndex = $dictionarySpecifics[$bXpath];

            return $aIndex > $bIndex ? 1 : -1;
        }

        uksort($specifics,'callback');
    }

    //#############################################

    public function searchCategoryAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $keywords = $this->getRequest()->getParam('keywords','');

        if (!$keywords) {
            return $this->getResponse()->setBody(json_encode(array(
                'result' => 'error',
                'message' => Mage::helper('M2ePro')->__('Please enter keywords.')
            )));
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select();
        $select->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'),'*');

        if ($marketplaceId == Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_US) {
            $select->where('item_types LIKE \'%keyword%\'');
        }
        $select->where('is_listable = 1');
        $select->where('xsd_hash != \'\'');
        $select->where('marketplace_id = ?',$marketplaceId);

        $where = '';

        $parts = explode(' ', $keywords);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part == '') {
                continue;
            }
            $where != '' && $where .= ' OR ';

            $part = $connRead->quote('%'.$part.'%');

            if ($marketplaceId == Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_US) {
                $where .= 'item_types LIKE '.$part;
            } else {
                $where .= 'title LIKE '.$part;
                $where .= ' OR path LIKE '.$part;
            }
        }

        $select->where($where);
        $select->limit(1000);
        $select->order('id ASC');

        $results = $select->query()->fetchAll();

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data',$results);

        $block = $this->loadLayout()
                      ->getLayout()
                      ->createBlock('M2ePro/adminhtml_common_amazon_template_newProduct_search_grid');
        return $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    private function saveListingProductIds()
    {
        $listingProductIds = $this->getRequest()->getParam('listing_product_ids');
        $listingProductIds = explode(',',$listingProductIds);
        $listingProductIds = array_filter(array_unique($listingProductIds));

        if (empty($listingProductIds)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__(
                'Please select at least 1 listing product.'
            ));
        }

        Mage::helper('M2ePro/Data_Session')->setValue('listing_product_ids',$listingProductIds);
        $this->listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('listing_product_ids');
    }

    //#############################################
}