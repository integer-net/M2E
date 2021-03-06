<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Gallery
    extends Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('rendererDescriptionGallery');
        // ---------------------------------------

        $this->setTemplate('M2ePro/renderer/description/gallery.phtml');
    }

    //########################################
}