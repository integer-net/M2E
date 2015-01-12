<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Notes extends Mage_Adminhtml_Block_Widget_Container
{
    // #################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMigrationToV6Breadcrumb');
        //------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__(
            'M2E Pro Migration to v. %version%', Mage::helper('M2ePro/Module')->getVersion()
        );

        $this->setTemplate('widget/form/container.phtml');
    }

    // #################################################

    protected function _toHtml()
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $migrationTable = Mage::getSingleton('core/resource')->getTableName('m2epro_migration_v6');

        $html = $connRead->select()->from($migrationTable,'data')
            ->where('`component` = \'*\'')->where('`group` = \'notes\'')
            ->query()->fetchColumn();

        return parent::_toHtml() . $html;
    }

    // #################################################
}