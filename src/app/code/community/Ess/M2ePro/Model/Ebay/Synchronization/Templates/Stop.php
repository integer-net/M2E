<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Templates_Stop
    extends Ess_M2ePro_Model_Ebay_Synchronization_Templates_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/stop/';
    }

    protected function getTitle()
    {
        return 'Stop';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 35;
    }

    protected function getPercentsEnd()
    {
        return 50;
    }

    //####################################

    protected function performActions()
    {
        $this->immediatelyChangedProducts();
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

            if (!$this->getInspector()->isMeetStopRequirements($listingProduct)) {
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

    private function getRunnerData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->getEbaySellingFormatTemplate()->getOutOfStockControl()) {
            return array(
                'action' => Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                'params' => array()
            );
        }

        return array(
            'action' => Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
            'params' => array(
                'replaced_action' => Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                'only_data'       => array('qty'=>true,'variations'=>true)
            )
        );
    }

    //####################################
}