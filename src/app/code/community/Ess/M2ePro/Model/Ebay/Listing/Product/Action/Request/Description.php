<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Description
     */
    private $descriptionTemplate = NULL;

    // ########################################

    public function getData()
    {
        $data = array();

        if ($this->getConfigurator()->isGeneral()) {

            $data = array_merge(
                array(
                    'hit_counter' => $this->getDescriptionTemplate()->getHitCounterType(),
                    'listing_enhancements' => $this->getDescriptionTemplate()->getEnhancements(),
                    'item_condition_note' => $this->getConditionNoteData(),
                    'product_details' => $this->getProductDetailsData()
                ),
                $this->getConditionData()
            );
        }

        return array_merge(
            $data,
            $this->getTitleData(),
            $this->getSubtitleData(),
            $this->getDescriptionData(),
            $this->getImagesData()
        );
    }

    // ########################################

    public function getTitleData()
    {
        if (!$this->getConfigurator()->isTitle()) {
            return array();
        }

        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionTemplate()->getTitleResultValue();
        $this->processNotFoundAttributes('Title');

        return array(
            'title' => $data
        );
    }

    public function getSubtitleData()
    {
        if (!$this->getConfigurator()->isSubtitle()) {
            return array();
        }

        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionTemplate()->getSubTitleResultValue();
        $this->processNotFoundAttributes('Subtitle');

        return array(
            'subtitle' => $data
        );
    }

    public function getDescriptionData()
    {
        if (!$this->getConfigurator()->isDescription()) {
            return array();
        }

        $this->searchNotFoundAttributes();
        $data = $this->getEbayListingProduct()->getDescription();
        $this->processNotFoundAttributes('Description');

        return array(
            'description' => $data
        );
    }

    // ----------------------------------------

    public function getImagesData()
    {
        if (!$this->getConfigurator()->isImages()) {
            return array();
        }

        $this->searchNotFoundAttributes();

        $data = array(
            'gallery_type' => $this->getDescriptionTemplate()->getGalleryType(),
            'images' => $this->getDescriptionTemplate()->getImagesForEbay(),
            'supersize' => $this->getDescriptionTemplate()->isUseSupersizeImagesEnabled()
        );

        $this->processNotFoundAttributes('Main Image / Gallery Images');

        return array(
            'images' => $data
        );
    }

    // ########################################

    public function getProductDetailsData()
    {
        $data = array();

        foreach (array('isbn','epid','upc','ean','gtin','brand','mpn') as $tempType) {

            $this->searchNotFoundAttributes();
            $tempValue = $this->getDescriptionTemplate()->getProductDetail($tempType);

            if (!$this->processNotFoundAttributes(strtoupper($tempType))) {
                continue;
            }

            if (!$tempValue) {
                continue;
            }

            $data[$tempType] = $tempValue;
        }

        if (empty($data)) {
            return $data;
        }

        $data['include_description'] = $this->getDescriptionTemplate()->isProductDetailsIncludeDescription();
        $data['include_image'] = $this->getDescriptionTemplate()->isProductDetailsIncludeImage();

        $data['list_if_no_product'] = $this->getDescriptionTemplate()->isProductDetailsListIfNoProduct();

        return $data;
    }

    // ----------------------------------------

    public function getConditionData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionTemplate()->getCondition();

        if (!$this->processNotFoundAttributes('Condition')) {
            return array();
        }

        return array(
            'item_condition' => $data
        );
    }

    public function getConditionNoteData()
    {
        $this->searchNotFoundAttributes();
        $data = $this->getDescriptionTemplate()->getConditionNote();
        $this->processNotFoundAttributes('Condition Description');

        return $data;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    private function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplate)) {
            $this->descriptionTemplate = $this->getListingProduct()
                                              ->getChildObject()
                                              ->getDescriptionTemplate();
        }
        return $this->descriptionTemplate;
    }

    // ########################################
}