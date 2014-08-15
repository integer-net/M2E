<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add extends Mage_Adminhtml_Block_Widget_Container
{
    // ##########################################################

    private $compatibilityType = null;

    private $productGridId = null;

    // ##########################################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/add.phtml');
    }

    protected function _beforeToHtml()
    {
        if (is_null($this->compatibilityType)) {
            throw new LogicException('Compatibility type was not set.');
        }

        return parent::_beforeToHtml();
    }

    // ##########################################################

    public function setCompatibilityType($type)
    {
        $this->compatibilityType = $type;
        return $this;
    }

    public function getCompatibilityType()
    {
        return $this->compatibilityType;
    }

    // ----------------------------------------------------------

    public function setProductGridId($gridId)
    {
        $this->productGridId = $gridId;
        return $this;
    }

    public function getProductGridId()
    {
        return $this->productGridId;
    }

    // ----------------------------------------------------------

    public function getCompatibilityGridId()
    {
        $gridBlockName = '';
        switch ($this->compatibilityType) {
            case Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC:
                $gridBlockName = 'M2ePro/adminhtml_ebay_motor_add_specific_grid';
                break;

            case Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE:
                $gridBlockName = 'M2ePro/adminhtml_ebay_motor_add_ktype_grid';
        }

        if (empty($gridBlockName)) {
            return null;
        }

        return $this->getLayout()->createBlock($gridBlockName)->getId();
    }

    // ##########################################################
}