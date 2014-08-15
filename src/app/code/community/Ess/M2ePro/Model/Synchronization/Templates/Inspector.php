<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Synchronization_Templates_Inspector
{
    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    private $runner = NULL;

    //####################################

    public function setRunner(Ess_M2ePro_Model_Synchronization_Templates_Runner $object)
    {
        $this->runner = $object;
    }

    public function getRunner()
    {
        if (is_null($this->runner)) {
            $this->runner = $this->makeRunner();
        }
        return $this->runner;
    }

    // -----------------------------------

    abstract protected function makeRunner();

    //####################################

    public function processProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->processProducts(array($listingProduct));
    }

    public function processProducts(array $listingsProducts = array())
    {
        $this->getRunner()->resetProducts();

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $this->processItem($listingProduct);
        }

        $this->getRunner()->execute();
    }

    // -----------------------------------

    private function processItem(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->isNotListed()) {

            $this->isMeetListRequirements($listingProduct) &&
            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                                           array());

        } else if ($listingProduct->isListed()) {

            if ($this->isMeetStopRequirements($listingProduct)) {

                $this->getRunner()->addProduct($listingProduct,
                                               Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
                                               array());
                return;
            }

            $this->inspectReviseQtyRequirements($listingProduct);
            $this->inspectRevisePriceRequirements($listingProduct);

        } else {
            $this->isMeetRelistRequirements($listingProduct) &&
            $this->getRunner()->addProduct($listingProduct,
                                           Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
                                           array());
        }
    }

    //####################################

    abstract public function isMeetListRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct);
    abstract public function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct);
    abstract public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct);

    abstract public function inspectReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct);
    abstract public function inspectRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct);

    //####################################
}