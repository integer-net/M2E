<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Menu extends Mage_Adminhtml_Block_Page_Menu
{
    //#############################################

    public function getModuleName()
    {
        if (Mage::getStoreConfig('advanced/modules_disable_output/Ess_M2ePro')) {
            return 'Mage_Adminhtml';
        }
        return parent::getModuleName();
    }

    //#############################################

    public function getMenuArray()
    {
        $menuArray = parent::getMenuArray();

        try {

            if (!Mage::getSingleton('admin/session')->isAllowed('m2epro')) {
                return $menuArray;
            }

            $tempTitle = Mage::helper('M2ePro/Module')->getMenuRootNodeLabel();
            !empty($tempTitle) && $menuArray['m2epro']['label'] = $tempTitle;

            // Add wizard menu item
            //---------------------------------
            /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
            $wizardHelper = Mage::helper('M2ePro/Wizard');

            if (!$wizardHelper->isInstallationFinished()) {

                unset($menuArray['m2epro']['children']);
                unset($menuArray['m2epro']['click']);

                $wizardEdition  = $wizardHelper->getEdition();
                $installatorNick = $wizardHelper->getNick($wizardHelper->getInstallatorWizard());

                $menuArray['m2epro']['url'] = $this->getUrl(
                    'M2ePro/adminhtml_'.$wizardEdition.'_'.$installatorNick.'/index'
                );
                $menuArray['m2epro']['last'] = true;

                return $menuArray;
            }
            //---------------------------------

            if (!Mage::helper('M2ePro/Component_Ebay')->isActive()) {
                unset($menuArray['m2epro']['children']['templates']['children']['general']);
                unset($menuArray['m2epro']['children']['templates']['children']['description']);
                unset($menuArray['m2epro']['children']['communication']);
            }

            if (!Mage::helper('M2ePro/Magento')->isGoCustomEdition()) {
                unset($menuArray['m2epro']['children']['listings']['children']['listing_quick']);
            }

            // Set documentation redirect url
            //---------------------------------
            $menuArray['m2epro']['children']['help']['children']['doc']['click'] =
                    "window.open(this.href, '_blank'); return false;";
            $menuArray['m2epro']['children']['help']['children']['doc']['url'] =
                    Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/documentation/', 'baseurl');
            //---------------------------------

            // Set video tutorials redirect url
            //---------------------------------
            $menuArray['m2epro']['children']['help']['children']['tutorial']['click'] =
                    "window.open(this.href, '_blank'); return false;";
            $menuArray['m2epro']['children']['help']['children']['tutorial']['url'] =
                    Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/video_tutorials/', 'baseurl');
            //---------------------------------

        } catch (Exception $exception) {}

        return $menuArray;
    }

    //#############################################
}