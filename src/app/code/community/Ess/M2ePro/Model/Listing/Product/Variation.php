<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Variation');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $options = $this->getOptions(true);
        foreach ($options as $option) {
            $option->deleteInstance();
        }

        $this->listingProductModel = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if (is_null($this->listingProductModel)) {
            $this->listingProductModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Listing_Product',$this->getData('listing_product_id')
            );
        }

        return $this->listingProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $instance
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $instance)
    {
         $this->listingProductModel = $instance;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getListingProduct()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getListingProduct()->getMarketplace();
    }

    // ########################################

    public function getOptions($asObjects = false, array $filters = array())
    {
        $options = $this->getRelatedComponentItems(
            'Listing_Product_Variation_Option','listing_product_variation_id',$asObjects,$filters
        );

        if ($asObjects) {
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $option->setListingProductVariation($this);
            }
        }

        return $options;
    }

    // ########################################

    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

     // ########################################
}