<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
{
    const ALL_DATA_KEY     = 'all_data';
    const ONLY_DATA_KEY    = 'only_data';

    const TYPE_GENERAL     = 'general';

    const TYPE_QTY         = 'qty';
    const TYPE_PRICE       = 'price';

    const TYPE_TITLE       = 'title';
    const TYPE_SUBTITLE    = 'subtitle';
    const TYPE_DESCRIPTION = 'description';

    /**
     * @var array
     */
    private $params = array();

    // ########################################

    abstract public function isAllPermitted();

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

    // ########################################

    public function isGeneral()
    {
        return $this->isAllowed(self::TYPE_GENERAL);
    }

    // -----------------------------------------

    public function isQty()
    {
        return $this->isAllowed(self::TYPE_QTY);
    }

    public function isPrice()
    {
        return $this->isAllowed(self::TYPE_PRICE);
    }

    // -----------------------------------------

    public function isTitle()
    {
        return $this->isAllowed(self::TYPE_TITLE);
    }

    public function isSubtitle()
    {
        return $this->isAllowed(self::TYPE_SUBTITLE);
    }

    public function isDescription()
    {
        return $this->isAllowed(self::TYPE_DESCRIPTION);
    }

    // ########################################

    protected function isAllowed($type)
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