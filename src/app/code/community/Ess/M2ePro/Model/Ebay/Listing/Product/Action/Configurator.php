<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
{
    const TYPE_IMAGES      = 'images';
    const TYPE_VARIATIONS  = 'variations';

    // ########################################

    public function isAllPermitted()
    {
        return $this->isGeneral() && $this->isVariations() &&
               $this->isQty() && $this->isPrice() &&
               $this->isTitle() && $this->isSubtitle() &&
               $this->isDescription() && $this->isImages();
    }

    // ########################################

    public function isVariations()
    {
        return $this->isAllowed(self::TYPE_VARIATIONS);
    }

    // ----------------------------------------

    public function isImages()
    {
        return $this->isAllowed(self::TYPE_IMAGES);
    }

    // ########################################
}