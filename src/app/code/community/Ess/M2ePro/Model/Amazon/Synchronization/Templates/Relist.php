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
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Immediately when Product was changed');

        /** @var Ess_M2ePro_Model_Listing_Product[] $changedListingsProducts */
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        foreach ($changedListingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Template_Synchronization $amazonSynchronizationTemplate */
            $amazonSynchronizationTemplate = $listingProduct->getChildObject()->getAmazonSynchronizationTemplate();

            $actionParams = array('only_data' => array('qty'=>true));
            if ($amazonSynchronizationTemplate->isRelistSendData()) {
                $actionParams = array('all_data'=>true);
            }

            $isExistInRunner = $this->getRunner()->isExistProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                $actionParams
            );

            if ($isExistInRunner) {
                continue;
            }

            if (!$this->getInspector()->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            $this->getRunner()->addProduct(
                $listingProduct,
                Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                $actionParams
            );
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}