<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Template_DescriptionController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    public function previewAction()
    {
        $title = NULL;
        $description = NULL;
        $errorMessage = NULL;

        if (!(int)$this->getRequest()->getPost('show',0)) {
            $templateData = $this->getRequest()->getPost('description');
            $this->_getSession()->setTemplateData($templateData);
        } else {

            $productEntities = $this->getProductsEntities();

            if (is_null($productEntities['magento_product'])) {
                $errorMessage = Mage::helper('M2ePro')->__('This product ID does not exist');
            } else {

                $templateData = $this->_getSession()->getTemplateData();

                $title = $productEntities['magento_product']->getProduct()->getData('name');
                $description = $this->getDescription($templateData['description_mode'],
                                                     $templateData['description_template'],
                                                     $productEntities['magento_product'],
                                                     $productEntities['listing_product']);

                if($templateData['watermark_mode']) {
                    $this->addWatermarkInfoToDescription($description);
                }
            }
        }
        $this->printOutput($title, $description, $errorMessage);
    }

    private function addWatermarkInfoToDescription(&$description)
    {
        if (strpos($description, 'm2e_watermark') !== false) {
            preg_match_all('/<img [^>]*\bm2e_watermark[^>]*>/i', $description, $tagsArr);

            $count = count($tagsArr[0]);
            for($i = 0; $i < $count; $i++){
                $dom = new DOMDocument();
                $dom->loadHTML($tagsArr[0][$i]);
                $tag = $dom->getElementsByTagName('img')->item(0);

                $newTag = str_replace(' m2e_watermark="1"', '', $tagsArr[0][$i]);
                $newTag = '<div class="description-preview-watermark-info">'.$newTag;

                if($tag->getAttribute('width') == '' || $tag->getAttribute('width') > 100) {
                    $newTag = $newTag.'<p>Watermark will be applied to this picture.</p></div>';
                } else {
                    $newTag = $newTag.'<p>Watermark.</p></div>';
                }
                $description = str_replace($tagsArr[0][$i], $newTag, $description);
            }
        }
    }

    private function printOutput($title = NULL, $description = NULL, $errorMessage = NULL)
    {
        $this->loadLayout();

        $headBlock = $this->getLayout()->getBlock('head');
        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_general');

        $productId = $this->getRequest()->getPost('id',NULL);

        $previewFormBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_description_preview_form', '',
            array('error_message' => $errorMessage, 'product_id' => $productId)
        );

        $previewBodyBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_description_preview_body', '',
            array('title' => $title, 'description' => $description)
        );

        $html = $headBlock->toHtml() . $generalBlock->toHtml() .
                $previewFormBlock->toHtml() . $previewBodyBlock->toHtml();

        $this->getResponse()->setBody($html);
    }

    // --------------------------------------------

    public function saveWatermarkImageAction()
    {
        $templateData = $this->getRequest()->getPost('description');

        if (is_null($templateData['id']) || empty($_FILES['watermark_image']['tmp_name'])) {
            return NULL;
        }

        $varDir = new Ess_M2ePro_Model_VariablesDir(
            array('child_folder' => 'ebay/template/description/watermarks')
        );

        $watermarkPath = $varDir->getPath().(int)$templateData['id'].'.png';
        if (is_file($watermarkPath)) {
            @unlink($watermarkPath);
        }

        $template = Mage::getModel('M2ePro/Ebay_Template_Description')->load((int)$templateData['id']);
        $template->updateWatermarkHashes();

        $data = array(
            'watermark_image' => file_get_contents($_FILES['watermark_image']['tmp_name'])
        );

        $template->addData($data);
        $template->save();
    }

    //#############################################

    private function getProductsEntities()
    {
        if ($productId = $this->getRequest()->getPost('id',NULL)) {
            return array(
                'magento_product' => $this->getMagentoProduct($productId),
                'listing_product' => $this->getListingProduct($productId)
            );
        }

        $result = array(
            'magento_product' => NULL,
            'listing_product' => NULL
        );

        $listingProduct = $this->getRandomListingProduct();

        if (!is_null($listingProduct)) {
            $result['magento_product'] = $listingProduct->getMagentoProduct();
            $result['listing_product'] = $listingProduct;
            return $result;
        }

        $magentoProduct = $this->getRandomMagentoProduct();

        if (!is_null($magentoProduct)) {
            $result['magento_product'] = $magentoProduct;
        }

        return $result;
    }

    // --------------------------------------------

    private function getRandomMagentoProduct()
    {
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setPageSize(100)
            ->getItems();

        if (count($products) <= 0) {
            return NULL;
        }

        shuffle($products);

        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProduct(array_shift($products));

        return $magentoProduct;
    }

    private function getRandomListingProduct()
    {
        $listingProducts = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->setPageSize(100)
            ->getItems();

        if (count($listingProducts) <= 0) {
            return NULL;
        }

        shuffle($listingProducts);

        return array_shift($listingProducts);
    }

    // --------------------------------------------

    /**
     * @param $productId
     * @return Ess_M2ePro_Model_Magento_Product|null
     */
    private function getMagentoProduct($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);

        if (is_null($product->getId())) {
            return NULL;
        }

        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProduct($product);

        return $magentoProduct;
    }

    private function getListingProduct($productId)
    {
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem();

        if (is_null($listingProduct->getId())) {
            return NULL;
        }

        return $listingProduct;
    }

    // --------------------------------------------

    private function getDescription($descriptionMode, $customDescription,
                                    Ess_M2ePro_Model_Magento_Product $magentoProduct,
                                    Ess_M2ePro_Model_Listing_Product $listingProduct = NULL)
    {
        $description = '';

        $descriptionModeProduct = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_PRODUCT;
        $descriptionModeShort = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_SHORT;
        $descriptionModeCustom = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM;

        if ($descriptionModeProduct == $descriptionMode) {
            $description = $magentoProduct->getProduct()->getDescription();
        } elseif ($descriptionModeShort == $descriptionMode){
            $description = $magentoProduct->getProduct()->getShortDescription();
        } elseif ($descriptionModeCustom == $descriptionMode){
            $description = $customDescription;
        }

        if (empty($description)) {
            return $description;
        }

        $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
        $description = $renderer->parseTemplate($description, $magentoProduct);

        if (!is_null($listingProduct)){
            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Description_Renderer $renderer */
            $renderer = Mage::getSingleton('M2ePro/Ebay_Listing_Product_Description_Renderer');
            $renderer->setListingProduct($listingProduct->getChildObject());
            $description = $renderer->parseTemplate($description);
        }

        return $description;
    }

    //#############################################
}