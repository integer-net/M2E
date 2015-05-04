<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const LISTING_ID_FIELD = 'listing_id';
    const LISTING_PRODUCT_ID_FIELD = 'listing_product_id';
    const LISTING_PARENT_PRODUCT_ID_FIELD = 'parent_listing_product_id';

    //####################################

    protected function getEntityId()
    {
        if ($this->isListingLog()) {
            return $this->getRequest()->getParam('id');
        }

        if ($this->isListingProductLog()) {
            return $this->getRequest()->getParam('listing_product_id');
        }

        return NULL;
    }

    protected function getEntityField()
    {
        if ($this->isListingLog()) {
            return self::LISTING_ID_FIELD;
        }

        if ($this->isListingProductLog()) {
            return self::LISTING_PRODUCT_ID_FIELD;
        }

        return NULL;
    }

    protected function getActionName()
    {
        switch ($this->getEntityField()) {
            case self::LISTING_ID_FIELD:
                return 'listingGrid';
                break;

            case self::LISTING_PRODUCT_ID_FIELD:
                return 'listingProductGrid';
                break;

        }
        return 'listingGrid';
    }

    //####################################

    public function isListingLog()
    {
        $id = $this->getRequest()->getParam('id');
        return !empty($id);
    }

    public function isListingProductLog()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id');
        return !empty($listingProductId);
    }

    //####################################

    public function getListingProductId()
    {
        return $this->getRequest()->getParam('listing_product_id', false);
    }

    // ----------------------------------------

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct = NULL;

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
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
        $fullDescription = Mage::helper('M2ePro/View')->decodeLogMessage($row->getData('description'));
        $row->setData('description', $fullDescription);

        $value = $column->getRenderer()->render($row);
        return $this->prepareLongText($fullDescription, $value);
    }

    //####################################

    protected function prepareLongText($fullText, $renderedText)
    {
        if (strlen($fullText) == strlen($renderedText)) {
            return Mage::helper('M2ePro/View')->appendLinksToLogMessage($renderedText);
        }

        $fullText = Mage::helper('M2ePro/View')->appendLinksToLogMessage($fullText);

        $renderedText .= '&nbsp;(<a href="javascript:void(0)" onclick="LogHandlerObj.showFullText(this);">more</a>)
                          <div style="display: none;"><br/>'.$fullText.'<br/><br/></div>';

        return $renderedText;
    }

    //####################################
}