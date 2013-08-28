<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Template_DescriptionController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/templates')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('Description Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Template/AttributeSetHandler.js')
             ->addJs('M2ePro/Ebay/Template/DescriptionHandler.js');

        $this->_initPopUp();

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/templates/description');
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/adminhtml_template_description/index');
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_Description')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/adminhtml_template_description/index');
        }

        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
        $templateAttributeSetsCollection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('object_id', $id)
                                        ->addFieldToFilter('object_type', $temp);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');

        $model->setData('attribute_sets', $templateAttributeSetsCollection->getColumnValues('attribute_set_id'));

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_description_edit'))
             ->renderLayout();
    }

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_template_description/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        //--------------------
        $data = array();

        $keys = array(
            'title',
            'title_mode',
            'title_template',
            'subtitle_mode',
            'subtitle_template',
            'description_mode',
            'description_template',
            'cut_long_titles',
            'hit_counter',
            'editor_type',
            'image_main_mode',
            'image_main_attribute',
            'use_supersize_images',
            'watermark_mode',
            'gallery_images_mode',
            'gallery_images_limit',
            'gallery_images_attribute',
            'variation_configurable_images'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);

        if ($data['watermark_mode'] == Ess_M2ePro_Model_Ebay_Template_Description::WATERMARK_MODE_YES) {
            if (!is_null($id)) {
                $watermarkSettings = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_Description',$id,NULL,array('template'))
                    ->getSettings('watermark_settings');
            } else {
                $watermarkSettings['position'] = $post['watermark_position'];
                $watermarkSettings['scale'] = $post['watermark_scale'];
                $watermarkSettings['transparent'] = $post['watermark_transparent'];
            }

            $hashChange = false;

            !isset($watermarkSettings['position']) && $watermarkSettings['position'] = -1;
            !isset($watermarkSettings['scale']) && $watermarkSettings['scale'] = -1;
            !isset($watermarkSettings['transparent']) && $watermarkSettings['transparent'] = -1;

            if (!is_null($id) &&
                ($watermarkSettings['position'] != $post['watermark_position'] ||
                $watermarkSettings['scale'] != $post['watermark_scale'] ||
                $watermarkSettings['transparent'] != $post['watermark_transparent'])
            ) {

                $hashChange = true;

                $watermarkSettings['position'] = $post['watermark_position'];
                $watermarkSettings['scale'] = $post['watermark_scale'];
                $watermarkSettings['transparent'] = $post['watermark_transparent'];
            }

            if (!empty($_FILES['watermark_image']['tmp_name'])) {
                $watermarkImage = file_get_contents($_FILES['watermark_image']['tmp_name']);

                $hashChange = true;

                if (!is_null($id)) {
                    $varDir = new Ess_M2ePro_Model_General_VariablesDir(
                        array('child_folder' => 'ebay/template/description/watermarks')
                    );
                    $watermarkPath = $varDir->getPath().$id.'.png';
                    if (is_file($watermarkPath)) {
                        @unlink($watermarkPath);
                    }
                }
            }

            if ($hashChange) {
                $newHash = substr(sha1(microtime().$data['title']), 0, 5);

                if (is_null($id)) {
                    $watermarkSettings['hashes']['current'] = $newHash;
                    $watermarkSettings['hashes']['previous'] = NULL;
                } else {
                    if (isset($watermarkSettings['hashes']['current'])) {
                        $previousHash = $watermarkSettings['hashes']['current'];
                    } else {
                        $previousHash = NULL;
                    }

                    $watermarkSettings['hashes']['previous'] = $previousHash;
                    $watermarkSettings['hashes']['current'] = $newHash;
                }
            }
        }
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Ebay')->getModel('Template_Description');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();

        if (isset($watermarkSettings)) {
            $model->load($id)->setSettings('watermark_settings', $watermarkSettings);
            $model->save();
        }

        if (isset($watermarkImage)) {
            $resource = Mage::getSingleton('core/resource');
            $updateConnection = $resource->getConnection('core_write');
            $tableName = Mage::getResourceModel('M2ePro/Ebay_Template_Description')->getMainTable();
            $updateConnection->update(
                $tableName,
                array('watermark_image' => $watermarkImage),
                array('template_description_id = ?' => (int)$id)
            );
        }
        //--------------------

        // Attribute sets
        //--------------------
        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
        $oldAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                                    ->getCollection()
                                    ->addFieldToFilter('object_type',$temp)
                                    ->addFieldToFilter('object_id',(int)$id)
                                    ->getItems();
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
            $oldAttributeSet->deleteInstance();
        }

        if (!is_array($post['attribute_sets'])) {
            $post['attribute_sets'] = explode(',', $post['attribute_sets']);
        }
        foreach ($post['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION,
                'object_id' => (int)$id,
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################

    public function getAttributesForConfigurableProductAction()
    {
        $attributeSets = $this->getRequest()->getParam('attribute_sets','');

        if ($attributeSets == '') {
            exit(json_encode(array()));
        }

        $attributeSets = explode(',',$attributeSets);

        if (!is_array($attributeSets) || count($attributeSets) <= 0) {
            exit(json_encode(array()));
        }

        $attributes = NULL;
        foreach ($attributeSets as $attributeSetId) {

            $attributesTemp = $this->getConfigurableAttributesByAttributeSetId($attributeSetId);

            if (is_null($attributes)) {
                $attributes = $attributesTemp;
                continue;
            }

            $intersectAttributes = array();
            foreach ($attributesTemp as $attributeTemp) {
                $findValue = false;
                foreach ($attributes as $attribute) {
                    if ($attributeTemp['value'] == $attribute['value'] &&
                        $attributeTemp['title'] == $attribute['title']) {
                        $findValue = true;
                        break;
                    }
                }
                if ($findValue) {
                    $intersectAttributes[] = $attributeTemp;
                }
            }

            $attributes = $intersectAttributes;
        }

        exit(json_encode($attributes));
    }

    private function getConfigurableAttributesByAttributeSetId($attributeSetId)
    {
        $attributeSetId = (int)$attributeSetId;

        $product = Mage::getModel('catalog/product');
        $product->setAttributeSetId($attributeSetId);
        $product->setTypeId('configurable');
        $product->setData('_edit_mode', true);

        $attributes = $product->getTypeInstance()->setStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID)
                              ->getSetAttributes($product);

        $result = array();
        foreach ($attributes as $attribute) {
            if ($product->getTypeInstance()->setStoreFilter(Mage_Core_Model_App::ADMIN_STORE_ID)
                        ->canUseAttribute($attribute, $product)) {
                $result[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'title' => $attribute->getFrontend()->getLabel()
                );
            }
        }

        return $result;
    }

    //#############################################

    public function previewAction()
    {
        $body = '';
        $errorTxt = false;

        if ((int)$this->getRequest()->getPost('show',0) == 1) {

            // form sended
            //--------------------------------
            $templateData = $this->_getSession()->getPreviewFormData();
            if (!$id = $this->getRequest()->getPost('id',NULL)) {
                $id = $this->_getRandomProduct($templateData['attribute_sets']);
            }
            //--------------------------------

            // get attributes sets title
            //--------------------------------
            $attributeSets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->addFieldToFilter('attribute_set_id', $templateData['attribute_sets'])
                ->toArray();
            $attributeSetsTitles = '';
            foreach ($attributeSets['items'] as $attributeSet) {
                $attributeSetsTitles != '' && $attributeSetsTitles .= ',';
                $attributeSetsTitles .= $attributeSet['attribute_set_name'];
            }
            //--------------------------------

            if (!$id) {
                $errorTxt = Mage::helper('M2ePro')->__('There are no any products for "%s" attribute set(s).',
                                                         Mage::helper('M2ePro')->__($attributeSetsTitles));
            } else {

                $id = (int)$id;
                $product = Mage::getModel('catalog/product')->load($id);

                if (!$product->getId()) {
                    $errorTxt = Mage::helper('M2ePro')->__('Product #%s does not exist', $id);
                } elseif (!in_array($product->getData('attribute_set_id'),$templateData['attribute_sets'])) {
                    $errorTxt = Mage::helper('M2ePro')->__('Product #%s does not belong to "%s" attribute set(s).',
                                                           $id, Mage::helper('M2ePro')->__($attributeSetsTitles));
                } else {

                    if (Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_CUSTOM == $templateData['title_mode']) {
                        $title = Mage::getModel('M2ePro/Template_Description_Parser')->parseTemplate(
                            $templateData['title_template'], $product
                        );
                    } else {
                        $title = $product->getData('name');
                    }

                    $temp = Ess_M2ePro_Model_Ebay_Template_Description::SUBTITLE_MODE_CUSTOM;
                    if ($temp == $templateData['subtitle_mode']) {
                        $subTitle =  Mage::getModel('M2ePro/Template_Description_Parser')->parseTemplate(
                            $templateData['subtitle_template'], $product
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
                        $description = Mage::getModel('M2ePro/Template_Description_Parser')->parseTemplate(
                            $templateData['description_template'], $product
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
            $templateData = $this->getRequest()->getPost();
            if (!is_array($templateData['attribute_sets'])) {
                $templateData['attribute_sets'] = explode(',', $templateData['attribute_sets']);
            }
            $this->_getSession()->setPreviewFormData($templateData);

        }

        $this->loadLayout();

        $headBlock = $this->getLayout()->getBlock('head');
        $generalBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_general');
        $previewFormBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_description_preview_form', '', array('error_txt' => $errorTxt)
        );

        $html = $headBlock->toHtml() . $generalBlock->toHtml() . $previewFormBlock->toHtml() . $body;
        $this->getResponse()->setBody($html);
    }

    private function _getRandomProduct($attributeSets)
    {
        $products = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addFieldToFilter('attribute_set_id', $attributeSets)
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