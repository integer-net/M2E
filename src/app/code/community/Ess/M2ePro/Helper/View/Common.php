<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Common extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Sell On Multi-Channels

    const NICK  = 'common';
    const TITLE = 'Sell On Multi-Channels';

    const WIZARD_INSTALLATION_NICK = 'installationCommon';
    const MENU_ROOT_NODE_NICK = 'm2epro_common';

    // ########################################

    public function getMenuRootNodeLabel()
    {
        $activeComponents = $this->getActiveComponentsLabels();

        if (count($activeComponents) <= 0 || count($activeComponents) > 1) {
            return Mage::helper('M2ePro')->__(self::TITLE);
        }

        return array_shift($activeComponents);
    }

    // ########################################

    public function getActiveComponentsLabels()
    {
        $labels = array();

        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $labels[] = Mage::helper('M2ePro/Component_Amazon')->getTitle();
        }

        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $labels[] = Mage::helper('M2ePro/Component_Buy')->getTitle();
        }

        return $labels;
    }

    // ########################################

    public function getPageNavigationPath($pathNick, $tabName = NULL, $channel = NULL, $additionalEnd = NULL,
                                          $params = array())
    {
        $pathParts = array();

        $rootMenuNode = Mage::getConfig()->getNode('adminhtml/menu/m2epro_common');
        $menuLabel = Mage::helper('M2ePro/View')->getMenuPath($rootMenuNode, $pathNick, $this->getMenuRootNodeLabel());

        if (!$menuLabel) {
            return '';
        }

        $pathParts['menu'] = $menuLabel;

        if ($tabName) {
            $pathParts['tab'] = $tabName . ' ' . Mage::helper('M2ePro')->__('Tab');
        } else {
            $pathParts['tab'] = NULL;
        }

        $channelLabel = '';
        if ($channel) {

            $components = $this->getActiveComponentsLabels();

            if ($channel == 'any') {
                if (count($components) > 1) {
                    if (isset($params['any_channel_as_label']) && $params['any_channel_as_label'] === true) {
                        $channelLabel = Mage::helper('M2ePro')->__('Any Channel');
                    } else {
                        $channelLabel = '[' . join($components, '/') . ']';
                    }
                }

            } elseif ($channel == 'all') {
                if (count($components) > 1) {
                    $channelLabel = Mage::helper('M2ePro')->__('All Channels');
                }
            } else {

                if (!Mage::helper('M2ePro/Component_' . ucfirst($channel))->isActive()) {
                    throw new Exception('Channel is not Active!');
                }

                if (count($components) > 1) {
                    $channelLabel = Mage::helper('M2ePro/Component_' . ucfirst($channel))->getTitle();
                }
            }
        }

        $pathParts['channel'] = $channelLabel;

        $pathParts['additional'] = $additionalEnd;

        $resultPath = array();

        $resultPath['menu'] = $pathParts['menu'];
        if (isset($params['reverse_tab_and_channel']) && $params['reverse_tab_and_channel'] === true) {
            $resultPath['channel'] = $pathParts['channel'];
            $resultPath['tab'] = $pathParts['tab'];
        } else {
            $resultPath['tab'] = $pathParts['tab'];
            $resultPath['channel'] = $pathParts['channel'];
        }
        $resultPath['additional'] = $pathParts['additional'];

        $resultPath = array_diff($resultPath, array(''));

        return join($resultPath, ' > ');
    }

    // ########################################

    public function getWizardInstallationNick()
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    public function isInstallationWizardFinished()
    {
        return Mage::helper('M2ePro/Module_Wizard')->isFinished(
            $this->getWizardInstallationNick()
        );
    }

    // ########################################

    public function getAutocompleteMaxItems()
    {
        $temp = (int)Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/view/common/autocomplete/','max_records_quantity');
        return $temp <= 0 ? 100 : $temp;
    }

    // ########################################

    public function getDocumentationUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()
                    ->getGroupValue('/view/common/support/', 'documentation_url');
    }

    public function getVideoTutorialsUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()
                    ->getGroupValue('/view/common/support/', 'video_tutorials_url');
    }

    // ########################################

    public function prepareMenu(array $menuArray)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed(self::MENU_ROOT_NODE_NICK)) {
            return $menuArray;
        }

        if (count(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents()) <= 0) {
            unset($menuArray[self::MENU_ROOT_NODE_NICK]);
            return $menuArray;
        }

        $tempTitle = $this->getMenuRootNodeLabel();
        !empty($tempTitle) && $menuArray[self::MENU_ROOT_NODE_NICK]['label'] = $tempTitle;

        // Add wizard menu item
        //---------------------------------
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeBlocker = $wizardHelper->getActiveBlockerWizard(Ess_M2ePro_Helper_View_Common::NICK);

        if ($activeBlocker) {

            unset($menuArray[self::MENU_ROOT_NODE_NICK]['children']);
            unset($menuArray[self::MENU_ROOT_NODE_NICK]['click']);

            $menuArray[self::MENU_ROOT_NODE_NICK]['url'] = Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_wizard_'.$wizardHelper->getNick($activeBlocker).'/index'
            );
            $menuArray[self::MENU_ROOT_NODE_NICK]['last'] = true;

            return $menuArray;
        }
        //---------------------------------

        // Set documentation redirect url
        //---------------------------------
        if (isset($menuArray[self::MENU_ROOT_NODE_NICK]['children']['help']['children']['doc'])) {
            $menuArray[self::MENU_ROOT_NODE_NICK]['children']['help']['children']['doc']['click'] =
                "window.open(this.href, '_blank'); return false;";
            $menuArray[self::MENU_ROOT_NODE_NICK]['children']['help']['children']['doc']['url'] =
                $this->getDocumentationUrl();
        }
        //---------------------------------

        // Set video tutorials redirect url
        //---------------------------------
        if (isset($menuArray[self::MENU_ROOT_NODE_NICK]['children']['help']['children']['tutorial'])) {
            $menuArray[self::MENU_ROOT_NODE_NICK]['children']['help']['children']['tutorial']['click'] =
                "window.open(this.href, '_blank'); return false;";
            $menuArray[self::MENU_ROOT_NODE_NICK]['children']['help']['children']['tutorial']['url'] =
                $this->getVideoTutorialsUrl();
        }
        //---------------------------------

        return $menuArray;
    }

    // ########################################
}