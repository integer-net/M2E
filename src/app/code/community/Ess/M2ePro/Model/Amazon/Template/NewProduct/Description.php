<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_NewProduct_Description extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    const TITLE_MODE_PRODUCT = 0;
    const TITLE_MODE_CUSTOM  = 1;

    const BRAND_MODE_NONE = 0;
    const BRAND_MODE_CUSTOM = 1;

    const MANUFACTURER_MODE_NONE = 0;
    const MANUFACTURER_MODE_CUSTOM = 1;

    const MANUFACTURER_PART_NUMBER_MODE_NONE = 0;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE = 1;
    const MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE = 2;

    const PACKAGE_WEIGHT_MODE_NONE = 0;
    const PACKAGE_WEIGHT_MODE_CUSTOM_VALUE = 1;
    const PACKAGE_WEIGHT_MODE_CUSTOM_ATTRIBUTE = 2;

    const PACKAGE_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE = 1;
    const PACKAGE_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE = 2;

    const SHIPPING_WEIGHT_MODE_NONE = 0;
    const SHIPPING_WEIGHT_MODE_CUSTOM_VALUE = 1;
    const SHIPPING_WEIGHT_MODE_CUSTOM_ATTRIBUTE = 2;

    const SHIPPING_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE = 1;
    const SHIPPING_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE = 2;

    const TARGET_AUDIENCE_MODE_NONE = 0;
    const TARGET_AUDIENCE_MODE_CUSTOM_VALUE = 1;
    const TARGET_AUDIENCE_MODE_CUSTOM_ATTRIBUTE = 2;

    const SEARCH_TERMS_MODE_NONE = 0;
    const SEARCH_TERMS_MODE_CUSTOM = 1;

    const BULLET_POINTS_MODE_NONE   = 0;
    const BULLET_POINTS_MODE_CUSTOM = 1;

    const DESCRIPTION_MODE_NONE     = 0;
    const DESCRIPTION_MODE_PRODUCT  = 1;
    const DESCRIPTION_MODE_SHORT    = 2;
    const DESCRIPTION_MODE_CUSTOM   = 3;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    const GALLERY_IMAGES_MODE_NONE      = 0;
    const GALLERY_IMAGES_MODE_PRODUCT   = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_NewProduct_Description');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->delete();

        return true;
    }

    // ########################################

    public function getTitleMode()
    {
        return (int)$this->getData('title_mode');
    }

    public function isTitleModeProduct()
    {
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT;
    }

    public function isTitleModeCustom()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM;
    }

    public function getTitleSource()
    {
        return array(
            'mode'     => $this->getTitleMode(),
            'template' => $this->getData('title_template')
        );
    }

    public function getTitleAttributes()
    {
        $attributes = array();
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBrandMode()
    {
        return (int)$this->getData('brand_mode');
    }

    public function isBrandModeNone()
    {
        return $this->getBrandMode() == self::BRAND_MODE_NONE;
    }

    public function isBrandModeCustom()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM;
    }

    public function getBrandSource()
    {
        return array(
            'mode'     => $this->getBrandMode(),
            'template' => $this->getData('brand_template')
        );
    }

    public function getBrandAttributes()
    {
        $attributes = array();
        $src = $this->getBrandSource();

        if ($src['mode'] == self::BRAND_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerMode()
    {
        return (int)$this->getData('manufacturer_mode');
    }

    public function isManufacturerModeNone()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_NONE;
    }

    public function isManufacturerModeCustom()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM;
    }

    public function getManufacturerSource()
    {
        return array(
            'mode'     => $this->getManufacturerMode(),
            'template' => $this->getData('manufacturer_template')
        );
    }

    public function getManufacturerAttributes()
    {
        $attributes = array();
        $src = $this->getManufacturerSource();

        if ($src['mode'] == self::MANUFACTURER_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerPartNumberMode()
    {
        return (int)$this->getData('manufacturer_part_number_mode');
    }

    public function getManufacturerPartNumberCustomValue()
    {
        return $this->getData('manufacturer_part_number_custom_value');
    }

    public function getManufacturerPartNumberCustomAttribute()
    {
        return $this->getData('manufacturer_part_number_custom_attribute');
    }

    public function isManufacturerPartNumberModeNone()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_NONE;
    }

    public function isManufacturerPartNumberModeCustomValue()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_VALUE;
    }

    public function isManufacturerPartNumberModeCustomAttribute()
    {
        return $this->getManufacturerPartNumberMode() == self::MANUFACTURER_PART_NUMBER_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getManufacturerPartNumberSource()
    {
        return array(
            'mode'     => $this->getManufacturerPartNumberMode(),
            'custom_value' => $this->getManufacturerPartNumberCustomValue(),
            'custom_attribute' => $this->getManufacturerPartNumberCustomAttribute()
        );
    }

    //-------------------------

    public function getPackageWeightMode()
    {
        return (int)$this->getData('package_weight_mode');
    }

    public function getPackageWeightCustomValue()
    {
        return $this->getData('package_weight_custom_value');
    }

    public function getPackageWeightCustomAttribute()
    {
        return $this->getData('package_weight_custom_attribute');
    }

    public function isPackageWeightModeNone()
    {
        return $this->getPackageWeightMode() == self::PACKAGE_WEIGHT_MODE_NONE;
    }

    public function isPackageWeightModeCustomValue()
    {
        return $this->getPackageWeightMode() == self::PACKAGE_WEIGHT_MODE_CUSTOM_VALUE;
    }

    public function isPackageWeightModeCustomAttribute()
    {
        return $this->getPackageWeightMode() == self::PACKAGE_WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPackageWeightSource()
    {
        return array(
            'mode'     => $this->getPackageWeightMode(),
            'custom_value' => $this->getPackageWeightCustomValue(),
            'custom_attribute' => $this->getPackageWeightCustomAttribute()
        );
    }

    //-------------------------

    public function getPackageWeightUnitOfMeasureMode()
    {
        return (int)$this->getData('package_weight_unit_of_measure_mode');
    }

    public function getPackageWeightUnitOfMeasureCustomValue()
    {
        return $this->getData('package_weight_unit_of_measure_custom_value');
    }

    public function getPackageWeightUnitOfMeasureCustomAttribute()
    {
        return $this->getData('package_weight_unit_of_measure_custom_attribute');
    }

    public function isPackageWeightUnitOfMeasureModeCustomValue()
    {
        return $this->getPackageWeightUnitOfMeasureMode() == self::PACKAGE_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isPackageWeightUnitOfMeasureModeCustomAttribute()
    {
        return $this->getPackageWeightUnitOfMeasureMode() == self::PACKAGE_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getPackageWeightUnitOfMeasureSource()
    {
        return array(
            'mode'     => $this->getPackageWeightUnitOfMeasureMode(),
            'custom_value' => $this->getPackageWeightUnitOfMeasureCustomValue(),
            'custom_attribute' => $this->getPackageWeightUnitOfMeasureCustomAttribute()
        );
    }

    //-------------------------

    public function getShippingWeightMode()
    {
        return (int)$this->getData('shipping_weight_mode');
    }

    public function getShippingWeightCustomValue()
    {
        return $this->getData('shipping_weight_custom_value');
    }

    public function getShippingWeightCustomAttribute()
    {
        return $this->getData('shipping_weight_custom_attribute');
    }

    public function isShippingWeightModeNone()
    {
        return $this->getShippingWeightMode() == self::SHIPPING_WEIGHT_MODE_NONE;
    }

    public function isShippingWeightModeCustomValue()
    {
        return $this->getShippingWeightMode() == self::SHIPPING_WEIGHT_MODE_CUSTOM_VALUE;
    }

    public function isShippingWeightModeCustomAttribute()
    {
        return $this->getShippingWeightMode() == self::SHIPPING_WEIGHT_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingWeightSource()
    {
        return array(
            'mode'     => $this->getShippingWeightMode(),
            'custom_value' => $this->getShippingWeightCustomValue(),
            'custom_attribute' => $this->getShippingWeightCustomAttribute()
        );
    }

    //-------------------------

    public function getShippingWeightUnitOfMeasureMode()
    {
        return (int)$this->getData('shipping_weight_unit_of_measure_mode');
    }

    public function getShippingWeightUnitOfMeasureCustomValue()
    {
        return $this->getData('shipping_weight_unit_of_measure_custom_value');
    }

    public function getShippingWeightUnitOfMeasureCustomAttribute()
    {
        return $this->getData('shipping_weight_unit_of_measure_custom_attribute');
    }

    public function isShippingWeightUnitOfMeasureModeCustomValue()
    {
        return $this->getShippingWeightUnitOfMeasureMode() == self::SHIPPING_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_VALUE;
    }

    public function isShippingWeightUnitOfMeasureModeCustomAttribute()
    {
        return $this->getShippingWeightUnitOfMeasureMode() ==
                self::SHIPPING_WEIGHT_UNIT_OF_MEASURE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getShippingWeightUnitOfMeasureSource()
    {
        return array(
            'mode'     => $this->getShippingWeightUnitOfMeasureMode(),
            'custom_value' => $this->getShippingWeightUnitOfMeasureCustomValue(),
            'custom_attribute' => $this->getShippingWeightUnitOfMeasureCustomAttribute()
        );
    }

    //-------------------------

    public function getTargetAudienceMode()
    {
        return (int)$this->getData('target_audience_mode');
    }

    public function getTargetAudienceCustomValue()
    {
        return $this->getData('target_audience_custom_value');
    }

    public function getTargetAudienceCustomAttribute()
    {
        return $this->getData('target_audience_custom_attribute');
    }

    public function isTargetAudienceModeNone()
    {
        return $this->getTargetAudienceMode() == self::TARGET_AUDIENCE_MODE_NONE;
    }

    public function isTargetAudienceModeCustomValue()
    {
        return $this->getTargetAudienceMode() == self::TARGET_AUDIENCE_MODE_CUSTOM_VALUE;
    }

    public function isTargetAudienceModeCustomAttribute()
    {
        return $this->getTargetAudienceMode() == self::TARGET_AUDIENCE_MODE_CUSTOM_ATTRIBUTE;
    }

    public function getTargetAudienceSource()
    {
        return array(
            'mode'     => $this->getTargetAudienceMode(),
            'custom_value' => $this->getTargetAudienceCustomValue(),
            'custom_attribute' => $this->getTargetAudienceCustomattribute()
        );
    }

    //-------------------------

    public function getSearchTermsMode()
    {
        return (int)$this->getData('search_terms_mode');
    }

    public function getSearchTermsTemplate()
    {
        return is_null($this->getData('search_terms')) ? array() : json_decode($this->getData('search_terms'),true);
    }

    public function isSearchTermsModeNone()
    {
        return $this->getSearchTermsMode() == self::SEARCH_TERMS_MODE_NONE;
    }

    public function isSearchTermsModeCustom()
    {
        return $this->getSearchTermsMode() == self::SEARCH_TERMS_MODE_CUSTOM;
    }

    public function getSearchTermsSource()
    {
        return array(
            'mode'     => $this->getSearchTermsMode(),
            'template' => $this->getSearchTermsTemplate()
        );
    }

    public function getSearchTermsAttributes()
    {
        $src = $this->getSearchTermsSource();

        if ($src['mode'] == self::SEARCH_TERMS_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::SEARCH_TERMS_MODE_CUSTOM) {
            $match = array();
            $searchTerms = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_]+?)#/', $searchTerms, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBulletPointsMode()
    {
        return (int)$this->getData('bullet_points_mode');
    }

    public function getBulletPointsTemplate()
    {
        return is_null($this->getData('bullet_points')) ? array() : json_decode($this->getData('bullet_points'),true);
    }

    public function isBulletPointsModeNone()
    {
        return $this->getBulletPointsMode() == self::BULLET_POINTS_MODE_NONE;
    }

    public function isBulletPointsModeCustom()
    {
        return $this->getBulletPointsMode() == self::BULLET_POINTS_MODE_CUSTOM;
    }

    public function getBulletPointsSource()
    {
        return array(
            'mode'     => $this->getBulletPointsMode(),
            'template' => $this->getBulletPointsTemplate()
        );
    }

    public function getBulletPointsAttributes()
    {
        $src = $this->getBulletPointsSource();

        if ($src['mode'] == self::BULLET_POINTS_MODE_NONE) {
            return array();
        }

        $attributes = array();

        if ($src['mode'] == self::BULLET_POINTS_MODE_CUSTOM) {
            $match = array();
            $bullets = implode(PHP_EOL,$src['template']);
            preg_match_all('/#([a-zA-Z_]+?)#/', $bullets, $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getDescriptionMode()
    {
        return (int)$this->getData('description_mode');
    }

    public function isDescriptionModeNone()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_NONE;
    }

    public function isDescriptionModeProduct()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT;
    }

    public function isDescriptionModeShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_SHORT;
    }

    public function isDescriptionModeCustom()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_CUSTOM;
    }

    public function getDescriptionSource()
    {
        return array(
            'mode'     => $this->getDescriptionMode(),
            'template' => $this->getData('description_template')
        );
    }

    public function getDescriptionAttributes()
    {
        $attributes = array();
        $src = $this->getDescriptionSource();

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_SHORT) {
            $attributes[] = 'short_description';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource()
    {
        return array(
            'mode'     => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getGalleryImagesMode()
    {
        return (int)$this->getData('gallery_images_mode');
    }

    public function isGalleryImagesModeNone()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_NONE;
    }

    public function isGalleryImagesModeProduct()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_PRODUCT;
    }

    public function isGalleryImagesModeAttribute()
    {
        return $this->getGalleryImagesMode() == self::GALLERY_IMAGES_MODE_ATTRIBUTE;
    }

    public function getGalleryImagesSource()
    {
        return array(
            'mode'     => $this->getGalleryImagesMode(),
            'attribute' => $this->getData('gallery_images_attribute'),
            'limit' => $this->getData('gallery_images_limit')
        );
    }

    public function getGalleryImagesAttributes()
    {
        $attributes = array();
        $src = $this->getGalleryImagesSource();

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ########################################
}