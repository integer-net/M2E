<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_Description getParentObject()
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

    const CUT_LONG_TITLE_DISABLED = 0;
    const CUT_LONG_TITLE_ENABLED  = 1;

    const EDITOR_TYPE_SIMPLE    = 0;
    const EDITOR_TYPE_TINYMCE   = 1;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    const GALLERY_IMAGES_MODE_NONE      = 0;
    const GALLERY_IMAGES_MODE_PRODUCT   = 1;
    const GALLERY_IMAGES_MODE_ATTRIBUTE = 2;

    const USE_SUPERSIZE_IMAGES_NO  = 0;
    const USE_SUPERSIZE_IMAGES_YES = 1;

    const WATERMARK_MODE_NO = 0;
    const WATERMARK_MODE_YES = 1;

    const WATERMARK_POSITION_TOP = 0;
    const WATERMARK_POSITION_MIDDLE = 1;
    const WATERMARK_POSITION_BOTTOM = 2;

    const WATERMARK_SCALE_MODE_NONE = 0;
    const WATERMARK_SCALE_MODE_IN_WIDTH = 1;
    const WATERMARK_SCALE_MODE_STRETCH = 2;

    const WATERMARK_TRANSPARENT_MODE_NO = 0;
    const WATERMARK_TRANSPARENT_MODE_YES = 1;

    const WATERMARK_CACHE_TIME = 604800; // 7 days

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Description');
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getListings($asObjects,$filters);
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

    //-------------------------

    public function isCutLongTitles()
    {
        return (bool)$this->getData('cut_long_titles');
    }

    public function getHitCounterType()
    {
        return $this->getData('hit_counter');
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

    //-------------------------

    public function isWatermarkEnabled()
    {
        return (int)$this->getData('watermark_mode') == self::WATERMARK_MODE_YES;
    }

    public function getWatermarkPosition()
    {
        return (int)$this->getSetting('watermark_settings', 'position');
    }

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

    public function getWatermarkScaleMode()
    {
        return (int)$this->getSetting('watermark_settings', 'scale');
    }

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

    public function getWatermarkTransparentMode()
    {
        return (int)$this->getSetting('watermark_settings', 'transparent');
    }

    public function isWatermarkTransparentEnabled()
    {
        return $this->getWatermarkTransparentMode() == self::WATERMARK_TRANSPARENT_MODE_YES;
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

    public function addWatermarkIfNeed($imageLink)
    {
        if (!$this->isWatermarkEnabled()) {
            return $imageLink;
        }

        $imagePath = $this->imageLinkToPath($imageLink);
        if (!is_file($imagePath)) {
            return $imageLink;
        }

        $fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);

        $markingImagePath = $imagePath.'-'.$this->getWatermarkHash().$fileExtension;
        if (is_file($markingImagePath)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($markingImagePath) + self::WATERMARK_CACHE_TIME > $currentTime) {
                return $this->pathToImageLink($markingImagePath);
            }

            @unlink($markingImagePath);
        }

        $prevMarkingImagePath = $imagePath.'-'.$this->getWatermarkPreviousHash().$fileExtension;
        if (is_file($prevMarkingImagePath)) {
            @unlink($prevMarkingImagePath);
        }

        $varDir = new Ess_M2ePro_Model_General_VariablesDir(array(
            'child_folder' => 'ebay/template/description/watermarks'
        ));
        $watermarkPath = $varDir->getPath().$this->getId().'.png';
        if (!is_file($watermarkPath)) {
            $varDir->create();
            @file_put_contents($watermarkPath, $this->getWatermarkImage());
        }

        $watermarkPositions = array(
            self::WATERMARK_POSITION_TOP => Varien_Image_Adapter_Abstract::POSITION_TOP_RIGHT,
            self::WATERMARK_POSITION_MIDDLE => Varien_Image_Adapter_Abstract::POSITION_CENTER,
            self::WATERMARK_POSITION_BOTTOM => Varien_Image_Adapter_Abstract::POSITION_BOTTOM_RIGHT
        );

        $image = new Varien_Image($imagePath);
        $imageOriginalHeight = $image->getOriginalHeight();
        $imageOriginalWidth = $image->getOriginalWidth();
        $image->open();
        $image->setWatermarkPosition($watermarkPositions[$this->getWatermarkPosition()]);

        $watermark = new Varien_Image($watermarkPath);
        $watermarkOriginalHeight = $watermark->getOriginalHeight();
        $watermarkOriginalWidth = $watermark->getOriginalWidth();

        if ($this->isWatermarkScaleModeStretch()) {
            $image->setWatermarkPosition(Varien_Image_Adapter_Abstract::POSITION_STRETCH);
        }

        if ($this->isWatermarkScaleModeInWidth()) {
            $watermarkWidth = $imageOriginalWidth;
            $heightPercent = $watermarkOriginalWidth / $watermarkWidth;
            $watermarkHeight = (int)($watermarkOriginalHeight / $heightPercent);

            $image->setWatermarkWidth($watermarkWidth);
            $image->setWatermarkHeigth($watermarkHeight);
        }

        if ($this->isWatermarkScaleModeNone()) {
            $image->setWatermarkWidth($watermarkOriginalWidth);
            $image->setWatermarkHeigth($watermarkOriginalHeight);

            if ($watermarkOriginalHeight > $imageOriginalHeight) {
                $image->setWatermarkHeigth($imageOriginalHeight);
                $widthPercent = $watermarkOriginalHeight / $imageOriginalHeight;
                $watermarkWidth = (int)($watermarkOriginalWidth / $widthPercent);
                $image->setWatermarkWidth($watermarkWidth);
            }

            if ($watermarkOriginalWidth > $imageOriginalWidth) {
                $image->setWatermarkWidth($imageOriginalWidth);
                $heightPercent = $watermarkOriginalWidth / $imageOriginalWidth;
                $watermarkHeight = (int)($watermarkOriginalHeight / $heightPercent);
                $image->setWatermarkHeigth($watermarkHeight);
            }
        }

        $opacity = 100;
        if ($this->isWatermarkTransparentEnabled()) {
            $opacity = 30;
        }

        $image->setWatermarkImageOpacity($opacity);
        $image->watermark($watermarkPath);
        $image->save($markingImagePath);

        return $this->pathToImageLink($markingImagePath);
    }

    //-------------------------

    public function imageLinkToPath($imageLink)
    {
        $imageLink = str_replace('%20', ' ', $imageLink);

        $baseMediaUrl = Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl();
        $baseMediaUrl = str_replace('https://', 'http://', $baseMediaUrl);

        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imagePath = str_replace($baseMediaUrl, $baseMediaPath, $imageLink);
        $imagePath = str_replace('/', DS, $imagePath);
        $imagePath = str_replace('\\', DS, $imagePath);

        return $imagePath;
    }

    public function pathToImageLink($path)
    {
        $baseMediaUrl = Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl();
        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imageLink = str_replace($baseMediaPath, $baseMediaUrl, $path);
        $imageLink = str_replace(DS, '/', $imageLink);

        $imageLink = str_replace('https://', 'http://', $imageLink);

        return str_replace(' ', '%20', $imageLink);
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
        return (int)$this->getData('use_supersize_images') == self::USE_SUPERSIZE_IMAGES_YES;
    }

    // ########################################

    public function cutLongTitles($str, $length = 80)
    {
        $str = trim($str);

        if ($str === '' || strlen($str) <= $length) {
            return $str;
        }

        if (!preg_match('/^.{0,'.$length.'}\s/us', $str, $matches)) {
            return '';
        }

        if (!isset($matches[0])) {
            return '';
        }

        $str = trim($matches[0]);

        return $str;
    }

    // ########################################

    public function getTrackingAttributes()
    {
        $tempArray = array_unique(array_merge(
            $this->getTitleAttributes(),
            $this->getSubTitleAttributes(),
            $this->getDescriptionAttributes()
        ));

        $resultArray = array();
        foreach ($tempArray as $attribute) {
            if (strpos($attribute,'media_gallery') !== false) {
                continue;
            }
            $resultArray[] = $attribute;
        }

        return $resultArray;
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro')->removeTagCacheValues('template_description');
        return parent::delete();
    }

    // ########################################
}