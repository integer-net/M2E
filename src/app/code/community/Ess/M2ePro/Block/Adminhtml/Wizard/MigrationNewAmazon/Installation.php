<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationNewAmazon_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $this->appendWizardCompleteButton();
        //-------------------------------

        // Steps
        //-------------------------------
        $this->setChild(
            'step_marketplaces_synchronization',
            $this->helper('M2ePro/Module_Wizard')->createBlock(
                'installation_marketplacesSynchronization',$this->getNick()
            )
        );

        $descriptionTemplateData = Mage::getSingleton('M2ePro/Wizard_MigrationNewAmazon')
                                    ->getDataForDescriptionTemplatesStep();
        if (!empty($descriptionTemplateData)) {
            $this->setChild(
                'step_description_templates',
                $this->helper('M2ePro/Module_Wizard')->createBlock(
                    'installation_descriptionTemplates',$this->getNick()
                )
            );
        }

        $this->setChild(
            'step_information',
            $this->helper('M2ePro/Module_Wizard')->createBlock(
                'installation_information',$this->getNick()
            )
        );
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function getHeaderTextHtml()
    {
        return 'Migration Wizard to the New M2E Pro Amazon Generation';
    }

    protected function _toHtml()
    {
        $urls = json_encode(array(
            'marketplacesSynchronization' => $this->getUrl('*/*/marketplacesSynchronization')
        ));

        $js = <<<JS
        <script>
            M2ePro.url.add({$urls});
            MigrationNewAmazonHandlerObj = new MigrationNewAmazonHandler;
        </script>
JS
;
        return parent::_toHtml()
               . $js
               . $this->getChildHtml('step_marketplaces_synchronization')
               . $this->getChildHtml('step_description_templates')
               . $this->getChildHtml('step_information')
               . $this->getChildHtml('end_button');
    }

    // ########################################
}