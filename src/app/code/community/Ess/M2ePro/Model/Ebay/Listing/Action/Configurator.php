<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_Configurator
    extends Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const MODE_EMPTY = 'empty';

    const DATA_TYPE_GENERAL     = 'general';
    const DATA_TYPE_QTY         = 'qty';
    const DATA_TYPE_PRICE       = 'price';
    const DATA_TYPE_TITLE       = 'title';
    const DATA_TYPE_SUBTITLE    = 'subtitle';
    const DATA_TYPE_DESCRIPTION = 'description';

    // ########################################

    public function getAllModes()
    {
        return array_merge(
            parent::getAllModes(),
            array(self::MODE_EMPTY)
        );
    }

    // ########################################

    public function isEmptyMode()
    {
        return $this->mode == self::MODE_EMPTY;
    }

    public function setEmptyMode()
    {
        return $this->setMode(self::MODE_EMPTY);
    }

    // ########################################

    public function getAllDataTypes()
    {
        return array(
            self::DATA_TYPE_GENERAL,
            self::DATA_TYPE_QTY,
            self::DATA_TYPE_PRICE,
            self::DATA_TYPE_TITLE,
            self::DATA_TYPE_SUBTITLE,
            self::DATA_TYPE_DESCRIPTION,
        );
    }

    // ########################################

    public function isAllAllowed()
    {
        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isAllAllowed();
    }

    public function getAllowedDataTypes()
    {
        if ($this->isEmptyMode()) {
            return array();
        }

        return parent::getAllowedDataTypes();
    }

    // ########################################

    public function isAllowed($dataType)
    {
        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isAllowed($dataType);
    }

    // ########################################

    public function isGeneralAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_GENERAL);
    }

    public function allowGeneral()
    {
        if ($this->isGeneralAllowed() || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_GENERAL;
        return $this;
    }

    // ----------------------------------------

    public function isQtyAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    public function allowQty()
    {
        if ($this->isQtyAllowed() || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_QTY;
        return $this;
    }

    // ----------------------------------------

    public function isPriceAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    public function allowPrice()
    {
        if ($this->isPriceAllowed() || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_PRICE;
        return $this;
    }

    // ----------------------------------------

    public function isTitleAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_TITLE);
    }

    public function allowTitle()
    {
        if ($this->isTitleAllowed() || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_TITLE;
        return $this;
    }

    // ----------------------------------------

    public function isSubtitleAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SUBTITLE);
    }

    public function allowSubtitle()
    {
        if ($this->isSubtitleAllowed() || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_SUBTITLE;
        return $this;
    }

    // ----------------------------------------

    public function isDescriptionAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_DESCRIPTION);
    }

    public function allowDescription()
    {
        if ($this->isDescriptionAllowed() || $this->isEmptyMode()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_DESCRIPTION;
        return $this;
    }

    // ########################################

    public function isDataConsists(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($configurator->isEmptyMode()) {
            return true;
        }

        if ($this->isEmptyMode()) {
            return false;
        }

        return parent::isDataConsists($configurator);
    }

    // -----------------------------------------

    public function mergeData(Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if ($configurator->isEmptyMode()) {
            return $this;
        }

        return parent::mergeData($configurator);
    }

    // ########################################
}