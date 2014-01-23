<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Template_Policy extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Policy');
    }

    // ########################################

    public function getApiName()
    {
        return $this->getData('api_name');
    }

    public function getApiIdentifier()
    {
        return $this->getData('api_identifier');
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_policy');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache')->removeTagValues('ebay_template_policy');
        return parent::delete();
    }

    // #######################################

//    public function getAffectedListingProducts($asObjects = false)
//    {
//        if (is_null($this->getId())) {
//            throw new LogicException('Method require loaded instance first');
//        }
//
//        $listingProducts = $this->getAffectedListingProductsDirectly($asObjects);
//        $listingProductsByListing = $this->getAffectedListingProductsDependingOnListing($asObjects);
//
//        foreach ($listingProductsByListing as $id => $listingProduct) {
//            !$asObjects && $listingProduct = $listingProduct['id'];
//            $listingProducts[$id] = $listingProduct;
//        }
//
//        !$asObjects && $listingProducts = array_values(array_unique($listingProducts));
//
//        return $listingProducts;
//    }
//
//    // #######################################
//
//    private function getAffectedListingProductsDirectly($asObjects = false)
//    {
//        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
//
//        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
//
//        $templates = array(
//            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
//            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
//            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN
//        );
//
//        $where = '';
//        foreach ($templates as $template) {
//            $templateManager->setTemplate($template);
//
//            $where && $where .= ' OR ';
//            $where .= "({$templateManager->getModeColumnName()} = "
//                      .Ess_M2ePro_Model_Ebay_Template_Manager::MODE_POLICY;
//            $where .= " AND {$templateManager->getPolicyIdColumnName()} = {$this->getId()})";
//        }
//
//        $collection->getSelect()->where($where);
//
//        return $asObjects ? $collection->getItems() : $collection->getData();
//    }
//
//    // #######################################
//
//    private function getAffectedListingProductsDependingOnListing($asObjects = false)
//    {
//        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
//
//        $templates = array(
//            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
//            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
//            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN
//        );
//
//        $where = '';
//        foreach ($templates as $template) {
//            $templateManager->setTemplate($template);
//
//            $where && $where .= ' OR ';
//            $where .= "({$templateManager->getModeColumnName()} = "
//                      .Ess_M2ePro_Model_Ebay_Template_Manager::MODE_POLICY;
//            $where .= " AND {$templateManager->getPolicyIdColumnName()} = {$this->getId()})";
//        }
//
//        $listingCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
//
//        $listingCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
//        $listingCollection->getSelect()->columns('id');
//
//        $listingCollection->getSelect()->where($where);
//
//        // ----------------------------------
//
//        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
//        $listingProductCollection->addFieldToFilter('listing_id', array('in' => $listingCollection->getSelect()));
//
//        $where = '';
//        foreach ($templates as $template) {
//            $templateManager->setTemplate($template);
//
//            $where && $where .= ' OR ';
//            $where .= "{$templateManager->getModeColumnName()} = "
//                       .Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT;
//        }
//
//        $listingProductCollection->getSelect()->where($where);
//
//        return $asObjects ? $listingProductCollection->getItems() : $listingProductCollection->getData();
//    }
//
//    // #######################################
//
//    public function setSynchStatusNeed($newData, $oldData)
//    {
//        if (!$this->getResource()->isDifferent($newData,$oldData)) {
//            return;
//        }
//
//        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
//        foreach ($this->getAffectedListingProducts() as $listingProduct) {
//            $listingProduct->setData('synch_status', Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_NEED)->save();
//        }
//    }

    // #######################################
}