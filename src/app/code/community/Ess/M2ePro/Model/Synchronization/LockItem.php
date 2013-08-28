<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_LockItem extends Ess_M2ePro_Model_LockItem
{
    const CHECK_KILL_INTERVAL = 60;

    /** @var Ess_M2ePro_Model_Synchronization_Run */
    private $synchRunObj = NULL;
    private $lastCheckKillTime = 0;

    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $this->setNick('synchronization');

        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')->getConfig()
                                        ->getGroupValue('/synchronization/lockItem/','max_deactivate_time');
        $this->setMaxDeactivateTime($maxDeactivateTime);

        parent::__construct($params);
    }

    //####################################

    public function setTitle($title)
    {
        $this->setContentData('info_title',$title);
    }

    public function setPercents($percents)
    {
        (int)$percents < 0 && $percents = 0;
        (int)$percents > 100 && $percents = 100;
        $this->setContentData('info_percents',(int)$percents);
    }

    public function setStatus($status)
    {
        $this->setContentData('info_status',$status);
    }

    //-----------------------------------

    public function getTitle()
    {
        return $this->getContentData('info_title');
    }

    public function getPercents()
    {
        return (int)$this->getContentData('info_percents');
    }

    public function getStatus()
    {
        return $this->getContentData('info_status');
    }

    //####################################

    public function setSynchRunObj($synchRunObj)
    {
        $this->synchRunObj = $synchRunObj;
    }

    public function activate()
    {
        parent::activate();

        $now = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        if($now - $this->lastCheckKillTime < self::CHECK_KILL_INTERVAL) {
            return;
        }

        $this->lastCheckKillTime = $now;

        if (is_null($this->synchRunObj)) {
            return;
        }

        $synchId = $this->synchRunObj->getId();

        try {

            if (empty($synchId)) {
                throw new Exception();
            }

            $tempSynchObj = Mage::getModel('M2ePro/Synchronization_Run')
                                        ->loadInstance($synchId);

        } catch (Exception $exception) {
            exit();
        }

        if ($tempSynchObj->getData('kill_now')) {
            exit();
        }
    }

    //####################################

    public function makeShutdownFunction()
    {
        $functionCode = "Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->remove();";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}