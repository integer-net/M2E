<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Abstract
{
    // ########################################

    public function isVariationProductMatched()
    {
        return (bool)(int)$this->getAmazonListingProduct()->getData('is_variation_product_matched');
    }

    // ########################################

    public function isActualProductAttributes()
    {
        $productAttributes = array_map('strtolower', array_keys($this->getProductOptions()));
        $magentoAttributes = array_map('strtolower', $this->getCurrentMagentoAttributes());

        return !array_diff($productAttributes, $magentoAttributes);
    }

    public function isActualProductVariation()
    {
        $currentOptions = $this->getProductOptions();

        $currentOptions = array_change_key_case(array_map('strtolower',$currentOptions), CASE_LOWER);
        $magentoVariations = $this->getListingProduct()->getMagentoProduct()
                                                       ->getVariationInstance()
                                                       ->getVariationsTypeStandard();

        foreach ($magentoVariations['variations'] as $magentoVariation) {

            $magentoOptions = array();

            foreach ($magentoVariation as $magentoOption) {
                $magentoOptions[strtolower($magentoOption['attribute'])] = strtolower($magentoOption['option']);
            }

            if (empty($magentoOptions)) {
                continue;
            }

            if ($currentOptions == $magentoOptions) {
                return true;
            }
        }

        return false;
    }

    // ########################################

    public function setProductVariation(array $variation)
    {
        $this->unsetProductVariation();

        $this->createStructure($variation);

        $options = array();
        foreach ($variation as $option) {
            $options[$option['attribute']] = $option['option'];
        }

        $this->setProductOptions($options, false);

        $this->getListingProduct()->setData('is_variation_product_matched',1);

        if ($this->getListingProduct()->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            $this->createChannelItem($options);
        }

        $this->getListingProduct()->save();
    }

    public function resetProductVariation()
    {
        if ($this->isVariationProductMatched()) {
            $this->unsetProductVariation();
        } else {
            $this->resetProductOptions();
        }
    }

    public function unsetProductVariation()
    {
        if (!$this->isVariationProductMatched()) {
            return;
        }

        $this->removeStructure();
        $this->resetProductOptions(false);

        $this->getListingProduct()->setData('is_variation_product_matched', 0);

        if ($this->getListingProduct()->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
            $this->removeChannelItems();
        }

        $this->getListingProduct()->save();
    }

    // ########################################

    public function getProductOptions()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (empty($additionalData['variation_product_options'])) {
            return NULL;
        }

        ksort($additionalData['variation_product_options']);

        return $additionalData['variation_product_options'];
    }

    // ----------------------------------------

    private function setProductOptions(array $options, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_product_options'] = $options;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    private function resetProductOptions($save = true)
    {
        $options = array_fill_keys($this->getCurrentMagentoAttributes(), null);
        $this->setProductOptions($options, $save);
    }

    // ########################################

    public function clearTypeData()
    {
        $this->unsetProductVariation();

        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_product_options']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    // ########################################

    private function removeStructure()
    {
        foreach ($this->getListingProduct()->getVariations(true) as $variation) {
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation->deleteInstance();
        }
    }

    private function createStructure(array $variation)
    {
        $variationId = Mage::helper('M2ePro/Component_Amazon')
                                ->getModel('Listing_Product_Variation')
                                ->addData(array(
                                    'listing_product_id' => $this->getListingProduct()->getId()
                                ))->save()->getId();

        foreach ($variation as $option) {

            $tempData = array(
                'listing_product_variation_id' => $variationId,
                'product_id' => $option['product_id'],
                'product_type' => $option['product_type'],
                'attribute' => $option['attribute'],
                'option' => $option['option']
            );

            Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Product_Variation_Option')
                                                   ->addData($tempData)->save();
        }
    }

    // ----------------------------------------

    private function removeChannelItems()
    {
        $items = Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                            ->addFieldToFilter('account_id',$this->getListing()->getAccountId())
                            ->addFieldToFilter('marketplace_id',$this->getListing()->getMarketplaceId())
                            ->addFieldToFilter('sku',$this->getAmazonListingProduct()->getSku())
                            ->addFieldToFilter('product_id',$this->getListingProduct()->getProductId())
                            ->addFieldToFilter('store_id',$this->getListing()->getStoreId())
                            ->getItems();

        foreach ($items as $item) {
            /* @var $item Ess_M2ePro_Model_Amazon_Item */
            $item->deleteInstance();
        }
    }

    private function createChannelItem(array $options)
    {
        $data = array(
            'account_id' => (int)$this->getListing()->getAccountId(),
            'marketplace_id' => (int)$this->getListing()->getMarketplaceId(),
            'sku' => $this->getAmazonListingProduct()->getSku(),
            'product_id' => (int)$this->getListingProduct()->getProductId(),
            'store_id' => (int)$this->getListing()->getStoreId(),
            'variation_options' => json_encode($options),
        );

        Mage::getModel('M2ePro/Amazon_Item')->setData($data)->save();
    }

    // ########################################
}