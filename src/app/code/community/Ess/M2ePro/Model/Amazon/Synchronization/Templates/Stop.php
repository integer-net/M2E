<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Stop
    extends Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
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

            $actionParams = array('only_data'=>array('qty'=>true));

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetStopRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}