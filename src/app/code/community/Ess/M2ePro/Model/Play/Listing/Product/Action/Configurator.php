<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator
{
    const ALL_DATA_KEY  = 'all_data';
    const ONLY_DATA_KEY = 'only_data';

    const TYPE_DETAILS  = 'details';
    const TYPE_SELLING  = 'selling';

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
        return $this->isDetails() && $this->isSelling();
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