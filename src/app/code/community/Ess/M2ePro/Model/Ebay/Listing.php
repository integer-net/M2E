<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const AUTO_MODE_NONE     = 0;
    const AUTO_MODE_GLOBAL   = 1;
    const AUTO_MODE_WEBSITE  = 2;
    const AUTO_MODE_CATEGORY = 3;

    const ADDING_MODE_NONE                    = 0;
    const ADDING_MODE_ADD                     = 1;
    const ADDING_MODE_ADD_AND_ASSIGN_CATEGORY = 2;

    const DELETING_MODE_NONE        = 0;
    const DELETING_MODE_STOP        = 1;
    const DELETING_MODE_STOP_REMOVE = 2;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $autoGlobalAddingCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $autoGlobalAddingOtherCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $autoWebsiteAddingCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $autoWebsiteAddingOtherCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Manager[]
     */
    private $templateManagers = array();

    //-----------------------------------------

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    private $synchronizationTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Description
     */
    private $descriptionTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Payment|Ess_M2ePro_Model_Ebay_Template_Policy
     */
    private $paymentTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Return|Ess_M2ePro_Model_Ebay_Template_Policy
     */
    private $returnTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Shipping|Ess_M2ePro_Model_Ebay_Template_Policy
     */
    private $shippingTemplateModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $categories = $this->getAutoCategories(true);
        foreach ($categories as $category) {
            $category->deleteInstance();
        }

        $this->templateManagers = array();
        $this->autoGlobalAddingCategoryTemplateModel = NULL;
        $this->autoGlobalAddingOtherCategoryTemplateModel = NULL;
        $this->autoWebsiteAddingCategoryTemplateModel = NULL;
        $this->autoWebsiteAddingOtherCategoryTemplateModel = NULL;
        $this->sellingFormatTemplateModel = NULL;
        $this->synchronizationTemplateModel = NULL;
        $this->descriptionTemplateModel = NULL;
        $this->paymentTemplateModel = NULL;
        $this->returnTemplateModel = NULL;
        $this->shippingTemplateModel = NULL;

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getAutoGlobalAddingCategoryTemplate()
    {
        if (is_null($this->autoGlobalAddingCategoryTemplateModel)) {

            try {
                $this->autoGlobalAddingCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAutoGlobalAddingTemplateCategoryId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoGlobalAddingCategoryTemplateModel;
            }
        }

        return $this->autoGlobalAddingCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setAutoGlobalAddingCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->autoGlobalAddingCategoryTemplateModel = $instance;
    }

    //-----------------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getAutoGlobalAddingOtherCategoryTemplate()
    {
        if (is_null($this->autoGlobalAddingOtherCategoryTemplateModel)) {

            try {
                $this->autoGlobalAddingOtherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAutoGlobalAddingTemplateOtherCategoryId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoGlobalAddingOtherCategoryTemplateModel;
            }
        }

        return $this->autoGlobalAddingOtherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setAutoGlobalAddingOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->autoGlobalAddingOtherCategoryTemplateModel = $instance;
    }

    //-----------------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getAutoWebsiteAddingCategoryTemplate()
    {
        if (is_null($this->autoWebsiteAddingCategoryTemplateModel)) {

            try {
                $this->autoWebsiteAddingCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAutoWebsiteAddingTemplateCategoryId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoWebsiteAddingCategoryTemplateModel;
            }
        }

        return $this->autoWebsiteAddingCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setAutoWebsiteAddingCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
         $this->autoWebsiteAddingCategoryTemplateModel = $instance;
    }

    //-----------------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getAutoWebsiteAddingOtherCategoryTemplate()
    {
        if (is_null($this->autoWebsiteAddingOtherCategoryTemplateModel)) {

            try {
                $this->autoWebsiteAddingOtherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAutoWebsiteAddingTemplateOtherCategoryId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->autoWebsiteAddingOtherCategoryTemplateModel;
            }
        }

        return $this->autoWebsiteAddingOtherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setAutoWebsiteAddingOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
         $this->autoWebsiteAddingOtherCategoryTemplateModel = $instance;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    public function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ########################################

    /**
     * @param $template
     * @return Ess_M2ePro_Model_Ebay_Template_Manager
     */
    public function getTemplateManager($template)
    {
        if (!isset($this->templateManagers[$template])) {
            /** @var Ess_M2ePro_Model_Ebay_Template_Manager $manager */
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setOwnerObject($this);
            $this->templateManagers[$template] = $manager->setTemplate($template);
        }

        return $this->templateManagers[$template];
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
            $this->sellingFormatTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->sellingFormatTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if (is_null($this->synchronizationTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
            $this->synchronizationTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->synchronizationTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
            $this->descriptionTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Ebay_Template_Description $instance)
    {
         $this->descriptionTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        if (is_null($this->paymentTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
            $this->paymentTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->paymentTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Payment $instance
     */
    public function setPaymentTemplate(Ess_M2ePro_Model_Ebay_Template_Payment $instance)
    {
         $this->paymentTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Return
     */
    public function getReturnTemplate()
    {
        if (is_null($this->returnTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;
            $this->returnTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->returnTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Return $instance
     */
    public function setReturnTemplate(Ess_M2ePro_Model_Ebay_Template_Return $instance)
    {
         $this->returnTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        if (is_null($this->shippingTemplateModel)) {
            $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
            $this->shippingTemplateModel = $this->getTemplateManager($template)->getResultObject();
        }

        return $this->shippingTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping $instance
     */
    public function setShippingTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping $instance)
    {
         $this->shippingTemplateModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects,$filters);
    }

    //-----------------------------------------

    public function getAutoCategoriesGroups($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Ebay_Listing_Auto_Category_Group','listing_id',
                                            $asObjects, $filters);
    }

    public function getAutoCategories($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Ebay_Listing_Auto_Category','listing_id',
                                            $asObjects, $filters);
    }

    // ########################################

    public function getAutoMode()
    {
        return (int)$this->getData('auto_mode');
    }

    public function isAutoModeNone()
    {
        return $this->getAutoMode() == self::AUTO_MODE_NONE;
    }

    public function isAutoModeGlobal()
    {
        return $this->getAutoMode() == self::AUTO_MODE_GLOBAL;
    }

    public function isAutoModeWebsite()
    {
        return $this->getAutoMode() == self::AUTO_MODE_WEBSITE;
    }

    public function isAutoModeCategory()
    {
        return $this->getAutoMode() == self::AUTO_MODE_CATEGORY;
    }

    // ########################################

    public function getAutoGlobalAddingMode()
    {
        return (int)$this->getData('auto_global_adding_mode');
    }

    public function getAutoGlobalAddingTemplateCategoryId()
    {
        return $this->getData('auto_global_adding_template_category_id');
    }

    public function getAutoGlobalAddingTemplateOtherCategoryId()
    {
        return $this->getData('auto_global_adding_template_other_category_id');
    }

    //----------------------------------------

    public function isAutoGlobalAddingModeNone()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_NONE;
    }

    public function isAutoGlobalAddingModeAdd()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_ADD;
    }

    public function isAutoGlobalAddingModeAddAndAssignCategory()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;
    }

    // #######################################

    public function getAutoWebsiteAddingMode()
    {
        return (int)$this->getData('auto_website_adding_mode');
    }

    public function getAutoWebsiteAddingTemplateCategoryId()
    {
        return $this->getData('auto_website_adding_template_category_id');
    }

    public function getAutoWebsiteAddingTemplateOtherCategoryId()
    {
        return $this->getData('auto_website_adding_template_other_category_id');
    }

    //----------------------------------------

    public function isAutoWebsiteAddingModeNone()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_NONE;
    }

    public function isAutoWebsiteAddingModeAdd()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_ADD;
    }

    public function isAutoWebsiteAddingModeAddAndAssignCategory()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;
    }

    // #######################################

    public function getAutoWebsiteDeletingMode()
    {
        return (int)$this->getData('auto_website_deleting_mode');
    }

    //----------------------------------------

    public function isAutoWebsiteDeletingModeNone()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_NONE;
    }

    public function isAutoWebsiteDeletingModeStop()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_STOP;
    }

    public function isAutoWebsiteDeletingModeStopRemove()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_STOP_REMOVE;
    }

    // #######################################

    public function convertPriceFromStoreToMarketplace($price)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPrice(
            $price,
            $this->getEbayMarketplace()->getCurrency(),
            $this->getParentObject()->getStoreId()
        );
    }

    public function addProductFromOther(Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
                                        $checkingMode = false,
                                        $checkHasProduct = true)
    {
        if (!$listingOtherProduct->getProductId()) {
            return false;
        }

        $productId = $listingOtherProduct->getProductId();
        $result = $this->getParentObject()->addProduct($productId, $checkingMode, true);

        if ($checkingMode) {
            return $result;
        }

        if (!($result instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $listingProduct = $result;

        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Ebay_Item')->getCollection()
            ->addFieldToFilter('account_id', $listingOtherProduct->getAccount()->getId())
            ->addFieldToFilter('item_id', $listingOtherProduct->getChildObject()->getItemId());

        $ebayItem = $collection->getFirstItem();

        $ebayItem->setData('store_id',$this->getParentObject()->getStoreId())
                 ->save();

        $dataForUpdate = array(
            'ebay_item_id' => $ebayItem->getId(),
            'online_sku' => $listingOtherProduct->getChildObject()->getSku(),
            'online_title' => $listingOtherProduct->getChildObject()->getTitle(),
            'online_buyitnow_price' => $listingOtherProduct->getChildObject()->getOnlinePrice(),
            'online_qty' => $listingOtherProduct->getChildObject()->getOnlineQty(),
            'online_qty_sold' => $listingOtherProduct->getChildObject()->getOnlineQtySold(),
            'online_bids' => $listingOtherProduct->getChildObject()->getOnlineBids(),
            'online_start_date' => $listingOtherProduct->getChildObject()->getStartDate(),
            'online_end_date' => $listingOtherProduct->getChildObject()->getEndDate(),
            'status' => $listingOtherProduct->getStatus(),
            'status_changer' => $listingOtherProduct->getStatusChanger()
        );

        $listingProduct->addData($dataForUpdate)->save();

        return $listingProduct;
    }

    // ########################################

    public function getEstimatedFees()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'data'), array());
    }

    public function setEstimatedFees(array $data)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'data'), $data);
        return $this;
    }

    public function getEstimatedFeesSourceProductName()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'source_product_name'), NULL);
    }

    public function setEstimatedFeesSourceProductName($name)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'source_product_name'), $name);
        return $this;
    }

    public function getEstimatedFeesObtainAttemptCount()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'obtain_attempt_count'), 0);
    }

    public function setEstimatedFeesObtainAttemptCount($count)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'obtain_attempt_count'), $count);
        return $this;
    }

    public function getEstimatedFeesObtainRequired()
    {
        return $this->getParentObject()
            ->getSetting('additional_data', array('estimated_fees', 'obtain_required'), true);
    }

    public function setEstimatedFeesObtainRequired($required)
    {
        $this->getParentObject()
            ->setSetting('additional_data', array('estimated_fees', 'obtain_required'), (bool)$required);
        return $this;
    }

    // ########################################

    public function increaseEstimatedFeesObtainAttemptCount()
    {
        $count = $this->getEstimatedFeesObtainAttemptCount();
        $this->setEstimatedFeesObtainAttemptCount(++$count);
        $this->getParentObject()->save();
    }

    public function isEstimatedFeesObtainRequired()
    {
        if (!$this->getEstimatedFeesObtainRequired()) {
            return false;
        }

        if ($this->getEstimatedFeesObtainAttemptCount() >= 3) {
            return false;
        }

        return true;
    }

    // ########################################

    public function getAddedListingProductsIds()
    {
        $ids = $this->getData('product_add_ids');
        $ids = array_filter((array)json_decode($ids, true));
        $ids = array_values(array_unique($ids));

        return $ids;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return array();
    }

    // ########################################

    public function getAffectedListingProducts($templates = array(
                                                   Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
                                                   Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
                                                   Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
                                                   Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
                                                   Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
                                               ), $asObjects = false, $key = NULL)
    {
        if (empty($templates)) {
            return array();
        }

        if (is_null($this->getId())) {
            throw new LogicException('Method require loaded instance first');
        }

        !is_array($templates) && $templates = array($templates);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('listing_id', $this->getId());

        if (!is_null($key)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($key);
        }

        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');

        $where = '';
        foreach ($templates as $template) {
            $templateManager->setTemplate($template);

            $where && $where .= ' OR ';
            $where .= "{$templateManager->getModeColumnName()} = ".Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT;
        }

        $collection->getSelect()->where($where);

        $listingProducts = $asObjects ? $collection->getItems() : $collection->getData();

        if (is_null($key)) {
            return $listingProducts;
        }

        $return = array();
        foreach ($listingProducts as $listingProduct) {
            isset($listingProduct[$key]) && $return[] = $listingProduct[$key];
        }

        return $return;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $this->setSynchStatusNeedByTemplates($newData,$oldData);
        $this->setSynchStatusNeedBySynchronizationTemplate($newData,$oldData);
    }

    // ----------------------------------------

    private function setSynchStatusNeedByTemplates($newData,$oldData)
    {
        $templates = array(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
                           Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
                           Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
                           Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
                           Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);

        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');

        $newTemplates = $templateManager->getTemplatesFromData($newData,$templates);
        $oldTemplates = $templateManager->getTemplatesFromData($oldData,$templates);

        $changedTemplates = array();
        foreach ($templates as $templateNick) {

            $templateManager->setTemplate($templateNick);

            $isDifferent = $newTemplates[$templateNick]->getResource()->isDifferent(
                $newTemplates[$templateNick]->getDataSnapshot(),
                $oldTemplates[$templateNick]->getDataSnapshot()
            );

            $isDifferent && $changedTemplates[] = $templateNick;
        }

        if (!$changedTemplates) {
            return;
        }

        $ids = $this->getAffectedListingProducts($changedTemplates,false,'id');

        if (empty($ids)) {
            return;
        }

        foreach ($changedTemplates as &$template) {
            if ($template == Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT) {
                $template = 'sellingFormatTemplate';
            } else {
                $template .= 'Template';
            }
        }
        unset($template);

        Mage::getSingleton('core/resource')->getConnection('core_read')->update(
            Mage::getSingleton('core/resource')->getTableName('M2ePro/Listing_Product'),
            array(
                'synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED,
                'synch_reasons' => new Zend_Db_Expr(
                    "IF(synch_reasons IS NULL,
                        '".implode(',',$changedTemplates)."',
                        CONCAT(synch_reasons,'".','.implode(',',$changedTemplates)."')
                    )"
                )
            ),
            array('id IN ('.implode(',', $ids).')')
        );
    }

    private function setSynchStatusNeedBySynchronizationTemplate($newData,$oldData)
    {
        $template = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;

        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');

        $newSynchTemplate = $templateManager->getTemplatesFromData($newData,array($template));
        $newSynchTemplate = reset($newSynchTemplate);

        $oldSynchTemplate = $templateManager->getTemplatesFromData($oldData,array($template));
        $oldSynchTemplate = reset($oldSynchTemplate);

        $newSynchTemplateSnapshot = $newSynchTemplate->getDataSnapshot();
        $oldSynchTemplateSnapshot = $oldSynchTemplate->getDataSnapshot();

        $settings = $newSynchTemplate->getFullReviseSettingWhichWereEnabled(
            $newSynchTemplateSnapshot, $oldSynchTemplateSnapshot
        );

        if (!$settings) {
            return;
        }

        $listingProducts = $this->getAffectedListingProducts($template);

        $idsByReasonDictionary = array();
        foreach ($listingProducts as $listingProduct) {

            if ($listingProduct['synch_status'] != Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP) {
                continue;
            }

            $listingProductSynchReasons = array_unique(array_filter(explode(',',$listingProduct['synch_reasons'])));
            foreach ($listingProductSynchReasons as $reason) {
                $idsByReasonDictionary[$reason][] = $listingProduct['id'];
            }
        }

        $idsForUpdate = array();
        foreach ($settings as $reason => $setting) {

            if (!isset($idsByReasonDictionary[$reason])) {
                continue;
            }

            $idsForUpdate = array_merge($idsForUpdate, $idsByReasonDictionary[$reason]);
        }

        Mage::getSingleton('core/resource')->getConnection('core_write')->update(
            Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
            array('synch_status' => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED),
            array('id IN (?)' => array_unique($idsForUpdate))
        );
    }

    // ########################################

    public function updateLastPrimaryCategory($path,$data)
    {
        $settings = $this->getParentObject()->getSettings('additional_data');
        $temp = &$settings;

        foreach ($path as $i => $part) {

            if (!array_key_exists($part,$temp)) {
                $temp[$part] = array();
            }

            if ($i == count($path) - 1) {
                $temp[$part] = $data;
            }

            $temp = &$temp[$part];
        }

        $this->getParentObject()->setSettings('additional_data',$settings)->save();
    }

    public function getLastPrimaryCategory($key)
    {
        return (array)$this->getSetting('additional_data',$key);
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('listing');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('listing');
        return parent::delete();
    }

    // ########################################
}