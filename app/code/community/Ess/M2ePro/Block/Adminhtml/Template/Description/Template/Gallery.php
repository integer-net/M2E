<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Template_Description_Template_Gallery
    extends Ess_M2ePro_Block_Adminhtml_Template_Description_Template_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateDescriptionTemplateGallery');
        //------------------------------

        $this->setTemplate('M2ePro/template/description/template/gallery.phtml');
    }

    // ####################################
}