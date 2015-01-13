<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Adminhtml_Wizard_MigrationToV6Controller
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getNick()
    {
        return Ess_M2ePro_Helper_Module::WIZARD_MIGRATION_NICK;
    }

    //#############################################

    protected function getCustomViewNick()
    {
        return count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0 ?
                Ess_M2ePro_Helper_View_Ebay::NICK : Ess_M2ePro_Helper_View_Common::NICK;
    }

    protected function _isAllowed()
    {
        $menuNickTemp = count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0 ?
                            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK :
                            Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK;
        return Mage::getSingleton('admin/session')->isAllowed($menuNickTemp);
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);

        if (count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) > 0) {
            $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Ebay::NICK);
            $tempResult->_title(Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel());
        } else {
            $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Common::NICK);
            $tempResult->_title(Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel());
        }

        return $tempResult;
    }

    //#############################################

    public function indexAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/welcome');
        }

        if ($wizardHelper->isActive($this->getNick())) {
            return $this->_redirect('*/*/installation');
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        if ($this->getCustomViewNick() == Ess_M2ePro_Helper_View_Ebay::NICK) {
            return $this->_redirect('*/adminhtml_ebay_listing/index');
        }

        return $this->_redirect('*/adminhtml_common_listing/index');
    }

    public function installationAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        Mage::getSingleton('M2ePro/Wizard_MigrationToV6')->removeEmptySteps();

        if ($wizardHelper->isFinished($this->getNick()) ||
            $wizardHelper->isNotStarted($this->getNick())) {
            return $this->_redirect('*/*/index');
        }

        if (!$wizardHelper->getStep($this->getNick())) {
            $wizardHelper->setStep(
                $this->getNick(),
                $wizardHelper->getWizard($this->getNick())->getFirstStep()
            );
        }

        $currentStep = $wizardHelper->getStep($this->getNick());

        $this->_forward($currentStep);
    }

    public function saveSellingFormatCurrenciesAction()
    {
        $postParam = $this->getRequest()->getPost('form_data');
        $nextStep = Mage::helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getNextStep();

        $response = array(
            'success' => true,
            'next_step' => $nextStep
        );
        $this->getResponse()->setBody(json_encode($response));

        if (is_null($postParam)) {
            return;
        }

        parse_str($postParam, $data);

        !empty($data['ebay']) && $this->saveEbaySellingFormatData($data['ebay']);
        !empty($data['amazon']) && $this->saveAmazonSellingFormatData($data['amazon']);
        !empty($data['buy']) && $this->saveBuySellingFormatData($data['buy']);
        !empty($data['play']) && $this->savePlaySellingFormatData($data['play']);
    }

    //#############################################

    private function renderSimpleStep()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $currentStep = $wizardHelper->getStep($this->getNick());

        return $this->_initAction()
            ->_addContent($wizardHelper->createBlock('installation_'.$currentStep,$this->getNick()))
            ->renderLayout();
    }

    //#############################################

    public function introAction()
    {
        $this->renderSimpleStep();
    }

    public function sellingFormatCurrenciesAction()
    {
        $this->renderSimpleStep();
    }

    public function notificationsAction()
    {
        $this->renderSimpleStep();
    }

    //#############################################

    protected function saveEbaySellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'start_price_coefficient',
            'buyitnow_price_coefficient',
            'reserve_price_coefficient'
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_ebay_template_selling_format');
    }

    protected function saveAmazonSellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'price_coefficient',
            'sale_price_coefficient',
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_amazon_template_selling_format');
    }

    protected function saveBuySellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'price_coefficient',
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_buy_template_selling_format');
    }

    protected function savePlaySellingFormatData($data)
    {
        if (empty($data)) {
            return;
        }

        $coefficientIds = array(
            'price_gbr_coefficient',
            'price_euro_coefficient',
        );

        $this->saveSellingFormatData($data, $coefficientIds, 'm2epro_play_template_selling_format');
    }

    // ------------------------------------------

    protected function saveSellingFormatData($data, $coefficientIds, $tableName)
    {
        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        foreach ($data as $coefficient => $value) {
            foreach ($coefficientIds as $coefficientId) {
                if (strpos($coefficient, $coefficientId) !== 0) {
                    continue;
                }

                $templateId = (int)str_replace($coefficientId.'_', '', $coefficient);

                $connWrite->update(
                    $tableName,
                    array($coefficientId => $value),
                    array('template_selling_format_id = ?' => $templateId)
                );
            }
        }
    }

    //#############################################
}