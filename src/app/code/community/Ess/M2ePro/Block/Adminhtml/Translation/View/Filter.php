<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Translation_View_Filter extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('translationViewFilter');
        //------------------------------

        $this->setTemplate('M2ePro/translation/view/filter.phtml');
    }

    protected function _beforeToHtml()
    {
        $readConn = Mage::getResourceModel('core/config')->getReadConnection();
        $groupsSelect = $readConn->select()
            ->from(Mage::getResourceModel('M2ePro/Translation_Text')->getMainTable(),
            new Zend_Db_Expr('DISTINCT `group`'));

        $groups = array();
        foreach ($readConn->fetchAll($groupsSelect) as $group) {
            $groups[] = $group['group'];
        }
        $this->groups = $groups;

        $this->groupFilter = $this->getRequest()->getParam('group');
        $this->statusFilter = $this->getRequest()->getParam('status');
    }
}