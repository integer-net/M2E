<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Defaults_RemoveUnusedTemplates
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const SAFE_CREATE_DATE_INTERVAL = 86400;

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Remove Unused Templates');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Remove Unused Templates" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Remove Unused Templates" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Prepare last time
        $this->prepareLastTime();

        // Check locked last time
        if ($this->isLockedLastTime()) {
            return;
        }

        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN);

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/4);
        $this->_lockItem->activate();

        $this->removeCategoriesTemplates();

        $this->_lockItem->setPercents(self::PERCENTS_START + 2*self::PERCENTS_INTERVAL/4);
        $this->_lockItem->activate();

        $this->removeOtherCategoriesTemplates();

        $this->_lockItem->setPercents(self::PERCENTS_START + 3*self::PERCENTS_INTERVAL/4);
        $this->_lockItem->activate();

        $this->setCheckLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
    }

    //####################################

    private function removeUnusedTemplates($templateNick)
    {
        $this->_profiler->addTimePoint(__METHOD__.$templateNick,'Remove Unused "'.$templateNick.'" Templates');

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $templateManager */
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setTemplate($templateNick);

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();

        $unionSelectListingTemplate = $connWrite->select()
                    ->from($listingTable,array('result_field'=>$templateManager->getTemplateIdColumnName()))
                    ->where($templateManager->getTemplateIdColumnName().' IS NOT NULL');
        $unionSelectListingCustom = $connWrite->select()
                     ->from($listingTable,array('result_field'=>$templateManager->getCustomIdColumnName()))
                     ->where($templateManager->getCustomIdColumnName().' IS NOT NULL');
        $unionSelectListingProductTemplate = $connWrite->select()
                     ->from($listingProductTable,array('result_field'=>$templateManager->getTemplateIdColumnName()))
                     ->where($templateManager->getTemplateIdColumnName().' IS NOT NULL');
        $unionSelectListingProductCustom = $connWrite->select()
                     ->from($listingProductTable,array('result_field'=>$templateManager->getCustomIdColumnName()))
                     ->where($templateManager->getCustomIdColumnName().' IS NOT NULL');

        $unionSelect = $connWrite->select()->union(array(
            $unionSelectListingTemplate,
            $unionSelectListingCustom,
            $unionSelectListingProductTemplate,
            $unionSelectListingProductCustom
        ));

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $collection = $templateManager->getTemplateCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`is_custom_template` = 1');
        $collection->getSelect()->where('`create_date` < ?',$minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->_profiler->saveTimePoint(__METHOD__.$templateNick);
    }

    // -----------------------------------

    private function removeCategoriesTemplates()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Remove Unused "Category" Templates');

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();
        $listingAutoCategoryTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category')->getMainTable();

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $unionListingAutoGlobalSelect = $connWrite->select()
                    ->from($listingTable,array('result_field'=>'auto_global_adding_template_category_id'))
                    ->where('auto_global_adding_template_category_id IS NOT NULL');
        $unionListingAutoWebsiteSelect = $connWrite->select()
                    ->from($listingTable,array('result_field'=>'auto_website_adding_template_category_id'))
                    ->where('auto_website_adding_template_category_id IS NOT NULL');
        $unionListingAutoCategorySelect = $connWrite->select()
                    ->from($listingAutoCategoryTable,array('result_field'=>'adding_template_category_id'))
                    ->where('adding_template_category_id IS NOT NULL');
        $unionSelectListingProductTemplate = $connWrite->select()
                    ->from($listingProductTable,array('result_field'=>'template_category_id'))
                    ->where('template_category_id IS NOT NULL');

        $unionSelect = $connWrite->select()->union(array(
            $unionListingAutoGlobalSelect,
            $unionListingAutoWebsiteSelect,
            $unionListingAutoCategorySelect,
            $unionSelectListingProductTemplate
        ));

        $collection = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`create_date` < ?',$minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function removeOtherCategoriesTemplates()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Remove Unused "Other Category" Templates');

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingTable = Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();
        $listingAutoCategoryTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category')->getMainTable();

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $unionListingAutoGlobalSelect = $connWrite->select()
                    ->from($listingTable,array('result_field'=>'auto_global_adding_template_other_category_id'))
                    ->where('auto_global_adding_template_other_category_id IS NOT NULL');
        $unionListingAutoWebsiteSelect = $connWrite->select()
                    ->from($listingTable,array('result_field'=>'auto_website_adding_template_other_category_id'))
                    ->where('auto_website_adding_template_other_category_id IS NOT NULL');
        $unionListingAutoCategorySelect = $connWrite->select()
                    ->from($listingAutoCategoryTable,array('result_field'=>'adding_template_other_category_id'))
                    ->where('adding_template_other_category_id IS NOT NULL');
        $unionSelectListingProductTemplate = $connWrite->select()
                    ->from($listingProductTable,array('result_field'=>'template_other_category_id'))
                    ->where('template_other_category_id IS NOT NULL');

        $unionSelect = $connWrite->select()->union(array(
            $unionListingAutoGlobalSelect,
            $unionListingAutoWebsiteSelect,
            $unionListingAutoCategorySelect,
            $unionSelectListingProductTemplate
        ));

        $collection = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`create_date` < ?',$minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function prepareLastTime()
    {
        $lastTime = $this->getCheckLastTime();
        if (empty($lastTime)) {
            $lastTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastTime->modify("-1 year");
            $this->setCheckLastTime($lastTime);
        }
    }

    private function isLockedLastTime()
    {
        $lastTime = strtotime($this->getCheckLastTime());

        $tempGroup = '/ebay/defaults/remove_unused_templates/';
        $interval = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue($tempGroup,'interval');

        if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return true;
        }

        return false;
    }

    private function getCheckLastTime()
    {
        $tempGroup = '/ebay/defaults/remove_unused_templates/';
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue($tempGroup,'last_time');
    }

    private function setCheckLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }
        $tempGroup = '/ebay/defaults/remove_unused_templates/';
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue($tempGroup,'last_time',$time);
    }

    //####################################
}