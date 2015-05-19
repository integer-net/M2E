<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Images
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        $data = array();

        if (!$this->getConfigurator()->isImages()) {
            return $data;
        }

        $this->searchNotFoundAttributes();

        $images = array(
            'offer' => $this->getAmazonListingProduct()->getListingSource()->getImages(),
        );

        if ($this->getAmazonListingProduct()->isExistDescriptionTemplate()) {
            $amazonDescriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
            $definitionSource = $amazonDescriptionTemplate->getDefinitionTemplate()->getSource(
                $this->getAmazonListingProduct()->getActualMagentoProduct()
            );

            $images['product'] = $definitionSource->getImages();
        }

        $this->processNotFoundAttributes('Images');

        if (!empty($images['offer'])) {
            $data['images_data']['offer'] = $images['offer'];
        }

        if (!empty($images['product'])) {
            $data['images_data']['product'] = $images['product'];
        }

        return $data;
    }

    // ########################################
}