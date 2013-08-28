<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Other_LockItem extends Ess_M2ePro_Model_LockItem
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

        $this->setNick('listing_other_'.$params['component']);

        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')->getConfig()
                                        ->getGroupValue('/listings/lockItem/','max_deactivate_time');
        $this->setMaxDeactivateTime($maxDeactivateTime);

        parent::__construct($params);
    }

    //####################################
}