<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_List
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/list/';
    }

    protected function getTitle()
    {
        return 'List';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 5;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
        $this->immediatelyNotCheckedProducts();
        $this->executeScheduled();
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was changed');

        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                array()
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function immediatelyNotCheckedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was not checked');

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('tried_to_list',0);
        $collection->getSelect()->limit(100);

        $listingsProducts = $collection->getItems();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->enableCache();
            $listingProduct->setData('tried_to_list',1)->save();

            if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                array()
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeScheduled()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute scheduled');

        $synchTemplates = Mage::helper('M2ePro/Component_Ebay')
                                        ->getCollection('Template_Synchronization')
                                        ->getItems();

        foreach ($synchTemplates as $synchTemplate) {

            /* @var $ebaySynchTemplate Ess_M2ePro_Model_Ebay_Template_Synchronization */
            $ebaySynchTemplate = $synchTemplate->getChildObject();

            if (!$ebaySynchTemplate->isScheduleEnabled()) {
                continue;
            }

            if (!$ebaySynchTemplate->isScheduleIntervalNow() ||
                !$ebaySynchTemplate->isScheduleWeekNow()) {
                continue;
            }

            $listingsProducts = array();
            $affectedListingsProducts = NULL;

            do {

                $tempListingsProducts = $this->getNextScheduledListingsProducts($synchTemplate->getId());

                if (count($tempListingsProducts) <= 0) {
                    break;
                }

                if (is_null($affectedListingsProducts)) {
                    $affectedListingsProducts = $ebaySynchTemplate->getAffectedListingsProducts(false);
                }

                if (count($affectedListingsProducts) <= 0) {
                    break;
                }

                foreach ($tempListingsProducts as $tempListingProduct) {

                    $found = false;
                    foreach ($affectedListingsProducts as $affectedListingProduct) {
                        if ((int)$tempListingProduct->getId() == $affectedListingProduct['id']) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        continue;
                    }

                    $listingsProducts[] = $tempListingProduct;
                }

            } while (count($listingsProducts) < 100);

            foreach ($listingsProducts as $listingProduct) {

                /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                $listingProduct->enableCache();

                if (!$this->getInspector()->isMeetListRequirements($listingProduct)) {
                    continue;
                }

                $this->getRunner()->addProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                    array()
                );
            }
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function getNextScheduledListingsProducts($synchTemplateId)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/template/synchronization/'.$synchTemplateId.'/schedule/list/';

        $yearMonthDay = Mage::helper('M2ePro')->getCurrentGmtDate(false,'Y-m-d');
        $configData = $cacheConfig->getGroupValue($cacheConfigGroup,'last_listing_product_id');

        if (is_null($configData)) {
            $configData = array();
        } else {
            $configData = json_decode($configData,true);
        }

        $lastListingProductId = 0;
        if (isset($configData[$yearMonthDay])) {
            $lastListingProductId = (int)$configData[$yearMonthDay];
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('main_table.id',array('gt'=>$lastListingProductId));
        $collection->addFieldToFilter('main_table.status',Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
        $collection->getSelect()->order('main_table.id', Zend_Db_Select::SQL_ASC);
        $collection->getSelect()->limit(100);

        $lastItem = $collection->getLastItem();
        if (!$lastItem->getId()) {
            return array();
        }

        $configData = array($yearMonthDay=>$lastItem->getId());
        $cacheConfig->setGroupValue($cacheConfigGroup,'last_listing_product_id',json_encode($configData));

        return $collection->getItems();
    }

    //####################################
}