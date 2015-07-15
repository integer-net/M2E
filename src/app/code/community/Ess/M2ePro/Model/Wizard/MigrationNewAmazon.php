<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Wizard_MigrationNewAmazon extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'marketplacesSynchronization',
        'descriptionTemplates',
        'information'
    );

    // ########################################

    public function getSteps()
    {
        $steps = $this->steps;
        $descriptionTemplatesData = $this->getDataForDescriptionTemplatesStep();

        if (empty($descriptionTemplatesData) &&
            (false !== $index = array_search('descriptionTemplates', $steps))) {
            unset($steps[$index]);
            $steps = array_values($steps);
        }

        return $steps;
    }

    // ########################################

    public function getDataForDescriptionTemplatesStep()
    {
        $tempTemplates = Mage::getModel('M2ePro/Registry')->load('/wizard/new_amazon_description_templates/', 'key')
                                                          ->getData('value');

        return $tempTemplates ? (array)json_decode($tempTemplates, true) : array();
    }

    public function isActive()
    {
        /** @var $marketplace Ess_M2ePro_Model_Marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Amazon')->getModel('Marketplace');
        $collection = $marketplace->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        if ($collection->getSize() <= 0 || !Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
            $wizardHelper = Mage::helper('M2ePro/Module_Wizard');
            $wizardHelper->setStatus(
                $this->getNick(),
                Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED
            );

            return false;
        }

        return true;
    }

    public function getNick()
    {
        return 'migrationNewAmazon';
    }

    // ########################################
}