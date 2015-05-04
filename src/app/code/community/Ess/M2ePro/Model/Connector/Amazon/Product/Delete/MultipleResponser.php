<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Delete_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    // ########################################

    /** @var Ess_M2ePro_Model_Listing_Product[] $parentsForProcessing */
    protected $parentsForProcessing = array();

    // ########################################

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully deleted
        return 'Item was successfully deleted';
    }

    // ########################################

    public function eventAfterExecuting()
    {
        if (!empty($this->params['params']['remove'])) {
            foreach ($this->listingsProducts as $listingProduct) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
                $amazonListingProduct = $listingProduct->getChildObject();

                $variationManager = $amazonListingProduct->getVariationManager();

                if ($variationManager->isRelationChildType()) {
                    $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
                    $this->parentsForProcessing[$parentListingProduct->getId()] = $parentListingProduct;
                }

                $listingProduct->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED);
                $listingProduct->save();
                $listingProduct->deleteInstance();
            }
        }

        parent::eventAfterExecuting();
    }

    protected function inspectProducts()
    {
        if (empty($this->params['params']['remove'])) {
            parent::inspectProducts();
        }
    }

    protected function processParentProcessors()
    {
        if (empty($this->params['params']['remove'])) {
            parent::processParentProcessors();
            return;
        }

        foreach ($this->parentsForProcessing as $listingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $amazonListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    // ########################################
}