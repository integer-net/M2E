<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid
    extends Ess_M2ePro_Block_Adminhtml_Common_Template_Grid
{
    protected $nick = Ess_M2ePro_Helper_Component_Amazon::NICK;

    // ##########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonAmazonTemplateGrid');
        //------------------------------
    }

    // ##########################################

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/adminhtml_common_template/edit',
            array(
                'id' => $row->getData('template_id'),
                'type' => $row->getData('type'),
                'back' => 1
            )
        );
    }

    // ##########################################
}