<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Templates_List
    extends Ess_M2ePro_Model_Play_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 10;
    const PERCENTS_END = 20;
    const PERCENTS_INTERVAL = 10;

    /**
     * @var Ess_M2ePro_Model_Play_Template_Synchronization_ProductInspector
     */
    private $_productInspector = NULL;

    //####################################

    public function __construct()
    {
        parent::__construct();

        $tempParams = array('runner_actions'=>$this->_runnerActions);
        $this->_productInspector = Mage::getModel('M2ePro/Play_Template_Synchronization_ProductInspector',
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
            $componentName = Ess_M2ePro_Helper_Component_Play::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'List Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "List" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "List" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $tasks = array(
            'immediatelyChangedProducts',
            'immediatelyNotCheckedProducts',
        );

        foreach ($tasks as $i => $task) {
            $this->$task();

            $this->_lockItem->setPercents(self::PERCENTS_START + ($i+1)*self::PERCENTS_INTERVAL/count($tasks));
            $this->_lockItem->activate();
        }
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was changed');

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

            if (!$this->_productInspector->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Play_Product_Dispatcher::ACTION_LIST,
                array()
            );
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function immediatelyNotCheckedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was not checked');

        /** @var $collection Varien_Data_Collection_Db */
        $collection = Mage::helper('M2ePro/Component_Play')->getCollection('Listing_Product');
        $collection->addFieldToFilter('tried_to_list',0);

        $collection->getSelect()->where(
            '`is_variation_product` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_PRODUCT_NO.
            ' OR ('.
                '`is_variation_product` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_PRODUCT_YES.
                ' AND `is_variation_matched` = '.Ess_M2ePro_Model_Play_Listing_Product::IS_VARIATION_MATCHED_YES.
            ')'
        );

        $collection->getSelect()->limit(100);

        $listingsProducts = $collection->getItems();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->setData('tried_to_list',1)->save();

            if (!$this->_productInspector->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $this->_runnerActions->setProduct(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Play_Product_Dispatcher::ACTION_LIST,
                array()
            );
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}