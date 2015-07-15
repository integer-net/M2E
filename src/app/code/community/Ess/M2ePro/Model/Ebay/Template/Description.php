<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_Description getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Description extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const TITLE_MODE_PRODUCT = 0;
    const TITLE_MODE_CUSTOM  = 1;

    const SUBTITLE_MODE_NONE     = 0;
    const SUBTITLE_MODE_CUSTOM   = 1;

    const DESCRIPTION_MODE_PRODUCT = 0;
    const DESCRIPTION_MODE_SHORT   = 1;
    const DESCRIPTION_MODE_CUSTOM  = 2;

    const CONDITION_MODE_EBAY       = 0;
    const CONDITION_MODE_ATTRIBUTE  = 1;
    const CONDITION_MODE_NONE       = 2;

    const CONDITION_EBAY_NEW                        = 1000;
    const CONDITION_EBAY_NEW_OTHER                  = 1500;
    const CONDITION_EBAY_NEW_WITH_DEFECT            = 1750;
    const CONDITION_EBAY_MANUFACTURER_REFURBISHED   = 2000;
    const CONDITION_EBAY_SELLER_REFURBISHED         = 2500;
    const CONDITION_EBAY_USED                       = 3000;
    const CONDITION_EBAY_VERY_GOOD                  = 4000;
    const CONDITION_EBAY_GOOD                       = 5000;
    const CONDITION_EBAY_ACCEPTABLE                 = 6000;
    const CONDITION_EBAY_NOT_WORKING                = 7000;

    const CONDITION_NOTE_MODE_NONE    = 0;
    const CONDITION_NOTE_MODE_CUSTOM  = 1;

    const EDITOR_TYPE_SIMPLE    = 0;
    const EDITOR_TYPE_TINYMCE   = 1;

    const CUT_LONG_TITLE_DISABLED = 0;
    const CUT_LONG_TITLE_ENABLED  = 1;

    const HIT_COUNTER_NONE          = 'NoHitCounter';
    const HIT_COUNTER_BASIC_STYLE   = 'BasicStyle';
    const HIT_COUNTER_GREEN_LED     = 'GreenLED';
    const HIT_COUNTER_HIDDEN_STYLE  = 'HiddenStyle';
    const HIT_COUNTER_HONESTY_STYLE = 'HonestyStyle';
    const HIT_COUNTER_RETRO_STYLE   = 'RetroStyle';

    const GALLERY_TYPE_EMPTY    = 4;
    const GALLERY_TYPE_NO       = 0;
    const GALLERY_TYPE_PICTURE  = 1;
    const GALLERY_TYPE_PLUS     = 2;
    const GALLERY_TYPE_FEATURED = 3;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    const GALLERY_IMAGES_MODE_NONE      = 0;
    const GALLERY_IMAGES_MODE_PRODUCT   = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    const USE_SUPERSIZE_IMAGES_NO  = 0;
    const USE_SUPERSIZE_IMAGES_YES = 1;

    const WATERMARK_MODE_NO   = 0;
    const WATERMARK_MODE_YES  = 1;

    const WATERMARK_POSITION_TOP = 0;
    const WATERMARK_POSITION_MIDDLE = 1;
    const WATERMARK_POSITION_BOTTOM = 2;

    const WATERMARK_SCALE_MODE_NONE = 0;
    const WATERMARK_SCALE_MODE_IN_WIDTH = 1;
    const WATERMARK_SCALE_MODE_STRETCH = 2;

    const WATERMARK_TRANSPARENT_MODE_NO = 0;
    const WATERMARK_TRANSPARENT_MODE_YES = 1;

    const WATERMARK_CACHE_TIME = 604800; // 7 days
    const GALLERY_IMAGES_COUNT_MAX = 11;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Description_Source
     */
    private $descriptionSourceModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Description');
    }

    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_description_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_description_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_description_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_description_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        // Delete watermark if exists
        // ----------------------------------
        $varDir = new Ess_M2ePro_Model_VariablesDir(
            array('child_folder' => 'ebay/template/description/watermarks')
        );

        $watermarkPath = $varDir->getPath().$this->getId().'.png';
        if (is_file($watermarkPath)) {
            @unlink($watermarkPath);
        }
        // ----------------------------------

        $temp = parent::deleteInstance();
        $temp && $this->descriptionSourceModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_Description_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        if (!empty($this->descriptionSourceModel)) {
            return $this->descriptionSourceModel;
        }

        $this->descriptionSourceModel = Mage::getModel('M2ePro/Ebay_Template_Description_Source');
        $this->descriptionSourceModel->setMagentoProduct($magentoProduct);
        $this->descriptionSourceModel->setDescriptionTemplate($this->getParentObject());

        return $this->descriptionSourceModel;
    }

    // ########################################

    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    // #######################################

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

    public function getSubTitleMode()
    {
        return (int)$this->getData('subtitle_mode');
    }

    public function isSubTitleModeProduct()
    {
        return $this->getSubTitleMode() == self::SUBTITLE_MODE_NONE;
    }

    public function isSubTitleModeCustom()
    {
        return $this->getSubTitleMode() == self::SUBTITLE_MODE_CUSTOM;
    }

    public function getSubTitleSource()
    {
        return array(
            'mode'     => $this->getSubTitleMode(),
            'template' => $this->getData('subtitle_template')
        );
    }

    public function getSubTitleAttributes()
    {
        $attributes = array();
        $src = $this->getSubTitleSource();

        if ($src['mode'] == self::SUBTITLE_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getDescriptionMode()
    {
        return (int)$this->getData('description_mode');
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

    // #######################################

    public function getConditionSource()
    {
        return array(
            'mode'      => (int)$this->getData('condition_mode'),
            'value'     => (int)$this->getData('condition_value'),
            'attribute' => $this->getData('condition_attribute')
        );
    }

    public function getConditionAttributes()
    {
        $attributes = array();
        $src = $this->getConditionSource();

        if ($src['mode'] == self::CONDITION_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-----------------------------------------

    public function getConditionNoteSource()
    {
        return array(
            'mode'      => (int)$this->getData('condition_note_mode'),
            'template'  => $this->getData('condition_note_template')
        );
    }

    public function getConditionNoteAttributes()
    {
        $attributes = array();
        $src = $this->getConditionNoteSource();

        if ($src['mode'] == self::CONDITION_NOTE_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    // #######################################

    public function getProductDetails()
    {
        return $this->getSettings('product_details');
    }

    // ---------------------------------------

    public function isProductDetailsIncludeDescription()
    {
        $productDetails = $this->getProductDetails();
        return isset($productDetails['include_description']) ? (bool)$productDetails['include_description'] : true;
    }

    public function isProductDetailsIncludeImage()
    {
        $productDetails = $this->getProductDetails();
        return isset($productDetails['include_image']) ? (bool)$productDetails['include_image'] : true;
    }

    public function isProductDetailsListIfNoProduct()
    {
        $productDetails = $this->getProductDetails();
        return isset($productDetails['list_if_no_product']) ? (bool)$productDetails['list_if_no_product'] : true;
    }

    // ---------------------------------------

    public function getProductDetailAttribute($type)
    {
        if (!in_array($type, array('isbn', 'epid', 'upc', 'ean', 'gtin', 'brand', 'mpn'))) {
            throw new InvalidArgumentException('Unknown Product details name');
        }

        $productDetails = $this->getProductDetails();

        if (!is_array($productDetails) || !isset($productDetails[$type])) {
            return NULL;
        }

        return $productDetails[$type];
    }

    public function getProductDetailAttributes()
    {
        $attributes = array();

        $temp = $this->getProductDetailAttribute('isbn');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('epid');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('upc');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('ean');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('gtin');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('brand');
        $temp && $attributes[] = $temp;

        $temp = $this->getProductDetailAttribute('mpn');
        $temp && $attributes[] = $temp;

        return $attributes;
    }

    // #######################################

    public function isCutLongTitles()
    {
        return (bool)$this->getData('cut_long_titles');
    }

    public function getHitCounterType()
    {
        return $this->getData('hit_counter');
    }

    public function getEnhancements()
    {
        return $this->getData('enhancement') ? explode(',', $this->getData('enhancement')) : array();
    }

    //-------------------------

    public function getEditorType()
    {
        return (int)$this->getData('editor_type');
    }

    public function isEditorTypeSimple()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_SIMPLE;
    }

    public function isEditorTypeTinyMce()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_TINYMCE;
    }

    //-------------------------

    public function getGalleryType()
    {
        return (int)$this->getData('gallery_type');
    }

    public function isGalleryTypeEmpty()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_EMPTY;
    }

    public function isGalleryTypeNo()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_NO;
    }

    public function isGalleryTypePicture()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_PICTURE;
    }

    public function isGalleryTypeFeatured()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_FEATURED;
    }

    public function isGalleryTypePlus()
    {
        return $this->getGalleryType() == self::GALLERY_TYPE_PLUS;
    }

    // #######################################

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

        if ($src['mode'] == self::GALLERY_IMAGES_MODE_PRODUCT) {
            $attributes[] = 'media_gallery';
        } else if ($src['mode'] == self::GALLERY_IMAGES_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getDefaultImageUrl()
    {
        return $this->getData('default_image_url');
    }

    //-------------------------

    public function getVariationConfigurableImages()
    {
        return $this->getData('variation_configurable_images');
    }

    public function isVariationConfigurableImages()
    {
        return $this->getVariationConfigurableImages() != '';
    }

    //-------------------------

    public function isUseSupersizeImagesEnabled()
    {
        return (bool)$this->getData('use_supersize_images');
    }

    // #######################################

    public function isWatermarkEnabled()
    {
        return (bool)$this->getData('watermark_mode');
    }

    public function getWatermarkImage()
    {
        return $this->getData('watermark_image');
    }

    public function getWatermarkHash()
    {
        $settingNamePath = array(
            'hashes',
            'current'
        );

        return $this->getSetting('watermark_settings', $settingNamePath);
    }

    public function getWatermarkPreviousHash()
    {
        $settingNamePath = array(
            'hashes',
            'previous'
        );

        return $this->getSetting('watermark_settings', $settingNamePath);
    }

    public function updateWatermarkHashes()
    {
        $settings = $this->getSettings('watermark_settings');

        if (isset($settings['hashes']['current'])) {
            $settings['hashes']['previous'] = $settings['hashes']['current'];
        } else {
            $settings['hashes']['previous'] = '';
        }

        $settings['hashes']['current'] = substr(sha1(microtime()), 0, 5);

        $this->setSettings('watermark_settings', $settings);
        return $this;
    }

    //-------------------------

    public function getWatermarkPosition()
    {
        return (int)$this->getSetting('watermark_settings', 'position');
    }

    public function getWatermarkScaleMode()
    {
        return (int)$this->getSetting('watermark_settings', 'scale');
    }

    public function getWatermarkTransparentMode()
    {
        return (int)$this->getSetting('watermark_settings', 'transparent');
    }

    //-------------------------

    public function isWatermarkPositionTop()
    {
        return $this->getWatermarkPosition() == self::WATERMARK_POSITION_TOP;
    }

    public function isWatermarkPositionMiddle()
    {
        return $this->getWatermarkPosition() == self::WATERMARK_POSITION_MIDDLE;
    }

    public function isWatermarkPositionBottom()
    {
        return $this->getWatermarkPosition() == self::WATERMARK_POSITION_BOTTOM;
    }

    //-------------------------

    public function isWatermarkScaleModeNone()
    {
        return $this->getWatermarkScaleMode() == self::WATERMARK_SCALE_MODE_NONE;
    }

    public function isWatermarkScaleModeInWidth()
    {
        return $this->getWatermarkScaleMode() == self::WATERMARK_SCALE_MODE_IN_WIDTH;
    }

    public function isWatermarkScaleModeStretch()
    {
        return $this->getWatermarkScaleMode() == self::WATERMARK_SCALE_MODE_STRETCH;
    }

    //-------------------------

    public function isWatermarkTransparentEnabled()
    {
        return (bool)$this->getWatermarkTransparentMode();
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array_unique(array_merge(
            $this->getTitleAttributes(),
            $this->getSubTitleAttributes(),
            $this->getDescriptionAttributes(),
            $this->getImageMainAttributes(),
            $this->getGalleryImagesAttributes()
        ));
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getTitleAttributes(),
            $this->getSubTitleAttributes(),
            $this->getDescriptionAttributes(),
            $this->getConditionAttributes(),
            $this->getConditionNoteAttributes(),
            $this->getProductDetailAttributes(),
            $this->getImageMainAttributes(),
            $this->getGalleryImagesAttributes()
        ));
    }

    // #######################################

    public function getDefaultSettingsSimpleMode()
    {
        return array(

            'title_mode' => self::TITLE_MODE_PRODUCT,
            'title_template' => '',

            'subtitle_mode' => self::SUBTITLE_MODE_NONE,
            'subtitle_template' => '',

            'description_mode' => self::DESCRIPTION_MODE_PRODUCT,
            'description_template' => '',

            'condition_mode' => self::CONDITION_MODE_EBAY,
            'condition_value' => self::CONDITION_EBAY_NEW,
            'condition_attribute' => '',

            'condition_note_mode' => self::CONDITION_NOTE_MODE_NONE,
            'condition_note_template' => '',

            'product_details' => json_encode(array(
                'isbn'  => '',
                'epid'  => '',
                'upc'   => '',
                'ean'   => '',
                'gtin'  => '',
                'brand' => '',
                'mpn'   => '',
                'include_description' => 1,
                'include_image'       => 1,
                'list_if_no_product'  => 1,
            )),

            'editor_type' => self::EDITOR_TYPE_SIMPLE,
            'cut_long_titles' => self::CUT_LONG_TITLE_ENABLED,
            'hit_counter' => self::HIT_COUNTER_NONE,

            'enhancement' => '',
            'gallery_type' => self::GALLERY_TYPE_EMPTY,

            'image_main_mode' => self::IMAGE_MAIN_MODE_PRODUCT,
            'image_main_attribute' => '',
            'gallery_images_mode' => self::GALLERY_IMAGES_MODE_PRODUCT,
            'gallery_images_limit' => 3,
            'gallery_images_attribute' => '',
            'default_image_url' => '',

            'variation_configurable_images' => '',
            'use_supersize_images' => self::USE_SUPERSIZE_IMAGES_NO,

            'watermark_mode' => self::WATERMARK_MODE_NO,

            'watermark_settings' => json_encode(array(
                'position' => self::WATERMARK_POSITION_TOP,
                'scale' => self::WATERMARK_SCALE_MODE_NONE,
                'transparent' => self::WATERMARK_TRANSPARENT_MODE_NO,

                'hashes' => array(
                    'current'  => '',
                    'previous' => '',
                )
            )),

            'watermark_image' => NULL
        );
    }

    public function getDefaultSettingsAdvancedMode()
    {
        $simpleSettings = $this->getDefaultSettingsSimpleMode();
        $simpleSettings['gallery_images_mode'] = self::GALLERY_IMAGES_MODE_NONE;
        return $simpleSettings;
    }

    // #######################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays, $columns
        );

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
                                                $asArrays, $columns
                                            );

            foreach ($tempListingsProducts as $listingProduct) {
                if (!isset($listingsProducts[$listingProduct['id']])) {
                    $listingsProducts[$listingProduct['id']] = $listingProduct;
                }
            }
        }

        return $listingsProducts;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description');
        return parent::delete();
    }

    // #######################################
}