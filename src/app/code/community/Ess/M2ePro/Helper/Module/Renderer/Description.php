<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Renderer_Description extends Mage_Core_Helper_Abstract
{
    const IMAGES_MODE_DEFAULT    = 0;
    const IMAGES_MODE_NEW_WINDOW = 1;
    const IMAGES_MODE_GALLERY    = 2;

    const IMAGES_QTY_ALL = 0;

    const LAYOUT_MODE_ROW    = 'row';
    const LAYOUT_MODE_COLUMN = 'column';

    // ########################################

    public function parseTemplate($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $text = $this->insertAttributes($text, $magentoProduct);
        $text = $this->insertImages($text, $magentoProduct);
        $text = $this->insertMediaGalleries($text, $magentoProduct);

        $design = Mage::getDesign();

        $oldArea = $design->getArea();
        $design->setArea('adminhtml');

        //  the CMS static block replacement i.e. {{media url=’image.jpg’}}
        $filter = new Mage_Core_Model_Email_Template_Filter();
        $filter->setVariables(array('product'=>$magentoProduct->getProduct()));

        $text = $filter->filter($text);

        $design->setArea($oldArea);

        return $text;
    }

    // ########################################

    private function insertAttributes($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        preg_match_all("/#([a-zA-Z_0-9]+?)#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $search = array();
        $replace = array();
        foreach ($matches[1] as $attributeCode) {

            $value = $magentoProduct->getAttributeValue($attributeCode);

            if (!is_array($value) && $value != '') {
                if ($attributeCode == 'description') {
                    $value = $this->normalizeDescription($value);
                } elseif ($attributeCode == 'weight') {
                    $value = (float)$value;
                } elseif (in_array($attributeCode, array('price', 'special_price'))) {
                    $value = Mage::helper('M2ePro')->parsePrice($value, false);
                    $storeId = $magentoProduct->getProduct()->getStoreId();
                    $store = Mage::app()->getStore($storeId);
                    $value = $store->formatPrice($value, false);
                }
                $search[] = '#' . $attributeCode . '#';
                $replace[] = $value;
            } else {
                $search[] = '#' . $attributeCode . '#';
                $replace[] = '';
            }
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }

    private function insertImages($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        preg_match_all("/#image\[(.*?)\]#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $imageLink = $magentoProduct->getImageLink('image');

        if ($imageLink == '') {
            $text = str_replace($matches[0], '', $text);
            return $text;
        }

        $blockObj = Mage::getSingleton('core/layout')->createBlock(
            'M2ePro/adminhtml_renderer_description_image'
        );

        $search = array();
        $replace = array();
        foreach ($matches[0] as $key => $match) {

            $tempImageAttributes = explode(',', $matches[1][$key]);
            $realImageAttributes = array();
            for ($i=0;$i<5;$i++) {
                if (!isset($tempImageAttributes[$i])) {
                    $realImageAttributes[$i] = 0;
                } else {
                    $realImageAttributes[$i] = (int)$tempImageAttributes[$i];
                }
            }

            $data = array(
                'width' => $realImageAttributes[0],
                'height' => $realImageAttributes[1],
                'margin' => $realImageAttributes[2],
                'linked_mode' => $realImageAttributes[3],
                'watermark' => $realImageAttributes[4],
                'src' => $imageLink
            );
            $search[] = $match;
            $replace[] = preg_replace('/\s{2,}/', '', $blockObj->addData($data)->toHtml());
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }

    private function insertMediaGalleries($text, Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        preg_match_all("/#media_gallery\[(.*?)\]#/", $text, $matches);

        if (!count($matches[0])) {
            return $text;
        }

        $blockObj = Mage::getSingleton('core/layout')->createBlock(
            'M2ePro/adminhtml_renderer_description_gallery'
        );

        $search = array();
        $replace = array();
        $attributeCounter = 0;
        foreach ($matches[0] as $key => $match) {
            $tempMediaGalleryAttributes = explode(',', $matches[1][$key]);
            $realMediaGalleryAttributes = array();
            for ($i=0;$i<8;$i++) {
                if (!isset($tempMediaGalleryAttributes[$i])) {
                    $realMediaGalleryAttributes[$i] = '';
                } else {
                    $realMediaGalleryAttributes[$i] = $tempMediaGalleryAttributes[$i];
                }
            }

            $imagesQty = (int)$realMediaGalleryAttributes[5];
            if ($imagesQty == self::IMAGES_QTY_ALL) {
                $imagesQty = $realMediaGalleryAttributes[3] == self::IMAGES_MODE_GALLERY ? 100 : 25;
            }

            $galleryImagesLinks = $magentoProduct->getGalleryImagesLinks($imagesQty);
            if (!count($galleryImagesLinks)) {
                $search = $matches[0];
                $replace = '';
                break;
            }

            if (!in_array($realMediaGalleryAttributes[4], array(self::LAYOUT_MODE_ROW, self::LAYOUT_MODE_COLUMN))) {
                $realMediaGalleryAttributes[4] = self::LAYOUT_MODE_ROW;
            }

            $data = array(
                'width'        => (int)$realMediaGalleryAttributes[0],
                'height'       => (int)$realMediaGalleryAttributes[1],
                'margin'       => (int)$realMediaGalleryAttributes[2],
                'linked_mode'  => (int)$realMediaGalleryAttributes[3],
                'layout'       => $realMediaGalleryAttributes[4],
                'gallery_hint' => trim($realMediaGalleryAttributes[6], '"'),
                'watermark' => (int)$realMediaGalleryAttributes[7],
                'images_count' => count($galleryImagesLinks),
                'image_counter' => 0
            );

            $tempHtml = '';
            $attributeCounter++;

            foreach ($galleryImagesLinks as $imageLink) {
                $data['image_counter']++;
                $data['attribute_counter'] = $attributeCounter;
                $data['src'] = $imageLink;
                $tempHtml .= $blockObj->addData($data)->toHtml();
            }
            $search[] = $match;
            $replace[] = preg_replace('/\s{2,}/', '', $tempHtml);
        }

        $text = str_replace($search, $replace, $text);

        return $text;
    }

    //----------------------------------------

    private function normalizeDescription($str)
    {
        // Trim whitespace
        if (($str = trim($str)) === '') {
            return '';
        }

        // Standardize newlines
        $str = str_replace(array("\r\n", "\r"), "\n", $str);

        // Trim whitespace on each line
        $str = preg_replace('~^[ \t]+~m', '', $str);
        $str = preg_replace('~[ \t]+$~m', '', $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found = (strpos($str, '<') !== false)) {
            // Elements that should not be surrounded by p tags
            $no_p  = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|';
            $no_p .= 'c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';

            // Put at least two linebreaks before and after $no_p elements
            $str = preg_replace('~^<' . $no_p . '[^>]*+>~im', "\n$0", $str);
            $str = preg_replace('~</' . $no_p . '\s*+>$~im', "$0\n", $str);
        }

        // Do the <p> magic!
        $str = '<p>' . trim($str) . '</p>';
        $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

        // The following regexes only need to be executed if the string contains html
        if ($html_found !== false) {
            // Remove p tags around $no_p elements
            $str = preg_replace('~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $str);
            $str = preg_replace('~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $str);
        }

        // Convert single linebreaks to <br />
        $br = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/renderer/description/','convert_linebreaks');
        if (is_null($br) || (bool)(int)$br === true) {
            $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
        }

        return $str;
    }

    // ########################################
}