<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation_Notifications_Content
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationNotifications');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/installation/notifications.phtml');
    }

    // ########################################

    protected function _toHtml()
    {
        $breadcrumbBlockHtml = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_wizard_migrationToV6_breadcrumb')
            ->toHtml();

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $migrationTable = Mage::getSingleton('core/resource')->getTableName('m2epro_migration_v6');

        $html = $connRead->select()->from($migrationTable,'data')
            ->where('`component` = \'*\'')->where('`group` = \'notes\'')
            ->query()->fetchColumn();

        if (empty($html)) {
            $this->setData('save_migration_notes', true);
            $html = parent::_toHtml();
        }

        if ($this->getData('save_migration_notes')) {
            /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $migrationTable = Mage::getSingleton('core/resource')->getTableName('m2epro_migration_v6');

            $migrationTableData = array(
                'component' => '*',
                'group' => 'notes',
                'data' => $html
            );

            $connWrite->insert($migrationTable,$migrationTableData);
        }

        return $breadcrumbBlockHtml . $html;
    }

    // ########################################
}