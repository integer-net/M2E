<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Common extends Mage_Core_Helper_Abstract
{
    // Parser hack -> Mage::helper('M2ePro')->__('Sell On Multi-Channels');

    const NICK  = 'common';
    const TITLE = 'Sell On Multi-Channels';

    const WIZARD_INSTALLATION_NICK = 'installationCommon';
    const MENU_ROOT_NODE_NICK = 'm2epro_common';

    // ########################################

    public function getMenuRootNodeLabel()
    {
        $componentsLabels = array();

        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
        }

        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE);
        }

        if (Mage::helper('M2ePro/Component_Play')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
        }

        if (count($componentsLabels) <= 0 || count($componentsLabels) > 2) {
            return Mage::helper('M2ePro')->__('Sell On Multi-Channels');
        }

        return implode(' / ', $componentsLabels);
    }

    public function getWizardInstallationNick()
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    // ########################################

    public function getAutocompleteMaxInactiveHours()
    {
        $temp = (int)Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/autocomplete/','max_records_quantity');
        return $temp <= 0 ? 100 : $temp;
    }

    // ########################################

    public function getDocumentationUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/common/support/', 'documentation_url');
    }

    public function getVideoTutorialsUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/common/support/', 'video_tutorials_url');
    }

    // ########################################
}