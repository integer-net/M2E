<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_Locker
{
    protected $needRemove = false;
    protected $lockItem = NULL;

    // ########################################

    public function update()
    {
        if (!$this->getLockItem()->isExist()) {
            $this->create();
        }
        $this->getLockItem()->activate();
    }

    public function create()
    {
        $this->getLockItem()->create();
        $this->getLockItem()->makeShutdownFunction();
        $this->needRemove = true;
    }

    public function remove()
    {
        if ($this->needRemove && $this->getLockItem()->isExist()) {
             $this->getLockItem()->remove();
        }
        $this->needRemove = false;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_LockItem
     * @throws Exception
     */
    abstract protected function getLockItem();

    // ########################################
}