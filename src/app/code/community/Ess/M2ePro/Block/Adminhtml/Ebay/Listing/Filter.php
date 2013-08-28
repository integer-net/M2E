<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Filter extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingFilter');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/filter.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $maxRecordsQuantity = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/autocomplete/',
                                                                                        'max_records_quantity');
        $maxRecordsQuantity <= 0 && $maxRecordsQuantity = 100;
        //-------------------------------

        //-------------------------------
        $this->selectedSellingFormatTemplate = (int)$this->getRequest()
                                                         ->getParam('filter_ebay_selling_format_template');
        $sellingFormatTemplatesCollection = Mage::helper('M2ePro/Component_Ebay')
                                                ->getCollection('Template_SellingFormat')
                                                ->setOrder('title', 'ASC');

        if ($sellingFormatTemplatesCollection->getSize() < $maxRecordsQuantity) {
            $this->sellingFormatTemplatesDropDown = true;
            $sellingFormatTemplates = array();

            foreach ($sellingFormatTemplatesCollection as $item) {
                $sellingFormatTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->sellingFormatTemplates = $sellingFormatTemplates;
        } else {
            $this->sellingFormatTemplatesDropDown = false;
            $this->sellingFormatTemplates = array();

            if ($this->selectedSellingFormatTemplate > 0) {
                $this->selectedSellingFormatTemplateValue = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_SellingFormat',
                                      $this->selectedSellingFormatTemplate, NULL,
                                      array('template'))
                    ->getTitle();
            } else {
                $this->selectedSellingFormatTemplateValue = '';
            }
        }

        $this->sellingFormatTemplateUrl = $this->makeCutUrlForTemplate('filter_ebay_selling_format_template');
        //-------------------------------

        //-------------------------------
        $this->selectedDescriptionTemplate = (int)$this->getRequest()->getParam('filter_ebay_description_template');
        $descriptionsTemplatesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_Description')
                                                                           ->setOrder('title', 'ASC');

        if ($descriptionsTemplatesCollection->getSize() < $maxRecordsQuantity) {
            $this->descriptionsTemplatesDropDown = true;
            $descriptionsTemplates = array();

            foreach ($descriptionsTemplatesCollection as $item) {
                $descriptionsTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->descriptionsTemplates = $descriptionsTemplates;
        } else {
            $this->descriptionsTemplatesDropDown = false;
            $this->descriptionsTemplates = array();

            if ($this->selectedDescriptionTemplate > 0) {
                $this->selectedDescriptionTemplateValue = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_Description',
                                      $this->selectedDescriptionTemplate, NULL,
                                      array('template'))
                    ->getTitle();
            } else {
                $this->selectedDescriptionTemplateValue = '';
            }
        }

        $this->descriptionTemplateUrl = $this->makeCutUrlForTemplate('filter_ebay_description_template');
        //-------------------------------

        //-------------------------------
        $this->selectedGeneralTemplate = (int)$this->getRequest()->getParam('filter_ebay_general_template');
        $generalTemplatesCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Template_General')
                                                                      ->setOrder('title', 'ASC');

        if ($generalTemplatesCollection->getSize() < $maxRecordsQuantity) {
            $this->generalTemplatesDropDown = true;
            $generalTemplates = array();

            foreach ($generalTemplatesCollection as $item) {
                $generalTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->generalTemplates = $generalTemplates;
        } else {
            $this->generalTemplatesDropDown = false;
            $this->generalTemplates = array();

            if ($this->selectedGeneralTemplate > 0) {
                $this->selectedGeneralTemplateValue = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_General',
                                      $this->selectedGeneralTemplate, NULL,
                                      array('template'))
                    ->getTitle();
            } else {
                $this->selectedGeneralTemplateValue = '';
            }
        }

        $this->generalTemplateUrl = $this->makeCutUrlForTemplate('filter_ebay_general_template');
        //-------------------------------

        //-------------------------------
        $this->selectedSynchronizationTemplate = (int)$this->getRequest()
                                                           ->getParam('filter_ebay_synchronization_template');
        $synchronizationsTemplatesCollection = Mage::helper('M2ePro/Component_Ebay')
                                                        ->getCollection('Template_Synchronization')
                                                        ->setOrder('title', 'ASC');

        if ($synchronizationsTemplatesCollection->getSize() < $maxRecordsQuantity) {
            $this->synchronizationsTemplatesDropDown = true;
            $synchronizationsTemplates = array();

            foreach ($synchronizationsTemplatesCollection as $item) {
                $synchronizationsTemplates[$item->getId()] = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            }
            $this->synchronizationsTemplates = $synchronizationsTemplates;
        } else {
            $this->synchronizationsTemplatesDropDown = false;
            $this->synchronizationsTemplates = array();

            if ($this->selectedSynchronizationTemplate > 0) {
                $this->selectedSynchronizationTemplateValue = Mage::helper('M2ePro/Component_Ebay')
                    ->getCachedObject('Template_Synchronization',
                                      $this->selectedSynchronizationTemplate, NULL,
                                      array('template'))
                    ->getTitle();
            } else {
                $this->selectedSynchronizationTemplateValue = '';
            }
        }

        $this->synchronizationTemplateUrl = $this->makeCutUrlForTemplate('filter_ebay_synchronization_template');
        //-------------------------------

        return parent::_beforeToHtml();
    }

    protected function makeCutUrlForTemplate($templateUrlParamName)
    {
        $paramsFilters = array(
            'filter_ebay_selling_format_template',
            'filter_ebay_description_template',
            'filter_ebay_general_template',
            'filter_ebay_synchronization_template'
        );

        $params = array();
        foreach ($paramsFilters as $value) {
            if ($value != $templateUrlParamName) {
                $params[$value] = $this->getRequest()->getParam($value);
            }
        }

        $params['tab'] = Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY;

        return $this->getUrl('*/adminhtml_listing/*',$params);
    }
}