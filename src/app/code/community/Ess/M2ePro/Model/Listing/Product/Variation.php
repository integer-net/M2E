<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const ADD_NO   = 0;
    const ADD_YES  = 1;

    const DELETE_NO   = 0;
    const DELETE_YES  = 1;

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

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getListingProduct()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListingProduct()->getSynchronizationTemplate();
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

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    //-----------------------------------------

    public function isAdd()
    {
        return (int)$this->getData('add') == self::ADD_YES;
    }

    public function isDelete()
    {
        return (int)$this->getData('delete') == self::DELETE_YES;
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
    }

    public function isUnknown()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
    }

    public function isBlocked()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
    }

    //-----------------------------------------

    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
    }

    //-----------------------------------------

    public function isListable()
    {
        return ($this->isNotListed() || $this->isSold() ||
                $this->isStopped() || $this->isFinished() ||
                $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isRelistable()
    {
        return ($this->isSold() || $this->isStopped() ||
                $this->isFinished() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isRevisable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isStoppable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

     // ########################################
}