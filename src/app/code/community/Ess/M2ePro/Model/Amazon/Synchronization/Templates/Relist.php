<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Relist
    extends Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
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
    }

    //####################################

    private function immediatelyChangedProducts()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when product was changed');

        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($changedListingsProducts as $listingProduct) {

            if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                array()
            );
        }
        //------------------------------------

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}