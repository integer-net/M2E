<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Revise
    extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 20;
    const PERCENTS_END = 35;
    const PERCENTS_INTERVAL = 15;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Synchronization_ProductInspector
     */
    private $_productInspector = NULL;

    //####################################

    public function __construct()
    {
        parent::__construct();

        $tempParams = array('runner_actions'=>$this->_runnerActions);
        $this->_productInspector = Mage::getModel('M2ePro/Amazon_Template_Synchronization_ProductInspector',
                                                  $tempParams);
    }

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
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Revise Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Revise" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Revise" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $tasks = array(
            'executeQtyChanged',
            'executePriceChanged',
            'executeNeedSynchronize',
            'executeTotal',
        );

        foreach ($tasks as $i => $task) {
            $this->$task();

            $this->_lockItem->setPercents(self::PERCENTS_START + ($i+1)*self::PERCENTS_INTERVAL/count($tasks));
            $this->_lockItem->activate();
        }
    }

    //####################################

    private function executeQtyChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update quantity');

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {
            $this->_productInspector->inspectReviseQtyRequirements($listingProduct);
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function executePriceChanged()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update price');

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = $this->getChangedInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {
            $this->_productInspector->inspectRevisePriceRequirements($listingProduct);
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeNeedSynchronize()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Execute is need synchronize');

        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $listingProductCollection->addFieldToFilter('synch_status', Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED);

        $listingProductCollection->getSelect()->where(
            '`is_variation_product` = '.Ess_M2ePro_Model_Amazon_Listing_Product::IS_VARIATION_PRODUCT_NO.
            ' OR ('.
                '`is_variation_product` = '.Ess_M2ePro_Model_Amazon_Listing_Product::IS_VARIATION_PRODUCT_YES.
                ' AND `is_variation_matched` = '.Ess_M2ePro_Model_Amazon_Listing_Product::IS_VARIATION_MATCHED_YES.
            ')'
        );

        $listingProductCollection->getSelect()->limit(100);

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingProductCollection->getItems() as $listingProduct) {

            $listingProduct->setData('synch_status',Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_SKIP)->save();

            /* @var $synchTemplate Ess_M2ePro_Model_Template_Synchronization */
            $synchTemplate = $listingProduct->getListing()->getChildObject()->getSynchronizationTemplate();

            $isRevise = false;
            foreach ($listingProduct->getSynchReasons() as $reason) {

                $method = 'isRevise' . ucfirst($reason);

                if (!method_exists($synchTemplate,$method)) {
                    continue;
                }

                if ($synchTemplate->$method()) {
                    $isRevise = true;
                    break;
                }
            }

            if (!$isRevise) {
                continue;
            }

            if ($this->_runnerActions
                     ->isExistProductAction(
                            $listingProduct,
                            Ess_M2ePro_Model_Connector_Amazon_Product_Dispatcher::ACTION_REVISE,
                            array('all_data'=>true))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action')) {
                continue;
            }

            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Amazon_Product_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                 );
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function executeTotal()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Execute revise all');

        $lastListingProductProcessed = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/amazon/templates/revise/total/','last_listing_product_id'
        );
        if (is_null($lastListingProductProcessed)) {
            return;
        }

        $itemsPerCycle = 100;

        /* @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id',array('gt' => $lastListingProductProcessed))
            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $collection->getSelect()->limit($itemsPerCycle);
        $collection->getSelect()->order('id ASC');

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($collection->getItems() as $listingProduct) {

            if ($this->_runnerActions
                     ->isExistProductAction(
                            $listingProduct,
                            Ess_M2ePro_Model_Connector_Amazon_Product_Dispatcher::ACTION_REVISE,
                            array('all_data'=>true))
            ) {
                continue;
            }

            if (!$listingProduct->isRevisable()) {
                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action')) {
                continue;
            }

            $this->_runnerActions
                 ->setProduct(
                        $listingProduct,
                        Ess_M2ePro_Model_Connector_Amazon_Product_Dispatcher::ACTION_REVISE,
                        array('all_data'=>true)
                 );
        }

        $lastListingProduct = $collection->getLastItem()->getId();
        if ($collection->count() < $itemsPerCycle) {
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                '/amazon/templates/revise/total/','end_date', Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $lastListingProduct = NULL;
        }

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            '/amazon/templates/revise/total/','last_listing_product_id', $lastListingProduct
        );

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}