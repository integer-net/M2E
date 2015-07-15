<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Relist
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/relist/';
    }

    protected function getTitle()
    {
        return 'Relist';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 20;
    }

    protected function getPercentsEnd()
    {
        return 35;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
        $this->executeScheduled();
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when Product was changed');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            $runnerData = $this->getRunnerData($listingProduct);

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                $runnerData['action'],
                $runnerData['params']
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                $runnerData['action'],
                $runnerData['params']
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeScheduled()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Execute scheduled');

        /** @var Ess_M2ePro_Model_Template_Synchronization $synchTemplateCollection */
        $synchTemplateCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_Synchronization');

        foreach ($synchTemplateCollection as $synchTemplate) {

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
                    $affectedListingsProducts = $ebaySynchTemplate->getAffectedListingsProducts(true);
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
                $listingProduct->getMagentoProduct()->enableCache();

                $runnerData = $this->getRunnerData($listingProduct);

                $isExistInRunner = $this->getRunner()->isExistProduct(
                    $listingProduct,
                    $runnerData['action'],
                    $runnerData['params']
                );

                if ($isExistInRunner) {
                    continue;
                }

                if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                    continue;
                }

                $this->getRunner()->addProduct(
                    $listingProduct,
                    $runnerData['action'],
                    $runnerData['params']
                );
            }
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function getNextScheduledListingsProducts($synchTemplateId)
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/template/synchronization/'.$synchTemplateId.'/schedule/relist/';

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
        $collection->addFieldToFilter('main_table.status',
                    array('neq'=>Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED));
        $collection->addFieldToFilter('main_table.status',
                    array('neq'=>Ess_M2ePro_Model_Listing_Product::STATUS_LISTED));
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

    public function getRunnerData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $data = array();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if ($listingProduct->isHidden()) {

            $data['action'] = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;

            if ($ebayListingProduct->getEbaySynchronizationTemplate()->isRelistSendData()) {
                $data['params'] = array('all_data'=>true);
            } else {
                $data['params'] = array('only_data'=>array('qty'=>true,'variations'=>true));
            }

            $data['params']['replaced_action'] = Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;

        } else {

            $data['action'] = Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;

            if ($ebayListingProduct->getEbaySynchronizationTemplate()->isRelistSendData()) {
                $data['params'] = array('all_data'=>true);
            } else {
                $data['params'] = array('only_data'=>array('base'=>true));
            }
        }

        return $data;
    }

    //####################################
}