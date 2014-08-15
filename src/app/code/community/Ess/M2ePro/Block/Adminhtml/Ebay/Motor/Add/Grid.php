<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ##########################################################

    private $listingId = null;

    // ##########################################################

    abstract public function getCompatibilityType();

    // ##########################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotor'.$this->getCompatibilityType().'Grid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('make');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ##########################################################

    public function setListingId($marketplaceId)
    {
        $this->listingId = $marketplaceId;
        return $this;
    }

    public function getListingId()
    {
        return $this->listingId;
    }

    // ##########################################################

    protected function _prepareMassaction()
    {
        $typeIdentifier = Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->getIdentifierKey(
            $this->getCompatibilityType()
        );

        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('main_table.'.$typeIdentifier);
        $this->getMassactionBlock()->setFormFieldName($typeIdentifier);
        //--------------------------------

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('overwrite_attribute', array(
            'label'   => Mage::helper('M2ePro')->__('Overwrite in Compatibility Attribute'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('add_to_attribute', array(
            'label'   => Mage::helper('M2ePro')->__('Add to Compatibility Attribute'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        // this is required for correct work of massaction js
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //##############################################################

    public function callbackNullableColumn($value, $row, $column, $isExport)
    {
        return $value ? $value : '--';
    }

    public function callbackColumnIdentifier($value, $row, $column, $isExport)
    {
        $idKey = Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->getIdentifierKey(
            $this->getCompatibilityType()
        );

        $editLabel = Mage::helper('M2ePro')->__('Edit Note');
        $addLabel = Mage::helper('M2ePro')->__('Add Note');
        $saveLabel = Mage::helper('M2ePro')->__('Save Note');
        $cancelLabel = Mage::helper('M2ePro')->__('Cancel');

        return <<<HTML

{$value}
<br />
<br />
<div id="note_{$row[$idKey]}">
    <span id="note_view_{$row[$idKey]}"></span>
    <textarea id="note_edit_{$row[$idKey]}" style="display: none"></textarea>
    <br />
    <span id="note_edit_link_{$row[$idKey]}" style="display: none;">
        <a href="javascript:void(0)" onclick="EbayMotorCompatibilityHandlerObj.switchNoteEditMode('{$row[$idKey]}')">
            {$editLabel}
        </a>
    </span>
    <span id="note_add_link_{$row[$idKey]}">
        <a href="javascript:void(0)" onclick="EbayMotorCompatibilityHandlerObj.switchNoteEditMode('{$row[$idKey]}')">
            {$addLabel}
        </a>
    </span>
    <span id="note_save_link_{$row[$idKey]}" style="display: none">
        <a href="javascript:void(0)" onclick="EbayMotorCompatibilityHandlerObj.saveNote('{$row[$idKey]}')">
            {$saveLabel}
        </a>
    </span>
    &nbsp;&nbsp;&nbsp;
    <span id="note_cancel_link_{$row[$idKey]}" style="display: none">
        <a href="javascript:void(0)"
           onclick="EbayMotorCompatibilityHandlerObj.switchNoteEditMode('{$row[$idKey]}', true)">
            {$cancelLabel}
        </a>
    </span>
</div>

HTML;

    }

    //##############################################################

    public function getRowUrl($row)
    {
        return false;
    }

    //##############################################################

    protected function _toHtml()
    {
        $additionalJs =
'<script type="text/javascript">
    $H(EbayMotorCompatibilityHandlerObj.savedNotes).each(function(note) {
         if ($(\'note_view_\' + note.key)) {
             $(\'note_view_\' + note.key).innerHTML = note.value;
         }
    });
</script>';

        return parent::_toHtml() . $additionalJs;
    }

    //##############################################################
}