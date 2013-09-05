<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
    }

    /**
     * Get absolute path to template
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array(
            '_relative' => true,
            '_area' => 'adminhtml',
            '_package' => 'default',
            '_theme' => 'default'
        );

        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        return $templateName;
    }

    // ####################################
}