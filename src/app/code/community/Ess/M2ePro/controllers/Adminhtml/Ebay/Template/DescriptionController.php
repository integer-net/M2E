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
        $body = '';
        $errorTxt = false;
        $productId = NULL;

        if ((int)$this->getRequest()->getPost('show',0) == 1) {

            // form sended
            //--------------------------------
            $templateData = $this->_getSession()->getPreviewFormData();
            if (!$productId = $this->getRequest()->getPost('id',NULL)) {
                $productId = $this->_getRandomProduct();
            }
            //--------------------------------

            if (!$productId) {
                $errorTxt = Mage::helper('M2ePro')->__('There are no any products in your magento store.');
            } else {
                $productId = (int)$productId;
                $product = Mage::getModel('catalog/product')->load($productId);

                if (!$product->getId()) {
                    $errorTxt = Mage::helper('M2ePro')->__('Product #%s does not exist', $productId);
                } else {

                    if (Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_CUSTOM == $templateData['title_mode']) {

                        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($product);

                        $title = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                            $templateData['title_template'], $magentoProduct
                        );

                    } else {
                        $title = $product->getData('name');
                    }

                    $temp = Ess_M2ePro_Model_Ebay_Template_Description::SUBTITLE_MODE_CUSTOM;
                    if ($temp == $templateData['subtitle_mode']) {

                        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($product);

                        $subTitle =  Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                            $templateData['subtitle_template'], $magentoProduct
                        );

                    } else {
                        $subTitle = '';
                    }

                    $cutLongTitles = !empty($templateData['cut_long_titles']);
                    if ($cutLongTitles) {
                        $title = Mage::getModel('M2ePro/Ebay_Template_Description')->cutLongTitles($title);
                        $subTitle = Mage::getModel('M2ePro/Ebay_Template_Description')->cutLongTitles($subTitle, 55);
                    }

                    $description = $product->getDescription();
                    $temp1 = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_SHORT;
                    $temp2 = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM;
                    if ($temp1 == $templateData['description_mode']) {
                        $description = $product->getShortDescription();
                    } elseif ($temp2 == $templateData['description_mode']) {

                        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
                        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($product);

                        $description = Mage::helper('M2ePro/Module_Renderer_Description')->parseTemplate(
                            $templateData['description_template'], $magentoProduct
                        );
                    }

                    $body = $this->getLayout()->createBlock(
                        'M2ePro/adminhtml_ebay_template_description_preview_body', '',
                        array(
                            'title' => $title,
                            'subtitle' => $subTitle,
                            'description' => $description
                        )
                    )->toHtml();
                }
            }
        } else {
            // first load
            $this->_getSession()->setPreviewFormData($this->getRequest()->getPost('description'));
        }

        $this->loadLayout();

        $headBlock = $this->getLayout()->getBlock('head');
        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_general');
        $previewFormBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_description_preview_form', '',
            array('error_txt' => $errorTxt, 'product_id' => $productId)
        );

        $html = $headBlock->toHtml() . $generalBlock->toHtml() . $previewFormBlock->toHtml() . $body;
        $this->getResponse()->setBody($html);
    }

    public function saveWatermarkImageAction()
    {
        $descriptionData = $this->getRequest()->getPost('description');

        if (is_null($descriptionData['id']) || empty($_FILES['watermark_image']['tmp_name'])) {
            return NULL;
        }

        $varDir = new Ess_M2ePro_Model_General_VariablesDir(
            array('child_folder' => 'ebay/template/description/watermarks')
        );

        $watermarkPath = $varDir->getPath().(int)$descriptionData['id'].'.png';
        if (is_file($watermarkPath)) {
            @unlink($watermarkPath);
        }

        $template = Mage::getModel('M2ePro/Ebay_Template_Description')->load((int)$descriptionData['id']);
        $template->updateWatermarkHashes();

        $data = array(
            'watermark_image' => file_get_contents($_FILES['watermark_image']['tmp_name'])
        );

        $template->addData($data);
        $template->save();
    }

    private function _getRandomProduct()
    {
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->setPage(1,4)
            ->getItems();

        if (count($products) <= 0) {
            return NULL;
        }

        shuffle($products);
        $product = array_shift($products);

        return (int)$product->getId();
    }

    //#############################################
}