<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator
{
    const ALL_DATA_KEY      = 'all_data';
    const ONLY_DATA_KEY     = 'only_data';

    const TYPE_DETAILS      = 'details';
    const TYPE_SELLING      = 'selling';
    const TYPE_SHIPPING     = 'shipping';
    const TYPE_NEW_PRODUCT  = 'new_product';

    /**
     * @var array
     */
    private $params = array();

    // ########################################

    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    // ########################################

    public function isAll()
    {
        return isset($this->params[self::ALL_DATA_KEY]) &&
               (bool)$this->params[self::ALL_DATA_KEY];
    }

    public function isOnly()
    {
        return isset($this->params[self::ONLY_DATA_KEY]) &&
               is_array($this->params[self::ONLY_DATA_KEY]) &&
               count($this->params[self::ONLY_DATA_KEY]) > 0;
    }

    // ----------------------------------------

    public function isAllPermitted()
    {
        return $this->isShipping() && $this->isSelling() &&
               $this->isDetails() && $this->isNewProduct();
    }

    // ########################################

    public function isDetails()
    {
        return $this->isAllowed(self::TYPE_DETAILS);
    }

    public function isSelling()
    {
        return $this->isAllowed(self::TYPE_SELLING);
    }

    public function isShipping()
    {
        return $this->isAllowed(self::TYPE_SHIPPING);
    }

    public function isNewProduct()
    {
        return $this->isAllowed(self::TYPE_NEW_PRODUCT);
    }

    // ########################################

    private function isAllowed($type)
    {
        if ($this->isAll()) {
            return true;
        }

        if (!$this->isOnly()) {
            return true;
        }

        return isset($this->params[self::ONLY_DATA_KEY][$type]) &&
               (bool)$this->params[self::ONLY_DATA_KEY][$type];
    }

    // ########################################
}