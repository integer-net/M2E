<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_NewProduct_Source
{
    const GALLERY_IMAGES_COUNT_MAX = 8;

    /* @var $listingProduct Ess_M2ePro_Model_Amazon_Listing_Product */
    private $listingProduct = null;

    /* @var $templateNewProduct Ess_M2ePro_Model_Amazon_Template_NewProduct */
    private $templateNewProduct = null;

    /* @var $templateNewProductDescription Ess_M2ePro_Model_Amazon_Template_NewProduct_Description */
    private $templateNewProductDescription = null;

    // ########################################

    public function __construct($args)
    {
        list($this->listingProduct,$this->templateNewProduct) = $args;

        $this->templateNewProductDescription = $this->templateNewProduct->getDescription();
    }

    // ########################################

    public function getRegisteredParameter()
    {
        return (string)$this->templateNewProduct->getRegisteredParameter();
    }

    // ########################################

    public function getWorldwideId()
    {
        $result = '';
        $src = $this->templateNewProduct->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_NewProduct::WORLDWIDE_ID_MODE_NONE) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_NewProduct::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->listingProduct->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    // ########################################

    public function getItemPackageQuantity()
    {
        $result = '';
        $src = $this->templateNewProduct->getItemPackageQuantitySource();

        if ($this->templateNewProduct->isItemPackageQuantityModeNone()) {
            $result = NULL;
        }

        if ($this->templateNewProduct->isItemPackageQuantityModeCustomValue()) {
            $result = (int)$src['value'];
        }

        if ($this->templateNewProduct->isItemPackageQuantityModeCustomAttribute()) {
            $result = (int)$this->listingProduct->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    // ########################################

    public function getNumberOfItems()
    {
        $result = '';
        $src = $this->templateNewProduct->getNumberOfItemsSource();

        if ($this->templateNewProduct->isNumberOfItemsModeNone()) {
            $result = NULL;
        }

        if ($this->templateNewProduct->isNumberOfItemsModeCustomValue()) {
            $result = (int)$src['value'];
        }

        if ($this->templateNewProduct->isNumberOfItemsModeCustomAttribute()) {
            $result = (int)$this->listingProduct->getActualMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $result;
    }

    // ########################################

    public function getProductData()
    {
        $arrayXml = array();
        foreach ($this->templateNewProduct->getSpecifics() as $specific) {

            $xpath = trim($specific['xpath'],'/');
            $xpathParts = explode('/',$xpath);

            $path = '';
            $isFirst = true;

            foreach ($xpathParts as $part) {
                list($tag,$index) = explode('-',$part);

                if (!$tag) {
                    continue;
                }

                $isFirst || $path .= '{"childNodes": ';
                $path .= "{\"$tag\": {\"$index\": ";
                $isFirst = false;
            }

            if ($specific['mode'] == 'none') {

                $path .= '[]';
                $path .= str_repeat('}',substr_count($path,'{'));

                $arrayXml = Mage::helper('M2ePro')->arrayReplaceRecursive(
                    $arrayXml,
                    json_decode($path,true)
                );

                continue;
            }

            $value = $specific[$specific['mode']];

            if ($specific['mode'] == 'custom_attribute') {
                $value = $this->listingProduct->getActualMagentoProduct()->getAttributeValue($specific['custom_attribute']);
            }

            $specific['type'] == 'int' && $value = (int)$value;
            $specific['type'] == 'float' && $value = (float)str_replace(',','.',$value);
            $specific['type'] == 'date_time' && $value = str_replace(' ','T',$value);

            $attributes = array();
            foreach (json_decode($specific['attributes'],1) as $i=>$attribute) {

                list($attributeName) = array_keys($attribute);

                $attributeData = $attribute[$attributeName];

                $attributeValue = $attributeData['mode'] == 'custom_value'
                    ? $attributeData['custom_value']
                    : $this->listingProduct->getActualMagentoProduct()->getAttributeValue($attributeData['custom_attribute']);

                $attributes[$i] = array(
                    'name' => str_replace(' ','',$attributeName),
                    'value' => $attributeValue,
                );
            }

            $attributes = json_encode($attributes);

            $path .= '%data%';
            $path .= str_repeat('}',substr_count($path,'{'));

            $path = str_replace(
                '%data%',
                "{\"value\": ".json_encode($value).",\"attributes\": $attributes}",
                $path
            );

            $arrayXml = Mage::helper('M2ePro')->arrayReplaceRecursive(
                $arrayXml,
                json_decode($path,true)
            );
        }

        return $arrayXml;
    }

    // ---------------------------------------

    public function getDescriptionData()
    {
        $descriptionData = array(
            'title' => $this->getTitle(),
            'brand' => $this->getBrand(),
            'description' => $this->getDescription(),
            'bullets' => $this->getBulletPoints(),
            'search_terms' => $this->getSearchTerms(),
            'manufacturer' => $this->getManufacturer(),
            'manufacturer_part_number' => $this->getManufacturerPartNumber(),
            'package_weight' => $this->getPackageWeight(),
            'shipping_weight' => $this->getShippingWeight(),
            'package_weight_unit_of_measure' => $this->getPackageWeightUnitOfMeasure(),
            'shipping_weight_unit_of_measure' => $this->getShippingWeightUnitOfMeasure(),
            'target_audience' => $this->getTargetAudience(),
        );

        $categoryIdentifiers = $this->templateNewProduct->getCategoryIdentifiers();

        $descriptionData['item_types'] = $categoryIdentifiers['item_types'];
        $descriptionData['browsenode_id'] = $categoryIdentifiers['browsenode_id'];

        return $descriptionData;
    }

    // ---------------------------------------

    public function getImagesData()
    {
        if ($this->templateNewProductDescription->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            return array();
        }

        $mainImage = array($mainImage);

        if ($this->templateNewProductDescription->isGalleryImagesModeNone()) {
            return $mainImage;
        }

        $galleryImages = array();
        $gallerySource = $this->templateNewProductDescription->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->templateNewProductDescription->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImages = $this->listingProduct
                ->getActualMagentoProduct()
                ->getGalleryImagesLinks((int)$gallerySource['limit']+1);
        }

        if ($this->templateNewProductDescription->isGalleryImagesModeAttribute()) {
            $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;
            $galleryImagesTemp = $this->listingProduct
                ->getActualMagentoProduct()
                ->getAttributeValue($gallerySource['attribute']);
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

    public function getTitle()
    {
        $src = $this->templateNewProductDescription->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::TITLE_MODE_PRODUCT:
                $title = $this->listingProduct->getActualMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::TITLE_MODE_CUSTOM:
                $title = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'],
                    $this->listingProduct->getActualMagentoProduct()
                );
                break;

            default:
                $title = $this->listingProduct->getActualMagentoProduct()->getName();
                break;
        }

        return $title;
    }

    public function getBrand()
    {
        $brand = '';
        $src = $this->templateNewProductDescription->getBrandSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::BRAND_MODE_CUSTOM) {
            $brand = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $src['template'],
                $this->listingProduct->getActualMagentoProduct()
            );
        }

        return $brand;
    }

    public function getDescription()
    {
        $src = $this->templateNewProductDescription->getDescriptionSource();
        /* @var $templateProcessor Mage_Core_Model_Email_Template_Filter */
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::DESCRIPTION_MODE_PRODUCT:
                $description = $this->listingProduct->getActualMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::DESCRIPTION_MODE_SHORT:
                $description = $this->listingProduct->getActualMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $src['template'],
                    $this->listingProduct->getActualMagentoProduct()
                );
                break;

            default:
                $description = '';
                break;
        }

        $allowedTags = array('<p>', '<br>', '<ul>', '<li>');

        $description = str_replace(array('<![CDATA[', ']]>'), '', $description);
        $description = strip_tags($description,implode($allowedTags));

        return $description;
    }

    public function getBulletPoints()
    {
        $bullets = array();

        $src = $this->templateNewProductDescription->getBulletPointsSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::BULLET_POINTS_MODE_CUSTOM) {

            foreach ($src['template'] as $bullet) {
                $bullets[] = strip_tags(
                    Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                        $bullet,
                        $this->listingProduct->getActualMagentoProduct()
                    )
                );
            }
        }

        return $bullets;
    }

    public function getSearchTerms()
    {
        $searchTerms = array();

        $src = $this->templateNewProductDescription->getSearchTermsSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::SEARCH_TERMS_MODE_CUSTOM) {

            foreach ($src['template'] as $searchTerm) {
                $searchTerms[] = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                    $searchTerm,
                    $this->listingProduct->getActualMagentoProduct()
                );
            }
        }

        return $searchTerms;
    }

    public function getManufacturer()
    {
        $manufacturer = '';
        $src = $this->templateNewProductDescription->getManufacturerSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_NewProduct_Description::MANUFACTURER_MODE_CUSTOM) {
            $manufacturer = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                $src['template'],
                $this->listingProduct->getActualMagentoProduct()
            );
        }

        return $manufacturer;
    }

    public function getManufacturerPartNumber()
    {
        $src = $this->templateNewProductDescription->getManufacturerPartNumberSource();

        if ($this->templateNewProductDescription->isManufacturerPartNumberModeNone()) {
            return NULL;
        }

        if ($this->templateNewProductDescription->isManufacturerPartNumberModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->listingProduct->getActualMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    public function getPackageWeight()
    {
        $packageWeight = NULL;
        $src = $this->templateNewProductDescription->getPackageWeightSource();

        if ($this->templateNewProductDescription->isPackageWeightModeNone()) {
            return $packageWeight;
        }

        if ($this->templateNewProductDescription->isPackageWeightModeCustomValue()) {
            $packageWeight = $src['custom_value'];
        } else {
            $packageWeight = $this->listingProduct->getActualMagentoProduct()->getAttributeValue($src['custom_attribute']);
        }

        $packageWeight = str_replace(',','.',$packageWeight);
        $packageWeight = round((float)$packageWeight,2);

        return $packageWeight;
    }

    public function getPackageWeightUnitOfMeasure()
    {
        $packageWeightUnitOfMeasure = NULL;
        $src = $this->templateNewProductDescription->getPackageWeightUnitOfMeasureSource();

        if ($this->templateNewProductDescription->isPackageWeightUnitOfMeasureModeCustomValue()) {
            $packageWeightUnitOfMeasure = $src['custom_value'];
        } else {
            $packageWeightUnitOfMeasure = $this->listingProduct->getActualMagentoProduct()
                                                               ->getAttributeValue($src['custom_attribute']);
        }

        return trim($packageWeightUnitOfMeasure);
    }

    public function getShippingWeight()
    {
        $shippingWeight = NULL;
        $src = $this->templateNewProductDescription->getShippingWeightSource();

        if ($this->templateNewProductDescription->isShippingWeightModeNone()) {
            return $shippingWeight;
        }

        if ($this->templateNewProductDescription->isShippingWeightModeCustomValue()) {
            $shippingWeight = $src['custom_value'];
        } else {
            $shippingWeight = $this->listingProduct->getActualMagentoProduct()
                                                    ->getAttributeValue($src['custom_attribute']);
        }

        $shippingWeight = str_replace(',','.',$shippingWeight);
        $shippingWeight = round((float)$shippingWeight,2);

        return $shippingWeight;
    }

    public function getShippingWeightUnitOfMeasure()
    {
        $shippingWeightUnitOfMeasure = NULL;
        $src = $this->templateNewProductDescription->getShippingWeightUnitOfMeasureSource();

        if ($this->templateNewProductDescription->isShippingWeightUnitOfMeasureModeCustomValue()) {
            $shippingWeightUnitOfMeasure = $src['custom_value'];
        } else {
            $shippingWeightUnitOfMeasure = $this->listingProduct->getActualMagentoProduct()
                                                                ->getAttributeValue($src['custom_attribute']);
        }

        return trim($shippingWeightUnitOfMeasure);
    }

    public function getTargetAudience()
    {
        $src = $this->templateNewProductDescription->getTargetAudienceSource();

        if ($this->templateNewProductDescription->isTargetAudienceModeNone()) {
            return NULL;
        }

        if ($this->templateNewProductDescription->isTargetAudienceModeCustomValue()) {
            return trim($src['custom_value']);
        }

        return trim($this->listingProduct->getActualMagentoProduct()->getAttributeValue($src['custom_attribute']));
    }

    // ########################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->templateNewProductDescription->isImageMainModeProduct()) {
            $imageLink = $this->listingProduct->getActualMagentoProduct()->getImageLink('image');
        }

        if ($this->templateNewProductDescription->isImageMainModeAttribute()) {
            $src = $this->templateNewProductDescription->getImageMainSource();
            $imageLink = $this->listingProduct->getActualMagentoProduct()->getImageLink($src['attribute']);
        }

        return $imageLink;
    }

    // ########################################

}