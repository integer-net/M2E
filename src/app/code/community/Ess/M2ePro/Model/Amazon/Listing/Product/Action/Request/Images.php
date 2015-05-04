<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Images
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    private $definitionSource = NULL;

    // ########################################

    public function getData()
    {
        $data = array();

        if (!$this->getConfigurator()->isImages() ||
            !$this->getAmazonListingProduct()->isExistDescriptionTemplate()) {
            return $data;
        }

        $variationManager = $this->getAmazonListingProduct()->getVariationManager();

        if (($variationManager->isRelationChildType() || $variationManager->isIndividualType()) &&
            ($this->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
             $this->getMagentoProduct()->isBundleType())) {
            return $data;
        }

        $this->searchNotFoundAttributes();
        $images = $this->getDefinitionSource()->getImages();
        $this->processNotFoundAttributes('Images');

        !empty($images) && $data['images_data'] = $images;

        return $data;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    private function getDefinitionSource()
    {
        if (is_null($this->definitionSource)) {
            $this->definitionSource = $this->getAmazonListingProduct()
                ->getAmazonDescriptionTemplate()->getDefinitionTemplate()
                ->getSource($this->getAmazonListingProduct()->getActualMagentoProduct());
        }
        return $this->definitionSource;
    }

    // ########################################
}