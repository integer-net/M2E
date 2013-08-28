<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_LockItem extends Ess_M2ePro_Model_Abstract
{
    private $nick = 'undefined';
    private $maxDeactivateTime = 0;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LockItem');
    }

    //####################################

    public function setNick($nick)
    {
        $nick = (string)$nick;

        if ($nick == '') {
            throw new Exception('Wrong nick of lock item.');
        }

        $this->nick = $nick;
    }

    public function getNick()
    {
        return $this->nick;
    }

    //-----------------------------------

    public function setMaxDeactivateTime($maxDeactivateTime)
    {
        $maxDeactivateTime = (int)$maxDeactivateTime;

        if ($maxDeactivateTime <= 0) {
            throw new Exception('Wrong max deactivate lock item time.');
        }

        $this->maxDeactivateTime = $maxDeactivateTime;
    }

    public function getMaxDeactivateTime()
    {
        return $this->maxDeactivateTime;
    }

    //####################################

    public function create()
    {
        if ($this->isExist()) {
            return false;
        }

        $data = array(
            'update_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        $dataForAdd = array(
            'nick' => $this->nick,
            'data' => json_encode($data)
        );

        Mage::getModel('M2ePro/LockItem')->setData($dataForAdd)->save();

        return true;
    }

    public function remove()
    {
        if (!$this->isExist()) {
            return false;
        }

        $temp = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');
        $temp->getId() && $temp->delete();

        return true;
    }

    //-----------------------------------

    public function isExist()
    {
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return false;
        }

        $curTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $modificationTimestamp = strtotime($lockModel->getData('update_date'));

        if ($modificationTimestamp < $curTimeStamp - $this->maxDeactivateTime) {
            $lockModel->delete();
            return false;
        }

        return true;
    }

    public function activate()
    {
        if (!$this->isExist()) {
            return false;
        }

        $this->setContentData('update_date',Mage::helper('M2ePro')->getCurrentGmtDate());

        return true;
    }

    //####################################

    public function setContentData($key, $value)
    {
        if (!$this->isExist()) {
            return false;
        }

        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        $data = json_decode($lockModel->getData('data'),true);
        $data[$key] = $value;
        $data = json_encode($data);

        $lockModel->addData(array('data'=>$data))->save();
        return true;
    }

    public function getContentData($key)
    {
        if (!$this->isExist()) {
            return NULL;
        }

        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');
        $data = json_decode($lockModel->getData('data'),true);

        if (isset($data[$key])) {
            return $data[$key];
        }

        return NULL;
    }

    //-----------------------------------

    public function makeShutdownFunction()
    {
        $functionCode = "Mage::getModel('M2ePro/LockItem')->load('".$this->nick."','nick')->remove();";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}