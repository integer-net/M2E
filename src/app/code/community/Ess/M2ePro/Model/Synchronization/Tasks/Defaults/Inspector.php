<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_Inspector extends Ess_M2ePro_Model_Synchronization_Tasks
{
    //####################################

    public function process()
    {
        $inspectorNick = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/','mode'
        );

        if (!in_array($inspectorNick, array('circle'))) {
            return;
        }

        $mode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/'.$inspectorNick.'/','mode'
        );

        if (!$mode) {
            return;
        }

        Mage::getModel('M2ePro/Synchronization_Tasks_Defaults_Inspector_'.ucfirst($inspectorNick))->process();
    }

   //####################################
}