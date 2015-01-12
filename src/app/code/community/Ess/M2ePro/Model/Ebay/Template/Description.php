<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_Description getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Description extends Ess_M2ePro_Model_Component_Abstract
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
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

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
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    //--------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
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

    public function getProductDetailAttribute($type)
    {
        if (!in_array($type, array('isbn', 'epid', 'upc', 'ean', 'gtin', 'brand', 'mpn'))) {
            throw new InvalidArgumentException('Unknown product details name');
        }

        if (is_null($this->getData('product_details')) ||
            $this->getData('product_details') == '' ||
            $this->getData('product_details') == json_encode(array())) {
            return NULL;
        }

        $tempProductsDetails = $this->getProductDetails();

        if (!isset($tempProductsDetails[$type])) {
            return NULL;
        }

        return $tempProductsDetails[$type];
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
        $pathWithoutExtension = preg_replace('/\.'.$fileExtension.'$/', '', $imagePath);

        $markingImagePath = $pathWithoutExtension.'-'.$this->getWatermarkHash().'.'.$fileExtension;
        if (is_file($markingImagePath)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($markingImagePath) + self::WATERMARK_CACHE_TIME > $currentTime) {
                return $this->pathToImageLink($markingImagePath);
            }

            @unlink($markingImagePath);
        }

        $prevMarkingImagePath = $pathWithoutExtension.'-'.$this->getWatermarkPreviousHash().'.'.$fileExtension;
        if (is_file($prevMarkingImagePath)) {
            @unlink($prevMarkingImagePath);
        }

        $varDir = new Ess_M2ePro_Model_VariablesDir(array(
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

    public function cutLongTitles($str, $length = 80)
    {
        $str = trim($str);

        if ($str === '' || strlen($str) <= $length) {
            return $str;
        }

        return Mage::helper('core/string')->truncate($str, $length, '');
    }

    //---------------------------------------

    public function imageLinkToPath($imageLink)
    {
        $imageLink = str_replace('%20', ' ', $imageLink);

        $baseMediaUrl = Mage::app()->getStore($this->getMagentoProduct()->getStoreId())
                                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                                        'catalog/product';
        $baseMediaUrl = str_replace('https://', 'http://', $baseMediaUrl);

        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imagePath = str_replace($baseMediaUrl, $baseMediaPath, $imageLink);
        $imagePath = str_replace('/', DS, $imagePath);
        $imagePath = str_replace('\\', DS, $imagePath);

        return $imagePath;
    }

    public function pathToImageLink($path)
    {
        $baseMediaUrl = Mage::app()->getStore($this->getMagentoProduct()->getStoreId())
                                        ->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, false).
                                        'catalog/product';
        $baseMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();

        $imageLink = str_replace($baseMediaPath, $baseMediaUrl, $path);
        $imageLink = str_replace(DS, '/', $imageLink);

        $imageLink = str_replace('https://', 'http://', $imageLink);

        return str_replace(' ', '%20', $imageLink);
    }

    // #######################################

    public function getTitleResultValue()
    {
        $title = '';
        $src = $this->getTitleSource();

        switch ($src['mode']) {
            case self::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case self::TITLE_MODE_CUSTOM:
                $title = Mage::helper('M2ePro/Module_Renderer_Description')
                    ->parseTemplate($src['template'], $this->getMagentoProduct());
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        if ($this->isCutLongTitles()) {
            $title = $this->cutLongTitles($title);
        }

        return $title;
    }

    public function getSubTitleResultValue()
    {
        $subTitle = '';
        $src = $this->getSubTitleSource();

        if ($src['mode'] == self::SUBTITLE_MODE_CUSTOM) {
            $subTitle = Mage::helper('M2ePro/Module_Renderer_Description')
                ->parseTemplate($src['template'], $this->getMagentoProduct());
            if ($this->isCutLongTitles()) {
                $subTitle = $this->cutLongTitles($subTitle, 55);
            }
        }

        return $subTitle;
    }

    public function getDescriptionResultValue()
    {
        $description = '';
        $src = $this->getDescriptionSource();
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        switch ($src['mode']) {
            case self::DESCRIPTION_MODE_PRODUCT:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;

            case self::DESCRIPTION_MODE_SHORT:
                $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
                $description = $templateProcessor->filter($description);
                break;

            case self::DESCRIPTION_MODE_CUSTOM:
                $description = Mage::helper('M2ePro/Module_Renderer_Description')
                    ->parseTemplate($src['template'], $this->getMagentoProduct());
                $this->addWatermarkForCustomDescription($description);
                break;

            default:
                $description = $this->getMagentoProduct()->getProduct()->getDescription();
                $description = $templateProcessor->filter($description);
                break;
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    private function addWatermarkForCustomDescription(&$description)
    {
        if (strpos($description, 'm2e_watermark') !== false) {
            preg_match_all('/<(img|a) [^>]*\bm2e_watermark[^>]*>/i', $description, $tagsArr);

            $tags = $tagsArr[0];
            $tagsNames = $tagsArr[1];

            $count = count($tags);
            for($i = 0; $i < $count; $i++){
                $dom = new DOMDocument();
                $dom->loadHTML($tags[$i]);
                $tag = $dom->getElementsByTagName($tagsNames[$i])->item(0);

                $newTag = str_replace(' m2e_watermark="1"', '', $tags[$i]);
                if($tagsNames[$i] === 'a') {
                    $newTag = str_replace($tag->getAttribute('href'),
                        $this->addWatermarkIfNeed($tag->getAttribute('href')), $newTag);
                }
                if($tagsNames[$i] === 'img') {
                    $newTag = str_replace($tag->getAttribute('src'),
                        $this->addWatermarkIfNeed($tag->getAttribute('src')), $newTag);
                }
                $description = str_replace($tags[$i], $newTag, $description);
            }
        }
    }

    // #######################################

    public function getCondition()
    {
        $src = $this->getConditionSource();

        if ($src['mode'] == self::CONDITION_MODE_NONE) {
            return 0;
        }

        if ($src['mode'] == self::CONDITION_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getConditionNote()
    {
        $note = '';
        $src = $this->getConditionNoteSource();

        if ($src['mode'] == self::CONDITION_NOTE_MODE_CUSTOM) {
            $note = Mage::helper('M2ePro/Module_Renderer_Description')
                    ->parseTemplate($src['template'], $this->getMagentoProduct());
        }

        return $note;
    }

    // #######################################

    public function getProductDetails()
    {
        return $this->getSettings('product_details');
    }

    public function getProductDetail($type)
    {
        $attribute = $this->getProductDetailAttribute($type);

        if (!$attribute) {
            return NULL;
        }

        return $this->getMagentoProduct()->getAttributeValue($attribute);
    }

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

    // #######################################

    public function getMainImageLink()
    {
        $imageLink = '';

        if ($this->isImageMainModeProduct()) {
            $imageLink = $this->getMagentoProduct()->getImageLink('image');
        }

        if ($this->isImageMainModeAttribute()) {
            $src = $this->getImageMainSource();
            $imageLink = $this->getMagentoProduct()->getImageLink($src['attribute']);
        }

        if (empty($imageLink)) {
            return $imageLink;
        }

        return $this->addWatermarkIfNeed($imageLink);
    }

    public function getImagesForEbay()
    {
        if ($this->isImageMainModeNone()) {
            return array();
        }

        $mainImage = $this->getMainImageLink();

        if ($mainImage == '') {
            $defaultImage = $this->getDefaultImageUrl();
            if (!empty($defaultImage)) {
                return array($defaultImage);
            }

            return array();
        }

        $mainImage = array($mainImage);

        if ($this->isGalleryImagesModeNone()) {
            return $mainImage;
        }

        $galleryImages = array();
        $gallerySource = $this->getGalleryImagesSource();
        $limitGalleryImages = self::GALLERY_IMAGES_COUNT_MAX;

        if ($this->isGalleryImagesModeProduct()) {
            $limitGalleryImages = (int)$gallerySource['limit'];
            $galleryImages = $this->getMagentoProduct()->getGalleryImagesLinks((int)$gallerySource['limit']+1);
        }

        if ($this->isGalleryImagesModeAttribute()) {
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

        foreach ($galleryImages as &$image) {
            $image = $this->addWatermarkIfNeed($image);
        }

        $mainImagePosition = array_search($mainImage[0], $galleryImages);
        if ($mainImagePosition !== false) {
            unset($galleryImages[$mainImagePosition]);
        }

        $galleryImages = array_slice($galleryImages,0,$limitGalleryImages);
        return array_merge($mainImage, $galleryImages);
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
     * @param bool|array $asArrays
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true)
    {
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays
        );

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION,
                                                $asArrays
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
        $neededColumns = array('id');
        $listingsProducts = $this->getAffectedListingsProducts($neededColumns);

        if (!$listingsProducts) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_description');
        return parent::delete();
    }

    // #######################################
}