<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Translation_Product_Add_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Translation_Responser
{
    // ########################################

    protected $listingsProducts = array();

    protected $failedListingsProducts = array();
    protected $succeededListingsProducts = array();

    private $descriptionTemplatesIds = array();

    // ########################################

    public function __construct(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::__construct($processingRequest);

        foreach ($this->params['products'] as $listingProductId => $listingProductData) {
            try {
                $this->listingsProducts[] = Mage::helper('M2ePro/Component_Ebay')
                    ->getObject('Listing_Product',(int)$listingProductId);
            } catch (Exception $exception) {}
        }
    }

    protected function unsetLocks($fail = false, $message = NULL)
    {
        $tempListings = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->deleteObjectLocks(NULL,$this->hash);
            $listingProduct->deleteObjectLocks('in_action',$this->hash);
            $listingProduct->deleteObjectLocks('translation_action',$this->hash);

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->deleteObjectLocks(NULL,$this->hash);
            $listingProduct->getListing()->deleteObjectLocks('products_in_action',$this->hash);
            $listingProduct->getListing()->deleteObjectLocks('products_translation_action',$this->hash);

            $tempListings[$listingProduct->getListingId()] = true;
        }

        $this->getAccount()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getAccount()->deleteObjectLocks('products_translation_action',$this->hash);

        $this->getMarketplace()->deleteObjectLocks('products_in_action',$this->hash);
        $this->getMarketplace()->deleteObjectLocks('products_translation_action',$this->hash);

        if ($fail) {

            foreach ($this->listingsProducts as $listingProduct) {

                $listingProduct->getChildObject()->setData(
                    'translation_status', Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING
                )->save();

                $this->addListingsProductsLogsMessage($listingProduct,$message,
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
            }
        }
    }

    // ########################################

    protected function addListingsProductsLogsMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                      $text, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                                      $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        $action = Ess_M2ePro_Model_Listing_Log::ACTION_TRANSLATE_PRODUCT;

        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
        } else if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
        } else {
            $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
        }

        $logModel = Mage::getModel('M2ePro/Listing_Log');
        $logModel->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $logModel->addProductMessage($listingProduct->getListingId() ,
                                     $listingProduct->getProductId() ,
                                     $listingProduct->getId() ,
                                     $initiator ,
                                     $this->getLogsActionId() ,
                                     $action , $text, $type , $priority);
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        $failedListingsProductsIds = array();

        // Check global messages
        //----------------------
        $globalMessages = $this->messages;

        foreach ($this->listingsProducts as $listingProduct) {

            $hasError = false;
            foreach ($globalMessages as $message) {

                $type = $this->getTypeByServerMessage($message);
                $priority = $this->getPriorityByServerMessage($message);
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                !$hasError && $hasError = $this->getHasErrorByServerMessage($message);

                $this->addListingsProductsLogsMessage($listingProduct,$text,$type,$priority);

                if (strpos($text, 'code:64') !== false) {

                    preg_match("/amount_due\:(.*?)\s*,\s*currency\:(.*?)\s*\)/i", $text, $matches);

                    $additionalData = $listingProduct->getAdditionalData();
                    $additionalData['translation_service']['payment'] = array(
                        'amount_due' => $matches[1],
                        'currency'   => $matches[2],
                    );

                    $listingProduct->setData('additional_data', json_encode($additionalData))->save();
                    $listingProduct->getChildObject()->setData(
                        'translation_status',
                        Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_PENDING_PAYMENT_REQUIRED
                    )->save();
                }
            }

            if ($hasError && !in_array($listingProduct->getId(),$failedListingsProductsIds)) {
                $this->failedListingsProducts[] = $listingProduct;
                $failedListingsProductsIds[] = $listingProduct->getId();
            }
        }

        //----------------------

        foreach ($this->listingsProducts as $listingProduct) {

            if (in_array($listingProduct->getId(),$failedListingsProductsIds)) {
                continue;
            }

            $this->succeededListingsProducts[] = $listingProduct;

            foreach ($response['products'] as $responseProduct) {
               if ($responseProduct['sku'] == $this->params['products'][$listingProduct->getId()]['sku']) {
                    $this->updateProduct($listingProduct, $responseProduct);
                    break;
                }
            }

            // M2ePro_TRANSLATIONS
            // 'Product has been successfully translated.',
            $this->addListingsProductsLogsMessage($listingProduct, 'Product has been successfully translated.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    protected function updateProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, array $response)
    {
        $productData = array();
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();
        $oldDescriptionTemplateId = $descriptionTemplate->getId();

        if (!isset($this->descriptionTemplatesIds[$oldDescriptionTemplateId]) && (
            trim($descriptionTemplate->getData('title_template'))       != '#ebay_translated_title#'    ||
            trim($descriptionTemplate->getData('subtitle_template'))    != '#ebay_translated_subtitle#' ||
            trim($descriptionTemplate->getData('description_template')) != '#ebay_translated_description#')) {

            $this->checkAndCreateMagentoAttributes(array(
                'ebay_translated_title'    => 'Ebay Translated Title',
                'ebay_translated_subtitle' => 'Ebay Translated Subtitle',
            ), 'text');

            $this->checkAndCreateMagentoAttributes(array(
                'ebay_translated_description' => 'Ebay Translated Description',
            ), 'textarea');

            $this->checkAndCreateMagentoProductAttributes($listingProduct->getMagentoProduct(), array(
                'ebay_translated_title',
                'ebay_translated_subtitle',
                'ebay_translated_description'
            ));

            $data = $descriptionTemplate->getDataSnapshot();
            unset($data['id'], $data['update_date'], $data['create_date']);

            $data['title']                = $data['title']
                .Mage::helper('M2ePro')->__(' (Changed because Translation Service applied.)');
            $data['title_mode']           = Ess_M2ePro_Model_Ebay_Template_Description::TITLE_MODE_CUSTOM;
            $data['title_template']       = '#ebay_translated_title#';
            $data['subtitle_mode']        = Ess_M2ePro_Model_Ebay_Template_Description::SUBTITLE_MODE_CUSTOM;
            $data['subtitle_template']    = '#ebay_translated_subtitle#';
            $data['description_mode']     = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM;
            $data['description_template'] = '#ebay_translated_description#';
            $data['is_custom_template']   = 1;

            $newDescriptionTemplate = Mage::getModel('M2ePro/Ebay_Template_Manager')
                ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION)
                ->getTemplateBuilder()
                ->build($data);
            $this->descriptionTemplatesIds[$oldDescriptionTemplateId] = $newDescriptionTemplate->getId();
        }

        if (isset($this->descriptionTemplatesIds[$oldDescriptionTemplateId])) {
            $productData['template_description_custom_id'] = $this->descriptionTemplatesIds[$oldDescriptionTemplateId];
            $productData['template_description_mode']      = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
        }

        $listingProduct->getMagentoProduct()
                       ->setAttributeValue('ebay_translated_title',       $response['title'])
                       ->setAttributeValue('ebay_translated_subtitle',    $response['subtitle'])
                       ->setAttributeValue('ebay_translated_description', $response['description']);
        //------------------------------

        $categoryPath = !is_null($response['category']['primary_id'])
            ? Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath((int)$response['category']['primary_id'],
                                                                            $this->params['marketplace_id'])
            : '';

        $response['category']['path'] = $categoryPath;

        if ($categoryPath) {
            $data = Mage::getModel('M2ePro/Ebay_Template_Category')->getDefaultSettings();
            $data['category_main_id']   = (int)$response['category']['primary_id'];
            $data['category_main_path'] = $categoryPath;
            $data['marketplace_id']     = $this->params['marketplace_id'];
            $data['specifics']          = $this->getSpecificsData($response['item_specifics']);

            $productData['template_category_id'] =
                Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($data)->getId();
        } else {
            $response['category']['primary_id'] = null;
        }

        $additionalData = $listingProduct->getAdditionalData();
        $additionalData['translation_service']['to'] = array_merge(
            $additionalData['translation_service']['to'], $response
        );
        $productData['additional_data'] = json_encode($additionalData);

        $listingProduct->addData($productData)->save();
        $listingProduct->getChildObject()->addData(array(
            'translation_status' => Ess_M2ePro_Model_Ebay_Listing_Product::TRANSLATION_STATUS_TRANSLATED,
            'translated_date'    => Mage::helper('M2ePro')->getCurrentGmtDate()
        ))->save();
    }

    // ########################################

    protected function getHasErrorByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                return false;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                return true;
        }
    }

    protected function getTypeByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                return Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                break;
        }
    }

    protected function getPriorityByServerMessage($message)
    {
        switch ($message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_KEY]) {

            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_WARNING:
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_SUCCESS:
                return Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_NOTICE:
                return Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW;
                break;
            case Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TYPE_ERROR:
            default:
                return Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                break;
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getObjectByParam('Marketplace','marketplace_id');
    }

    //---------------------------------------

    protected function getStatusChanger()
    {
        return (int)$this->params['status_changer'];
    }

    protected function getLogsActionId()
    {
        return (int)$this->params['logs_action_id'];
    }

    // ########################################

    private function checkAndCreateMagentoAttributes($attributes, $frontendInput = 'text')
    {
        foreach ($attributes as $code => $label) {
            if (!Mage::helper('M2ePro/Magento_Attribute')->getByCode($code)) {
                Mage::helper('M2ePro/Magento_Attribute')->create($code, array($label), $frontendInput, 0);
            }
        }
        return true;
    }

    private function checkAndCreateMagentoProductAttributes($magentoProduct, $attributes)
    {
        $helpAttribute    = Mage::helper('M2ePro/Magento_Attribute');
        $helpAttributeSet = Mage::helper('M2ePro/Magento_AttributeSet');

        $attributeSetId = $magentoProduct->getProduct()->getAttributeSetId();
        $attributesInSet = $helpAttribute->getByAttributeSet($attributeSetId);

        foreach ($attributes as $code) {
            if (!$helpAttribute->isExistInAttributesArray($code, $attributesInSet)) {
                empty($attributeGroupId) &&
                    $attributeGroupId = $this->getAttributeGroupId($attributeSetId);

                $attributeIds = $helpAttribute->getByCode($code, Ess_M2ePro_Helper_Magento_Attribute::RETURN_TYPE_IDS);
                !empty($attributeIds) &&
                    $helpAttributeSet->attributeAdd($attributeIds[0], $attributeSetId, $attributeGroupId);
            }
        }

        return true;
    }

    //---------------------------------------

    private function getAttributeGroupId($attributeSetId, $groupName = 'Ebay')
    {
        $attributeGroupModel = Mage::getModel('eav/entity_attribute_group')
            ->setAttributeGroupName($groupName)
            ->setAttributeSetId($attributeSetId);

        if (!$attributeGroupModel->itemExists()) {
            try {
                $attributeGroupModel->save();
            } catch (Exception $e) {
                return false;
            }
        } else {
            $attributeGroupModel = Mage::getModel('eav/entity_attribute_group')
                ->getResourceCollection()
                ->addFieldToFilter('attribute_group_name', $groupName)
                ->addFieldToFilter('attribute_set_id', $attributeSetId)
                ->getFirstItem();
        }

        return $attributeGroupModel->getId();
    }

    //---------------------------------------

    private function getSpecificsData($responseSpecifics)
    {
        $data = array();
        foreach ($responseSpecifics as $responseSpecific) {
            $data[] = array(
                'mode'                  => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS,
                'mode_relation_id'      => 0,
                'attribute_id'          => $responseSpecific['name'],
                'attribute_title'       => $responseSpecific['name'],
                'value_mode'            => Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE,
                'value_ebay_recommended'=> json_encode(array()),
                'value_custom_value'    => join(",", $responseSpecific['value']),
                'value_custom_attribute'=> ''
            );
        }

        return $data;
    }

    // ########################################
}