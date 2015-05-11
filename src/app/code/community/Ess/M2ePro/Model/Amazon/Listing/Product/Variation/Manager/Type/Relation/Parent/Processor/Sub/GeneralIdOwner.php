<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_GeneralIdOwner
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    // ##########################################################

    protected function check()
    {
        if (!$this->getProcessor()->isGeneralIdSet() || !$this->getProcessor()->isGeneralIdOwner()) {
            $this->getProcessor()->getListingProduct()->setData('sku', null);
        }
    }

    protected function execute()
    {
        $isGeneralIdOwner = $this->getProcessor()->getAmazonListingProduct()->isGeneralIdOwner();
        $descriptionTemplateId = $this->getProcessor()->getAmazonListingProduct()->getTemplateDescriptionId();

        foreach ($this->getProcessor()->getChildListingProducts() as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $needSave = false;

            if ($amazonListingProduct->isGeneralIdOwner() != $isGeneralIdOwner) {
                $listingProduct->setData('is_general_id_owner', $isGeneralIdOwner);
                $needSave = true;
            }

            if ($amazonListingProduct->getTemplateDescriptionId() != $descriptionTemplateId) {
                $listingProduct->setData('template_description_id', $descriptionTemplateId);
                $needSave = true;
            }

            $needSave && $listingProduct->save();
        }
    }

    // ##########################################################
}