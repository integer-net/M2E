<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_LockItem extends Ess_M2ePro_Model_LockItem
{
    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        if (!isset($params['component'])) {
            throw new Exception('Listing component is not defined.');
        }

        if (!isset($params['id'])) {
            throw new Exception('Listing id is not defined.');
        }

        $this->setNick('listing_'.$params['component'].'_'.$params['id']);

        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')->getConfig()
                                        ->getGroupValue('/listings/lockItem/','max_deactivate_time');
        $this->setMaxDeactivateTime($maxDeactivateTime);

        parent::__construct($params);
    }

    //####################################
}