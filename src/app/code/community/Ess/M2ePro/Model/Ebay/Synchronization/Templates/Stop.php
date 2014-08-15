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
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was changed');

        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$this->getInspector()->isMeetStopRequirements($listingProduct)) {
                continue;
            }

            $runnerData = $this->getInspector()->getRunnerStopDataByListingProduct($listingProduct);

            $this->getRunner()->addProduct(
                $listingProduct,
                $runnerData['action'],
                $runnerData['params']
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}