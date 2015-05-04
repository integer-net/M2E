<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit
{
    // ########################################

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $parentListingProduct = NULL;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getParentListingProduct()
    {
        if (is_null($this->parentListingProduct)) {
            $parentListingProductId = $this->getVariationManager()->getVariationParentId();
            $this->parentListingProduct = Mage::helper('M2ePro/Component_Amazon')
                                                    ->getObject('Listing_Product',$parentListingProductId);
        }

        return $this->parentListingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonParentListingProduct()
    {
        return $this->getParentListingProduct()->getChildObject();
    }

    // ########################################

    public function isVariationChannelMatched()
    {
        return (bool)$this->getListingProduct()->getData('is_variation_channel_matched');
    }

    // ########################################

    public function setChannelVariation(array $options)
    {
        $this->unsetChannelVariation();

        $this->setChannelOptions($options, false);
        $this->getListingProduct()->setData('is_variation_channel_matched', 1);

        $this->getListingProduct()->save();
    }

    public function unsetChannelVariation()
    {
        if (!$this->isVariationChannelMatched()) {
            return;
        }

        $this->setChannelOptions(array(), false);
        $this->getListingProduct()->setData('is_variation_channel_matched', 0);

        $this->getListingProduct()->save();
    }

    // ########################################

    public function getChannelOptions()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (empty($additionalData['variation_channel_options'])) {
            return NULL;
        }

        ksort($additionalData['variation_channel_options']);

        return $additionalData['variation_channel_options'];
    }

    // -----------------------------------------

    private function setChannelOptions(array $options, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_channel_options'] = $options;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ########################################

    public function clearTypeData()
    {
        parent::clearTypeData();

        $this->unsetChannelVariation();

        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_channel_options']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    // ########################################
}