<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Observer_Abstract
{
    /**
     * @var null|Varien_Event_Observer
     */
    private $eventObserver = NULL;

    //####################################

    public function canProcess()
    {
        return true;
    }

    abstract public function process();

    //####################################

    public function beforeProcess() {}
    public function afterProcess() {}

    //####################################

    /**
     * @param Varien_Event_Observer $eventObserver
     */
    public function setEventObserver(Varien_Event_Observer $eventObserver)
    {
        $this->eventObserver = $eventObserver;
    }

    /**
     * @return Varien_Event_Observer
     * @throws LogicException
     */
    protected function getEventObserver()
    {
        if (!($this->eventObserver instanceof Varien_Event_Observer)) {
            throw new LogicException('Property "eventObserver" should be set first.');
        }

        return $this->eventObserver;
    }

    //####################################

    /**
     * @return Varien_Event
     */
    protected function getEvent()
    {
        return $this->getEventObserver()->getEvent();
    }

    //####################################
}