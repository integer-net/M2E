<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Description_Definition_Source
{
    const GALLERY_IMAGES_COUNT_MAX = 8;

    const WEIGHT_TYPE_ITEM_DIMENSIONS    = 'item_dimensions';
    const WEIGHT_TYPE_PACKAGE_DIMENSIONS = 'package_dimensions';
    const WEIGHT_TYPE_SHIPPING           = 'shipping';
    const WEIGHT_TYPE_PACKAGE            = 'package';

    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $descriptionDefinitionTemplateModel Ess_M2ePro_Model_Amazon_Template_Description_Definition
     */
    private $descriptionDefinitionTemplateModel = null;

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setDescriptionDefinitionTemplate(Ess_M2ePro_Model_Amazon_Template_Description_Definition $instance)
    {
        $this->descriptionDefinitionTemplateModel = $instance;
        return $this;
    }

    public function getDescriptionDefinitionTemplate()
    {
        return $this->descriptionDefinitionTemplateModel;
    }

    // ########################################

    public function getTitle()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_Description_Definition::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_Amazon_Template_Description_Definition::TITLE_MODE_CUSTOM:
                $title = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'], $this->getMagentoProduct()
                );
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        return $title;
    }

    public function getBrand()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getBrandSource();

        if ($this->getDescriptionDefinitionTemplate()->isBrandModeNone()) {
            return NULL;
        }

        if ($this->getDescriptionDefinitionTemplate()->isBrandModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    public function getDescription()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getDescriptionSource();

        /* @var $templateProcessor Mage_Core_Model_Email_Template_Filter */
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_Description_Definition::DESCRIPTION_MODE_PRODUCT:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Amazon_Template_Description_Definition::DESCRIPTION_MODE_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Amazon_Template_Description_Definition::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'], $this->getMagentoProduct()
                );
                break;

            default:
                $description = '';
                break;
        }

        $allowedTags = array('<p>', '<br>', '<ul>', '<li>', '<b>');

        $description = str_replace(array('<![CDATA[', ']]>'), '', $description);
        $description = strip_tags($description,implode($allowedTags));

        return $description;
    }

    // ---------------------------------------

    public function getTargetAudience()
    {
        if ($this->getDescriptionDefinitionTemplate()->isTargetAudienceModeNone()) {
            return array();
        }

        $audience = array();
        $src = $this->getDescriptionDefinitionTemplate()->getTargetAudienceSource();

        foreach ($src['template'] as $bullet) {
            $audience[] = strip_tags(
                Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $bullet, $this->getMagentoProduct()
                )
            );
        }

        return $audience;
    }

    public function getBulletPoints()
    {
        if ($this->getDescriptionDefinitionTemplate()->isBulletPointsModeNone()) {
            return array();
        }

        $bullets = array();
        $src = $this->getDescriptionDefinitionTemplate()->getBulletPointsSource();

        foreach ($src['template'] as $bullet) {
            $bullets[] = strip_tags(
                Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $bullet, $this->getMagentoProduct()
                )
            );
        }

        return $bullets;
    }

    public function getSearchTerms()
    {
        if ($this->getDescriptionDefinitionTemplate()->isSearchTermsModeNone()) {
            return array();
        }

        $searchTerms = array();
        $src = $this->getDescriptionDefinitionTemplate()->getSearchTermsSource();

        foreach ($src['template'] as $searchTerm) {
            $searchTerms[] = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $searchTerm, $this->getMagentoProduct()
            );
        }

        return $searchTerms;
    }

    // ---------------------------------------

    public function getManufacturer()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getManufacturerSource();

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerModeNone()) {
            return NULL;
        }

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    public function getManufacturerPartNumber()
    {
        $src = $this->getDescriptionDefinitionTemplate()->getManufacturerPartNumberSource();

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerPartNumberModeNone()) {
            return NULL;
        }

        if ($this->getDescriptionDefinitionTemplate()->isManufacturerPartNumberModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    // ---------------------------------------

    public function getItemDimensionsVolume()
    {
        $volume = array();

        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeModeNone()) {
            return $volume;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsVolumeSource();

        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeModeCustomValue()) {
            $volume = array(
                'length' => $src['length_custom_value'],
                'width'  => $src['width_custom_value'],
                'height' => $src['height_custom_value']
            );
        } else {
            $volume = array(
                'length' => trim($this->getMagentoProduct()->getAttributeValue($src['length_custom_attribute'])),
                'width'  => trim($this->getMagentoProduct()->getAttributeValue($src['width_custom_attribute'])),
                'height' => trim($this->getMagentoProduct()->getAttributeValue($src['height_custom_attribute']))
            );
        }

        return $volume;
    }

    public function getItemDimensionsVolumeUnitOfMeasure()
    {
        $unitOfMeasure = NULL;

        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeModeNone()) {
            return $unitOfMeasure;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsVolumeUnitOfMeasureSource();

        $unitOfMeasure = $src['custom_value'];
        if ($this->getDescriptionDefinitionTemplate()->isItemDimensionsVolumeUnitOfMeasureModeCustomAttribute()) {
            $unitOfMeasure = trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
        }

        return $unitOfMeasure;
    }

    public function getItemDimensionsWeight()
    {
        return $this->getWeight(self::WEIGHT_TYPE_ITEM_DIMENSIONS);
    }

    public function getItemDimensionsWeightUnitOfMeasure()
    {
        return $this->getWeightUnitOfMeasure(self::WEIGHT_TYPE_ITEM_DIMENSIONS);
    }

    // ---------------------------------------

    public function getPackageDimensionsVolume()
    {
        $volume = array();

        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeModeNone()) {
            return $volume;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getPackageDimensionsVolumeSource();

        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeModeCustomValue()) {
            $volume = array(
                'length' => $src['length_custom_value'],
                'width'  => $src['width_custom_value'],
                'height' => $src['height_custom_value']
            );
        } else {
            $volume = array(
                'length' => trim($this->getMagentoProduct()->getAttributeValue($src['length_custom_attribute'])),
                'width'  => trim($this->getMagentoProduct()->getAttributeValue($src['width_custom_attribute'])),
                'height' => trim($this->getMagentoProduct()->getAttributeValue($src['height_custom_attribute']))
            );
        }

        return $volume;
    }

    public function getPackageDimensionsVolumeUnitOfMeasure()
    {
        $unitOfMeasure = NULL;

        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeModeNone()) {
            return $unitOfMeasure;
        }

        $src = $this->getDescriptionDefinitionTemplate()->getPackageDimensionsVolumeUnitOfMeasureSource();

        $unitOfMeasure = $src['custom_value'];
        if ($this->getDescriptionDefinitionTemplate()->isPackageDimensionsVolumeUnitOfMeasureModeCustomAttribute()) {
            $unitOfMeasure = trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
        }

        return $unitOfMeasure;
    }

    // ---------------------------------------

    public function getPackageWeight()
    {
        return $this->getWeight(self::WEIGHT_TYPE_PACKAGE);
    }

    public function getPackageWeightUnitOfMeasure()
    {
        return $this->getWeightUnitOfMeasure(self::WEIGHT_TYPE_PACKAGE);
    }

    // ---------------------------------------

    public function getShippingWeight()
    {
        return $this->getWeight(self::WEIGHT_TYPE_SHIPPING);
    }

    public function getShippingWeightUnitOfMeasure()
    {
        return $this->getWeightUnitOfMeasure(self::WEIGHT_TYPE_SHIPPING);
    }

    // ---------------------------------------

    private function getWeight($weightType)
    {
        $src = NULL;

        switch ($weightType) {

            case self::WEIGHT_TYPE_ITEM_DIMENSIONS:
                $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsWeightSource();
                break;

            case self::WEIGHT_TYPE_PACKAGE_DIMENSIONS:
                $src = $this->getDescriptionDefinitionTemplate()->getPackageDimensionsWeightSource();
                break;

            case self::WEIGHT_TYPE_PACKAGE:
                $src = $this->getDescriptionDefinitionTemplate()->getPackageWeightSource();
                break;

            case self::WEIGHT_TYPE_SHIPPING:
                $src = $this->getDescriptionDefinitionTemplate()->getShippingWeightSource();
                break;
        }

        if (!$src || $src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_MODE_NONE) {
            return NULL;
        }

        $weight = $src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_MODE_CUSTOM_VALUE
            ? $src['custom_value']
            : $this->getMagentoProduct()->getAttributeValue($src['custom_attribute']);

        $weight = str_replace(',', '.', $weight);
        $weight = round((float)$weight, 2);

        return $weight;
    }

    private function getWeightUnitOfMeasure($weightType)
    {
        $src = NULL;

        switch ($weightType) {

            case self::WEIGHT_TYPE_ITEM_DIMENSIONS:
                $src = $this->getDescriptionDefinitionTemplate()->getItemDimensionsWeightUnitOfMeasureSource();
                break;

            case self::WEIGHT_TYPE_PACKAGE_DIMENSIONS:
                $src = $this->getDescriptionDefinitionTemplate()->getPackageDimensionsWeightUnitOfMeasureSource();
                break;

            case self::WEIGHT_TYPE_PACKAGE:
                $src = $this->getDescriptionDefinitionTemplate()->getPackageWeightUnitOfMeasureSource();
                break;

            case self::WEIGHT_TYPE_SHIPPING:
                $src = $this->getDescriptionDefinitionTemplate()->getShippingWeightUnitOfMeasureSource();
                break;
        }

        if (!$src) {
            return NULL;
        }

        $tValue = Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
        if ($src['mode'] == $tValue) {
            return $src['custom_value'];
        }

        $tValue = Ess_M2ePro_Model_Amazon_Template_Description_Definition::WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
        if ($src['mode'] == $tValue) {
            return trim($this->getMagentoProduct()->getAttributeValue($src['custom_attribute']));
        }

        return NULL;
    }

    // ########################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->getDescriptionDefinitionTemplate()->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }

        if ($this->getDescriptionDefinitionTemplate()->isImageMainModeAttribute()) {
            $src = $this->getDescriptionDefinitionTemplate()->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        return $imageLink;
    }

    public function getImages()
    {
        if ($this->getDescriptionDefinitionTemplate()->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        $mainImage = array($mainImage);

        if ($this->getDescriptionDefinitionTemplate()->isGalleryImagesModeNone()) {
            return $mainImage;
        }

        $galleryImages = array();
        $gallerySource = $this->getDescriptionDefinitionTemplate()->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->getDescriptionDefinitionTemplate()->isGalleryImagesModeProduct()) {

            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks($limitGalleryImages + 1);
        }

        if ($this->getDescriptionDefinitionTemplate()->isGalleryImagesModeAttribute()) {

            $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;
            $galleryImagesTemp = $this->getMagentoProduct()->getAttributeValue($gallerySource['attribute']);

            $galleryImagesTemp = (array)explode(',', $galleryImagesTemp);
            foreach ($galleryImagesTemp as $tempImageLink) {

                $tempImageLink = trim($tempImageLink);
                if (!empty($tempImageLink)) {
                    $galleryImages[] = $tempImageLink;
                }
            }
        }

        $galleryImages = array_unique($galleryImages);

        if (count($galleryImages) <= 0) {
            return $mainImage;
        }

        $mainImagePosition = array_search($mainImage[0], $galleryImages);
        if ($mainImagePosition !== false) {
            unset($galleryImages[$mainImagePosition]);
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);
        return array_merge($mainImage, $galleryImages);
    }

    // ########################################
}