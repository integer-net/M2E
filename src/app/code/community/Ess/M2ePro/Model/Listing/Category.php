<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Category extends Ess_M2ePro_Model_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing
     */
    private $listingModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Category');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->listingModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        if (is_null($this->listingModel)) {
            $this->listingModel = Mage::helper('M2ePro/Component')->getUnknownObject(
                'Listing',$this->getData('listing_id')
            );
        }

        return $this->listingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing $instance
     */
    public function setListing(Ess_M2ePro_Model_Listing $instance)
    {
         $this->listingModel = $instance;
    }

    // ########################################

    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    // ########################################
}