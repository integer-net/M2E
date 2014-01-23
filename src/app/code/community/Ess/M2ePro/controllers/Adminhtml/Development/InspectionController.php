<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_InspectionController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    public function phpInfoAction()
    {
        phpinfo();
    }

    //#############################################

    public function cronScheduleTableAction()
    {
        $this->loadLayout();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_development_inspection_cronScheduleTable_grid');
            return $this->getResponse()->setBody($block->toHtml());
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_development_inspection_cronScheduleTable');

        $this->_addContent($block);
        $this->renderLayout();
    }

    public function cronScheduleTableShowMessagesAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            return $this->_redirect('*/*/cronScheduleTable');
        }

        return $this->getResponse()->setBody(Mage::getModel('cron/schedule')->load($id)->getMessages());
    }

    //#############################################
}