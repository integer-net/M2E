<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_AutoActionController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //#############################################

    public function indexAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $autoMode  = $this->getRequest()->getParam('auto_mode');
        $listing   = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);
        //------------------------------

        if (empty($autoMode)) {
            $autoMode = $listing->getChildObject()->getAutoMode();
        }

        $this->loadLayout();

        switch ($autoMode) {
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_global');
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_website');
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_category');
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_NONE:
            default:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode');
                break;
        }

        $this->getResponse()->setBody($block->toHtml());
    }

    // ########################################

    public function getCategoryChooserHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $autoMode  = $this->getRequest()->getParam('auto_mode');
        $listing   = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $attributes = array();
        foreach (Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAllAttributeSets() as $attribute) {
            $attributes[] = $attribute['code'];
        }

        $template = NULL;
        switch ($autoMode) {
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL:
                $template = $listing->getChildObject()->getAutoGlobalAddingCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE:
                $template = $listing->getChildObject()->getAutoWebsiteAddingCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY:
                if ($magentoCategoryId = $this->getRequest()->getParam('magento_category_id')) {
                    $autoCategory = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
                        ->getCollection()
                            ->addFieldToFilter('listing_id', $listingId)
                            ->addFieldToFilter('category_id', $magentoCategoryId)
                            ->getFirstItem();

                    if ($autoCategory->getId()) {
                        $template = $autoCategory->getCategoryTemplate();
                    }
                }
                break;
        }

        $this->loadLayout();

        /* @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('data_container');
        $chooserBlock->setAccountId($listing->getAccountId());
        $chooserBlock->setMarketplaceId($listing->getMarketplaceId());
        $chooserBlock->setAttributes($attributes);

        if (!is_null($template)) {
            $chooserBlock->setInternalData($template->getData());
        }

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    public function getCategorySpecificHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $autoMode = $this->getRequest()->getParam('auto_mode');
        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $this->loadLayout();

        /* @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());
        $specific->setDivId('data_container');
        $specific->setCategoryMode($categoryMode);
        $specific->setCategoryValue($categoryValue);

        $template = NULL;
        switch ($autoMode) {
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL:
                $template = $listing->getChildObject()->getAutoGlobalAddingCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE:
                $template = $listing->getChildObject()->getAutoWebsiteAddingCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY:
                if ($magentoCategoryId = $this->getRequest()->getParam('magento_category_id')) {
                    $autoCategory = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
                        ->getCollection()
                            ->addFieldToFilter('listing_id', $listingId)
                            ->addFieldToFilter('category_id', $magentoCategoryId)
                            ->getFirstItem();

                    if ($autoCategory->getId()) {
                        $template = $autoCategory->getCategoryTemplate();
                    }
                }
                break;
        }

        if (!is_null($template)) {
            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY
                && $template->getData('category_main_id') == $categoryValue
            ) {
                $specific->setInternalData($template->getData());
                $specific->setSelectedSpecifics($template->getSpecifics());
            }

            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE
                && $template->getData('category_main_attribute') == $categoryValue
            ) {
                $specific->setInternalData($template->getData());
                $specific->setSelectedSpecifics($template->getSpecifics());
            }
        }

        $this->getResponse()->setBody($specific->toHtml());
    }

    // ########################################

    public function getAutoCategoryFormHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_listing', $listing);
        //------------------------------

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_category_form');

        $this->getResponse()->setBody($block->toHtml());
    }

    // ########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return;
        }

        if (!isset($post['auto_action_data'])) {
            return;
        }

        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $data = json_decode($post['auto_action_data'], true);

        if (isset($data['template_category_data'])) {
            Mage::getModel('M2ePro/Ebay_Template_Category')
                ->fillCategoriesPaths(
                    $data['template_category_data'],
                    $listing->getMarketplaceId(),
                    $listing->getAccountId()
                );
        }

        $listingData = array(
            'auto_mode' => Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE,
            'auto_global_adding_template_category_id' => NULL,
            'auto_website_adding_mode' => Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE,
            'auto_website_adding_template_category_id' => NULL,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_NONE
        );

        // mode global
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL;
            $listingData['auto_global_adding_mode'] = $data['auto_global_adding_mode'];

            if ($data['auto_global_adding_mode'] == Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $builderData = $data['template_category_data'];
                $builderData['motors_specifics_attribute'] = $data['template_category_specifics_data']['motors_specifics_attribute'];
                $builderData['specifics'] = $data['template_category_specifics_data']['specifics'];
                $builderData['variation_enabled'] = Mage::helper('M2ePro/Component_Ebay_Category')
                    ->isVariationEnabledByCategoryId(
                        $data['template_category_data']['category_main_id'],
                        $listing->getMarketplaceId()
                    );

                $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);

                $listingData['auto_global_adding_template_category_id'] = $categoryTemplate->getId();
            }
        }
        //------------------------------

        // mode website
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE;
            $listingData['auto_website_adding_mode'] = $data['auto_website_adding_mode'];
            $listingData['auto_website_deleting_mode'] = $data['auto_website_deleting_mode'];

            if ($data['auto_website_adding_mode'] == Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $builderData = $data['template_category_data'];
                $builderData['motors_specifics_attribute'] = $data['template_category_specifics_data']['motors_specifics_attribute'];
                $builderData['specifics'] = $data['template_category_specifics_data']['specifics'];
                $builderData['variation_enabled'] = Mage::helper('M2ePro/Component_Ebay_Category')
                    ->isVariationEnabledByCategoryId(
                        $data['template_category_data']['category_main_id'],
                        $listing->getMarketplaceId()
                    );

                $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);

                $listingData['auto_website_adding_template_category_id'] = $categoryTemplate->getId();
            }
        }
        //------------------------------

        // mode category
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_CATEGORY;

            //------------------------------
            $group = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group');

            if ((int)$data['group_id'] > 0) {
                $group->load((int)$data['group_id']);
            }

            $group->setData('listing_id', $listingId);
            $group->setData('title', $data['group_title']);
            $group->save();
            //------------------------------

            //------------------------------
            $autoCategoryData = array();
            $autoCategoryData['group_id'] = $group->getId();
            $autoCategoryData['listing_id'] = $listingId;
            $autoCategoryData['adding_mode'] = $data['adding_mode'];
            $autoCategoryData['adding_duplicate'] = (int)$data['adding_duplicate'];
            $autoCategoryData['deleting_mode'] = $data['deleting_mode'];
            $autoCategoryData['adding_template_category_id'] = NULL;
            //------------------------------

            if ($data['adding_mode'] == Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $builderData = $data['template_category_data'];
                $builderData['motors_specifics_attribute'] = $data['template_category_specifics_data']['motors_specifics_attribute'];
                $builderData['specifics'] = $data['template_category_specifics_data']['specifics'];
                $builderData['variation_enabled'] = Mage::helper('M2ePro/Component_Ebay_Category')
                    ->isVariationEnabledByCategoryId(
                        $data['template_category_data']['category_main_id'],
                        $listing->getMarketplaceId()
                    );

                $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);

                $autoCategoryData['adding_template_category_id'] = $categoryTemplate->getId();
            }

            foreach ($data['categories'] as $categoryId) {
                $autoCategory = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
                    ->getCollection()
                        ->addFieldToFilter('listing_id', $listingId)
                        ->addFieldToFilter('category_id', $categoryId)
                        ->getFirstItem();

                $autoCategoryData['category_id'] = $categoryId;
                $autoCategory->addData($autoCategoryData)->save();
            }

            Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category')
                ->assignGroup(
                    $listingId,
                    $group->getId(),
                    $data['categories']
                );
        }
        //------------------------------

        $listing->addData($listingData)->save();
    }

    // ########################################

    public function resetAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $data = array(
            'auto_mode' => Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE,
            'auto_global_adding_template_category_id' => null,
            'auto_website_adding_mode' => Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE,
            'auto_website_adding_template_category_id' => null,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_NONE
        );

        $listing->addData($data)->save();

        foreach ($listing->getChildObject()->getAutoCategoriesGroups(true) as $autoCategoryGroup) {
            $autoCategoryGroup->deleteInstance();
        }

        foreach ($listing->getChildObject()->getAutoFilters(true) as $autoFilter) {
            $autoFilter->deleteInstance();
        }
    }

    //#############################################

    public function deleteCategoryAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $categoryId = $this->getRequest()->getParam('category_id');

        $category = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
            ->getCollection()
                ->addFieldToFilter('listing_id', (int)$listingId)
                ->addFieldToFilter('category_id', (int)$categoryId)
                ->getFirstItem();

        if (!$category->getId()) {
            return;
        }

        $category->deleteInstance();

        Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category_Group')
            ->deleteEmpty($listingId);
    }

    //#############################################

    public function deleteCategoryGroupAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')
            ->loadInstance($groupId)
            ->deleteInstance();
    }

    //#############################################

    public function isCategoryGroupTitleUniqueAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $groupId = $this->getRequest()->getParam('group_id');
        $title = $this->getRequest()->getParam('title');

        if ($title == '') {
            exit(json_encode(array('unique' => false)));
        }

        $collection = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')
            ->getCollection()
                ->addFieldToFilter('listing_id', $listingId)
                ->addFieldToFilter('title', $title);

        if ($groupId) {
            $collection->addFieldToFilter('id', array('neq' => $groupId));
        }

        exit(json_encode(array('unique' => !(bool)$collection->getSize())));
    }

    //#############################################

    public function getCategoryGroupGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_category_group_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################
}