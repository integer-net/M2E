<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Details
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description
     */
    private $descriptionTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    private $definitionTemplate = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    private $definitionSource = NULL;

    // ########################################

    public function getData()
    {
        $data = array();

        if (!$this->getConfigurator()->isDetails()) {
            return $data;
        }

        if (!$this->getVariationManager()->isRelationParentType()) {
            $data = array_merge(
                $data,
                $this->getConditionData(),
                $this->getGiftData()
            );
        }

        $isUseDescriptionTemplate = false;

        do {

            if (!$this->getAmazonListingProduct()->isExistDescriptionTemplate()) {
                break;
            }

            $variationManager = $this->getAmazonListingProduct()->getVariationManager();

            if (($variationManager->isRelationChildType() || $variationManager->isIndividualType()) &&
                ($this->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
                 $this->getMagentoProduct()->isBundleType())) {
                break;
            }

            $isUseDescriptionTemplate = true;

        } while (false);

        if (!$isUseDescriptionTemplate) {

            if (isset($data['gift_wrap']) || isset($data['gift_message'])) {

                $data['description_data']['title'] = $this->getAmazonListingProduct()
                                                          ->getMagentoProduct()
                                                          ->getName();
            }

            return $data;
        }

        $data = array_merge($data, $this->getDescriptionData());

        $browsenodeId = $this->getDescriptionTemplate()->getBrowsenodeId();
        if (empty($browsenodeId)) {
            return $data;
        }

        // browsenode_id requires description_data
        $data['browsenode_id'] = $browsenodeId;

        return array_merge(
            $data,
            $this->getProductData()
        );
    }

    // ########################################

    private function getConditionData()
    {
        $this->searchNotFoundAttributes();
        $conditionNote = $this->getAmazonListingProduct()->getListingSource()->getConditionNote();
        $this->processNotFoundAttributes('Condition Note');

        return array(
            'condition'      => $this->getAmazonListingProduct()->getListingSource()->getCondition(),
            'condition_note' => $conditionNote,
        );
    }

    private function getGiftData()
    {
        $data = array();
        $giftWrap = $this->getAmazonListingProduct()->getListingSource()->getGiftWrap();

        if (!is_null($giftWrap)) {
            $data['gift_wrap'] = $giftWrap;
        }

        $giftMessage = $this->getAmazonListingProduct()->getListingSource()->getGiftMessage();

        if (!is_null($giftMessage)) {
            $data['gift_message'] = $giftMessage;
        }

        return $data;
    }

    // ---------------------------------------

    private function getDescriptionData()
    {
        $source = $this->getDefinitionSource();

        $data = array(
            'brand'                    => $source->getBrand(),
            'manufacturer'             => $source->getManufacturer(),
            'manufacturer_part_number' => $source->getManufacturerPartNumber(),

            'item_dimensions_volume'                 => $source->getItemDimensionsVolume(),
            'item_dimensions_volume_unit_of_measure' => $source->getItemDimensionsVolumeUnitOfMeasure(),
            'item_dimensions_weight'                 => $source->getItemDimensionsWeight(),
            'item_dimensions_weight_unit_of_measure' => $source->getItemDimensionsWeightUnitOfMeasure(),

            'package_dimensions_volume'                 => $source->getPackageDimensionsVolume(),
            'package_dimensions_volume_unit_of_measure' => $source->getPackageDimensionsVolumeUnitOfMeasure(),

            'package_weight'                  => $source->getPackageWeight(),
            'package_weight_unit_of_measure'  => $source->getPackageWeightUnitOfMeasure(),

            'shipping_weight'                 => $source->getShippingWeight(),
            'shipping_weight_unit_of_measure' => $source->getShippingWeightUnitOfMeasure(),
        );

        $this->searchNotFoundAttributes();
        $data['title'] = $this->getDefinitionSource()->getTitle();
        $this->processNotFoundAttributes('Title');

        $this->searchNotFoundAttributes();
        $data['description'] = $this->getDefinitionSource()->getDescription();
        $this->processNotFoundAttributes('Description');

        $this->searchNotFoundAttributes();
        $data['bullet_points'] = $this->getDefinitionSource()->getBulletPoints();
        $this->processNotFoundAttributes('Bullet Points');

        $this->searchNotFoundAttributes();
        $data['search_terms'] = $this->getDefinitionSource()->getSearchTerms();
        $this->processNotFoundAttributes('Search Terms');

        $this->searchNotFoundAttributes();
        $data['target_audience'] = $this->getDefinitionSource()->getTargetAudience();
        $this->processNotFoundAttributes('Target Audience');

        if (is_null($data['package_weight'])) {
            unset(
                $data['package_weight'],
                $data['package_weight_unit_of_measure']
            );
        }

        if (is_null($data['shipping_weight'])) {
            unset(
                $data['shipping_weight'],
                $data['shipping_weight_unit_of_measure']
            );
        }

        return array(
            'description_data' => $data
        );
    }

    // ---------------------------------------

    private function getProductData()
    {
        $data = array();

        $this->searchNotFoundAttributes();

        foreach ($this->getDescriptionTemplate()->getSpecifics(true) as $specific) {

            $path = $specific->getSource(
                $this->getAmazonListingProduct()->getActualMagentoProduct()
            )->getPath();

            $data = Mage::helper('M2ePro')->arrayReplaceRecursive(
                $data, json_decode($path, true)
            );
        }

        $this->processNotFoundAttributes('Product Specifics');

        return array(
            'product_data'      => $data,
            'product_data_nick' => $this->getDescriptionTemplate()->getProductDataNick(),
        );
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    private function getDescriptionTemplate()
    {
        if (is_null($this->descriptionTemplate)) {
            $this->descriptionTemplate = $this->getAmazonListingProduct()->getAmazonDescriptionTemplate();
        }
        return $this->descriptionTemplate;
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    private function getDefinitionTemplate()
    {
        if (is_null($this->definitionTemplate)) {
            $this->definitionTemplate = $this->getDescriptionTemplate()->getDefinitionTemplate();
        }
        return $this->definitionTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
     */
    private function getDefinitionSource()
    {
        if (is_null($this->definitionSource)) {
            $this->definitionSource = $this->getDefinitionTemplate()
                ->getSource($this->getAmazonListingProduct()->getActualMagentoProduct());
        }
        return $this->definitionSource;
    }

    // ########################################
}