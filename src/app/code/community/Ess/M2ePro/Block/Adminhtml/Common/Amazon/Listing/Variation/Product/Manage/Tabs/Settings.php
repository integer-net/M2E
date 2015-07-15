<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_Manage_Tabs_Settings
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{

    const MESSAGE_TYPE_ERROR = 'error';
    const MESSAGE_TYPE_WARNING = 'warning';

    protected $warningsCalculated = false;

    protected $channelThemes = null;
    protected $childListingProducts = null;
    protected $currentProductVariations = null;
    protected $productVariationsTree = array();
    protected $channelVariationsTree = array();

    //------------------------------

    protected $listingProductId;

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    //------------------------------

    protected $messages = array();
    /**
     * @param array $message
     */
    public function addMessage($message, $type = self::MESSAGE_TYPE_ERROR)
    {
        $this->messages[] = array(
            'type' => $type,
            'msg' => $message
        );
    }
    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }
    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    public function getMessagesType()
    {
        $type = self::MESSAGE_TYPE_WARNING;
        foreach ($this->messages as $message) {
            if($message['type'] === self::MESSAGE_TYPE_ERROR)     {
                $type = $message['type'];
                break;
            }
        }

        return $type;
    }

    //------------------------------
    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct;

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if(empty($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //------------------------------

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $matcherAttribute */
    protected $matcherAttributes;

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute
     */
    public function getMatcherAttributes()
    {
        if(empty($this->matcherAttributes)) {
            $this->matcherAttributes = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
            $this->matcherAttributes->setMarketplaceId($this->getListingProduct()->getListing()->getMarketplaceId());
            $this->matcherAttributes->setMagentoProduct($this->getListingProduct()->getMagentoProduct());
            $this->matcherAttributes->setDestinationAttributes($this->getDestinationAttributes());
        }

        return $this->matcherAttributes;
    }

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/variation/product/manage/tabs/settings.phtml');
    }

    //------------------------------

    public function getWarnings()
    {
        $warnings = '';
        foreach ($this->getMessages() as $message) {
            $warnings .= <<<HTML
<li class="{$message['type']}-msg">
    <ul>
        <li>{$message['msg']}</li>
    </ul>
</li>
HTML;
        }
        return $warnings;
    }

    public function calculateWarnings()
    {
        if(!$this->warningsCalculated) {

            $this->warningsCalculated = true;

            if (!$this->hasGeneralId() && $this->isGeneralIdOwner()) {
                if(!$this->hasChannelTheme() || !$this->hasMatchedAttributes()) {
                    $this->addMessage(
                        Mage::helper('M2ePro')
                            ->__('Creation of New Parent-Child Product is impossible because Variation Theme
                                  or correspondence between Magento Product Attributes and Amazon Product Attributes
                                  was not set. Please, specify a Variation Theme or correspondence between
                                  Variation Attributes.'),
                        self::MESSAGE_TYPE_ERROR
                    );
                }
            } elseif ($this->hasGeneralId()) {
                if (!$this->hasMatchedAttributes()) {
                    $this->addMessage(
                        Mage::helper('M2ePro')->__(
                            'Selling of existing Child Products on Amazon is impossible because correspondence
                             between Magento Product Attributes and Amazon Product Attributes was not set.
                             Please, specify correspondence between Variation Attributes.'
                        ),
                        self::MESSAGE_TYPE_ERROR
                    );
                }
                if ($this->isGeneralIdOwner() && !$this->hasChannelTheme()) {
                    $this->addMessage(
                        Mage::helper('M2ePro')->__(
                            'Creation of New Amazon Child Products feature is temporary unavailable because
                             Variation Theme was not set. Please, specify Variation Theme.'
                        ),
                        self::MESSAGE_TYPE_WARNING
                    );
                }
            }

        }
    }

    //------------------------------

    protected function _beforeToHtml()
    {
        $this->calculateWarnings();

        return parent::_beforeToHtml();
    }

    // ###########################################

    public function isInAction()
    {
        $lockedObjects = $this->getListingProduct()->getObjectLocks();
        return !empty($lockedObjects);
    }

    // -------------------------------------------

    public function getProductAttributes()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getProductAttributes();
    }

    // -------------------------------------------

    public function showGeneralIdActions()
    {
        return !$this->getListingProduct()->getMagentoProduct()->isBundleType() &&
               !$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions();
    }

    // -------------------------------------------

    public function hasGeneralId()
    {
        return $this->getListingProduct()->getChildObject()->getGeneralId() !== NULL;
    }

    public function getGeneralId()
    {
        return $this->getListingProduct()->getChildObject()->getGeneralId();
    }

    public function getGeneralIdLink()
    {
        $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl(
            $this->getGeneralId(),
            $this->getListingProduct()->getListing()->getMarketplaceId()
        );

        return <<<HTML
<a href="{$url}" target="_blank" title="{$this->getGeneralId()}" >{$this->getGeneralId()}</a>
HTML;
    }

    public function isGeneralIdOwner()
    {
        return $this->getListingProduct()->getChildObject()->isGeneralIdOwner();
    }

    // -------------------------------------------

    public function getDescriptionTemplateLink()
    {
        $url = $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array(
            'id' => $this->getListingProduct()->getChildObject()->getTemplateDescriptionId()
        ));

        $templateTitle = $this->getListingProduct()->getChildObject()->getDescriptionTemplate()->getTitle();

        return <<<HTML
<a href="{$url}" target="_blank" title="{$templateTitle}" >{$templateTitle}</a>
HTML;
    }

    // -------------------------------------------

    public function hasChannelTheme()
    {
        return $this->getListingProduct()->getChildObject()->getVariationManager()->getTypeModel()->hasChannelTheme();
    }

    public function getChannelTheme()
    {
        return $this->getListingProduct()->getChildObject()->getVariationManager()->getTypeModel()->getChannelTheme();
    }

    public function getChannelThemes()
    {
        if (!is_null($this->channelThemes)) {
            return $this->channelThemes;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getListingProduct()->getChildObject();
        $descriptionTemplate = $amazonListingProduct->getAmazonDescriptionTemplate();

        if (!$descriptionTemplate) {
            return array();
        }

        $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $detailsModel->setMarketplaceId($this->getListingProduct()->getListing()->getMarketplaceId());

        return $this->channelThemes = $detailsModel->getVariationThemes($descriptionTemplate->getProductDataNick());
    }

    public function getChannelThemeAttr()
    {
        $theme = $this->getChannelTheme();
        $themes = $this->getChannelThemes();

        if(!empty($themes[$theme])) {
            return $themes[$theme]['attributes'];
        }

        return null;
    }

    public function getChannelThemeNote()
    {
        $theme = $this->getChannelTheme();
        $themes = $this->getChannelThemes();

        if(!empty($themes[$theme])) {
            return $themes[$theme]['note'];
        }

        return null;
    }

    public function getChannelThemeAttrString()
    {
        $themesAttributes = $this->getChannelThemeAttr();

        if(!empty($themesAttributes)) {
            return implode(', ', $themesAttributes);
        }

        return Mage::helper('M2ePro')->__('Variation Theme not found.');
    }

    // ----------------------------------------

    public function hasMatchedAttributes()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->hasMatchedAttributes();
    }

    public function getMatchedAttributes()
    {
        if($this->hasMatchedAttributes()) {
            return $this->getListingProduct()->getChildObject()
                ->getVariationManager()->getTypeModel()->getMatchedAttributes();
        }
        return $this->getMatcherAttributes()->getMatchedAttributes();
    }

    public function getDestinationAttributes()
    {
        if(!$this->hasGeneralId() && $this->isGeneralIdOwner() && $this->hasChannelTheme()) {
            return $this->getChannelThemeAttr();
        }
        return array_keys($this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChannelAttributesSets());
    }

    // ###########################################

    public function getChildListingProducts()
    {
        if (!is_null($this->childListingProducts)) {
            return $this->childListingProducts;
        }

        return $this->childListingProducts = $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getCurrentProductVariations()
    {
        if (!is_null($this->currentProductVariations)) {
            return $this->currentProductVariations;
        }

        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $productVariations = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productVariations[] = $productOption;
        }

        return $this->currentProductVariations = $productVariations;
    }

    public function getChannelVariations()
    {
        if (!is_null($this->childListingProducts)) {
            return $this->childListingProducts;
        }

        return $this->childListingProducts = $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChildListingsProducts();
    }

    public function getCurrentChannelVariations()
    {
        return $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getChannelVariations();
    }

    // ------------------------------------------

    public function getUsedChannelVariations()
    {
        $usedOptions = array();

        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                continue;
            }

            $usedOptions[] = $childTypeModel->getChannelOptions();
        }

        return $usedOptions;
    }

    public function getUsedProductVariations()
    {
        $usedOptions = array();

        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                continue;
            }

            $usedOptions[] = $childTypeModel->getProductOptions();
        }

        return $usedOptions;
    }

    // ------------------------------------------

    public function getUnusedProductVariations()
    {
        return $this->getUnusedVariations($this->getCurrentProductVariations(), $this->getUsedProductVariations());
    }

    public function getUnusedChannelVariations()
    {
        return $this->getUnusedVariations($this->getCurrentChannelVariations(), $this->getUsedChannelVariations());
    }

    private function getUnusedVariations($currentVariations, $usedVariations)
    {
        if (empty($currentVariations)) {
            return array();
        }

        if (empty($usedVariations)) {
            return $currentVariations;
        }

        $unusedOptions = array();

        foreach ($currentVariations as $id => $currentOption) {
            if ($this->isVariationExistsInArray($currentOption, $usedVariations)) {
                continue;
            }

            $unusedOptions[$id] = $currentOption;
        }

        return $unusedOptions;
    }

    private function isVariationExistsInArray(array $needle, array $haystack)
    {
        foreach ($haystack as $option) {
            if ($option != $needle) {
                continue;
            }

            return true;
        }

        return false;
    }

    // ------------------------------------------

    public function hasUnusedProductVariation()
    {
        return count($this->getChildListingProducts()) < count($this->getCurrentProductVariations());
    }

    public function hasUnusedChannelVariations()
    {
        return count($this->getUsedChannelVariations()) < count($this->getCurrentChannelVariations());
    }

    // ------------------------------------------

    public function hasChildWithEmptyProductOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched()) {
                return true;
            }
        }

        return false;
    }

    public function hasChildWithEmptyChannelOptions()
    {
        foreach ($this->getChildListingProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched()) {
                return true;
            }
        }

        return false;
    }

    // ###########################################
}