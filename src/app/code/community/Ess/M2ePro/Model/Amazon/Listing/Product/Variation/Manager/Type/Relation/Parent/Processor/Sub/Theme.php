<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2EPro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Theme
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
{
    // ##########################################################

    protected function check()
    {
        $currentTheme = $this->getProcessor()->getTypeModel()->getChannelTheme();
        if (empty($currentTheme)) {
            return;
        }

        if (!$this->getProcessor()->isGeneralIdOwner()) {
            $this->getProcessor()->getTypeModel()->setChannelTheme(null, false);
            $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
            return;
        }

        if (!$this->getProcessor()->getAmazonListingProduct()->isExistDescriptionTemplate() ||
            !$this->getProcessor()->getAmazonDescriptionTemplate()->isNewAsinAccepted()
        ) {
            $this->getProcessor()->getTypeModel()->setChannelTheme(null, false);
            $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
            return;
        }

        $possibleThemes = $this->getProcessor()->getPossibleThemes();
        if (empty($possibleThemes[$currentTheme])) {
            $this->getProcessor()->getTypeModel()->setChannelTheme(null, false);
            $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
            return;
        }

        $currentThemeAttributes = $possibleThemes[$currentTheme]['attributes'];

        if ($this->getProcessor()->isGeneralIdSet()) {
            $currentChannelAttributes = $this->getProcessor()->getTypeModel()->getChannelAttributesSets();

            if (array_diff($currentThemeAttributes, array_keys($currentChannelAttributes))) {
                $this->getProcessor()->getTypeModel()->setChannelTheme(null, false);
                $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
            }

            return;
        }

        $magentoVariations = $this->getProcessor()->getActualMagentoProductVariations();

        if ($this->getProcessor()->getTypeModel()->isChannelThemeSetManually()) {
            if (count($magentoVariations['set']) != count($currentThemeAttributes)) {
                $this->getProcessor()->getTypeModel()->setChannelTheme(null, false);
                $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
            }

            return;
        }

        if (array_keys($magentoVariations['set']) != $currentThemeAttributes) {
            $this->getProcessor()->getTypeModel()->setChannelTheme(null, false);
            $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
        }
    }

    protected function execute()
    {
        if ($this->getProcessor()->getTypeModel()->getChannelTheme() || !$this->getProcessor()->isGeneralIdOwner()) {
            return;
        }

        $possibleThemes = $this->getProcessor()->getPossibleThemes();

        if (!$this->getProcessor()->getAmazonListingProduct()->isExistDescriptionTemplate() ||
            !$this->getProcessor()->getAmazonDescriptionTemplate()->isNewAsinAccepted() ||
            empty($possibleThemes)
        ) {
            return;
        }

        if ($this->getProcessor()->isGeneralIdSet()) {
            $this->processExistProduct();
            return;
        }

        $this->processNewProduct();
    }

    // ##########################################################

    private function processExistProduct()
    {
        $possibleThemes = $this->getProcessor()->getPossibleThemes();
        $channelAttributes = array_keys(
            $this->getProcessor()->getTypeModel()->getChannelAttributesSets()
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme $themeMatcher */
        $themeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Theme');
        $themeMatcher->setThemes($possibleThemes);
        $themeMatcher->setSourceAttributes($channelAttributes);

        $matchedTheme = $themeMatcher->getMatchedTheme();
        if (is_null($matchedTheme)) {
            return;
        }

        $this->getProcessor()->getTypeModel()->setChannelTheme($matchedTheme, false);
        $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
    }

    private function processNewProduct()
    {
        $possibleThemes = $this->getProcessor()->getPossibleThemes();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme $themeMatcher */
        $themeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Theme');
        $themeMatcher->setThemes($possibleThemes);
        $themeMatcher->setMagentoProduct($this->getProcessor()->getListingProduct()->getMagentoProduct());

        $matchedTheme = $themeMatcher->getMatchedTheme();
        if (is_null($matchedTheme)) {
            return;
        }

        $this->getProcessor()->getTypeModel()->setChannelTheme($matchedTheme, false);
        $this->getProcessor()->getTypeModel()->setIsChannelThemeSetManually(false, false);
    }

    // ##########################################################
}