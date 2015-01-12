<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_AttributeSet extends Ess_M2ePro_Model_Abstract
{
    const OBJECT_TYPE_LISTING                     = 1;
    const OBJECT_TYPE_TEMPLATE_SELLING_FORMAT     = 3;
    const OBJECT_TYPE_AMAZON_TEMPLATE_NEW_PRODUCT = 5;
    const OBJECT_TYPE_BUY_TEMPLATE_NEW_PRODUCT    = 6;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/AttributeSet');
    }

    // ########################################

    public function getObjectId()
    {
        return (int)$this->getData('object_id');
    }

    public function getObjectType()
    {
        return (int)$this->getData('object_type');
    }

    public function getAttributeSetId()
    {
        return (int)$this->getData('attribute_set_id');
    }

    // ########################################

    public function isListing()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_LISTING;
    }

    public function isSellingFormatTemplate()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
    }

    public function isAmazonTemplateNewProduct()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_AMAZON_TEMPLATE_NEW_PRODUCT;
    }

    public function isBuyTemplateNewProduct()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_BUY_TEMPLATE_NEW_PRODUCT;
    }

    // ########################################
}