<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_Help extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/order/help.phtml');
    }

    public function getContainerId()
    {
        return 'block_notice_amazon_orders_list';
    }
}