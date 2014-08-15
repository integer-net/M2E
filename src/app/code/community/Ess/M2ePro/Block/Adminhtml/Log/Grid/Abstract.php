<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid
{
    //####################################

    protected function getEntityId()
    {
        $entityData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (isset($entityData['id'])) {
            return $entityData['id'];
        }

        return NULL;
    }

    //####################################

    protected function _getLogTypeList()
    {
        return array(
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE => Mage::helper('M2ePro')->__('Notice'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR => Mage::helper('M2ePro')->__('Error')
        );
    }

    protected function _getLogPriorityList()
    {
        return array(
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH => Mage::helper('M2ePro')->__('High'),
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM => Mage::helper('M2ePro')->__('Medium'),
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW => Mage::helper('M2ePro')->__('Low')
        );
    }

    protected function _getLogInitiatorList()
    {
        return array(
            Ess_M2ePro_Helper_Data::INITIATOR_USER => Mage::helper('M2ePro')->__('Manual'),
            Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION => Mage::helper('M2ePro')->__('Automatic')
        );
    }

    //####################################

    public function callbackColumnType($value, $row, $column, $isExport)
    {
         switch ($row->getData('type')) {

            case Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE:
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS:
                $value = '<span style="color: green;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING:
                $value = '<span style="color: orange; font-weight: bold;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR:
                 $value = '<span style="color: red; font-weight: bold;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnPriority($value, $row, $column, $isExport)
    {
         switch ($row->getData('priority')) {

            case Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH:
                $value = '<span style="font-weight: bold;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM:
                $value = '<span style="font-style: italic;">'.$value.'</span>';
                break;

            case Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW:
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnInitiator($value, $row, $column, $isExport)
    {
        switch ($row->getData('initiator')) {

            case Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION:
                $value = '<span style="text-decoration: underline;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackDescription($value, $row, $column, $isExport)
    {
        $fullDescription = Mage::getModel('M2ePro/Log_Abstract')->decodeDescription($row->getData('description'));
        $fullDescription = Mage::helper('M2ePro')->escapeHtml($fullDescription, array(), ENT_NOQUOTES);

        $row->setData('description', $fullDescription);

        $value = $column->getRenderer()->render($row);
        return $this->prepareLongText($fullDescription, $value);
    }

    //####################################

    protected function prepareLongText($fullText, $renderedText)
    {
        if (strlen($fullText) == strlen($renderedText)) {
            return Mage::helper('M2ePro/View')->getModifiedLogMessage($renderedText);
        }

        $fullText = Mage::helper('M2ePro/View')->getModifiedLogMessage($fullText);

        $renderedText .= '&nbsp;(<a href="javascript:void(0)" onclick="LogHandlerObj.showFullText(this);">more</a>)
                          <div style="display: none;"><br />'.$fullText.'<br /><br /></div>';

        return $renderedText;
    }

    //####################################
}