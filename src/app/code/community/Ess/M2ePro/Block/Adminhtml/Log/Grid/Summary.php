<?php

class Ess_M2ePro_Block_Adminhtml_Log_Grid_Summary extends Mage_Adminhtml_Block_Widget
{
    protected $tip = NULL;
    protected $iconSrc = NULL;
    protected $rows = array();

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('logGridSummary');
        //------------------------------

        $this->setTemplate('M2ePro/log/grid/summary.phtml');
    }

    public function getTip()
    {
        return $this->tip;
    }

    public function getIconSrc()
    {
        return $this->iconSrc;
    }

    public function getEncodedRows()
    {
        return base64_encode(json_encode($this->rows));
    }

    public function getEntityId()
    {
        if (!isset($this->_data['entity_id']) || !is_int($this->_data['entity_id'])) {
            throw new LogicException('Entity ID is not set.');
        }

        return $this->_data['entity_id'];
    }

    public function getViewHelpHandler()
    {
        if (!isset($this->_data['view_help_handler']) || !is_string($this->_data['view_help_handler'])) {
            throw new LogicException('View help handler is not set.');
        }

        return $this->_data['view_help_handler'];
    }

    public function getCloseHelpHandler()
    {
        if (!isset($this->_data['hide_help_handler']) || !is_string($this->_data['hide_help_handler'])) {
            throw new LogicException('Close help handler is not set.');
        }

        return $this->_data['hide_help_handler'];
    }

    protected function getRows()
    {
        if (!isset($this->_data['rows']) || !is_array($this->_data['rows'])) {
            throw new LogicException('Log rows are not set.');
        }

        if (count($this->_data['rows']) == 0) {
            return array();
        }

        return array_slice($this->_data['rows'], 0, 3);
    }

    protected function getTips()
    {
        if (!isset($this->_data['tips']) || !is_array($this->_data['tips'])) {
            throw new LogicException('Log tips are not set.');
        }

        return $this->_data['tips'];
    }

    protected function getIcons()
    {
        if (!isset($this->_data['icons']) || !is_array($this->_data['icons'])) {
            throw new LogicException('Log icons are not set.');
        }

        return $this->_data['icons'];
    }

    protected function _beforeToHtml()
    {
        $rows = $this->getRows();

        if (count($rows) == 0) {
            return parent::_beforeToHtml();
        }

        $lastActionRow = $rows[0];
        //--------------------------

        // Get log icon
        //--------------------------
        $icon = 'normal';
        $tip = Mage::helper('M2ePro')->__('Last action was completed successfully.');

        if (isset($lastActionRow['type'])) {
            $tip = $this->getTipByType($lastActionRow['type']);
            $icon = $this->getIconByType($lastActionRow['type']);
        }

        $this->tip = Mage::helper('M2ePro')->escapeHtml($tip);
        $this->iconSrc = $this->getSkinUrl('M2ePro').'/images/log_statuses/'.$icon.'.png';
        $this->rows = $rows;
        //--------------------------

        return parent::_beforeToHtml();
    }

    protected function getTipByType($type)
    {
        foreach ($this->getTips() as $tipType => $tip) {
            if ($tipType == $type) {
                return Mage::helper('M2ePro')->__($tip);
            }
        }

        return Mage::helper('M2ePro')->__('Last action was completed successfully.');
    }

    protected function getIconByType($type)
    {
        foreach ($this->getIcons() as $iconType => $icon) {
            if ($iconType == $type) {
                return $icon;
            }
        }

        return 'normal';
    }

    protected function _toHtml()
    {
        if (count($this->rows) == 0) {
            return '';
        }

        return parent::_toHtml();
    }
}