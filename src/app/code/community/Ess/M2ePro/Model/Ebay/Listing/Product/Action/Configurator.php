<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
{
    const DATA_TYPE_IMAGES      = 'images';
    const DATA_TYPE_VARIATIONS  = 'variations';

    // ########################################

    public function getAllDataTypes()
    {
        return array_merge(
            parent::getAllDataTypes(),
            array(
                self::DATA_TYPE_IMAGES,
                self::DATA_TYPE_VARIATIONS,
            )
        );
    }

    // ########################################

    public function isImagesAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    public function allowImages()
    {
        if ($this->isImagesAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_IMAGES;
        return $this;
    }

    // ----------------------------------------

    public function isVariationsAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_VARIATIONS);
    }

    public function allowVariations()
    {
        if ($this->isVariationsAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_VARIATIONS;
        return $this;
    }

    // ########################################
}