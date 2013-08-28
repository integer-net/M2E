<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Helper_Wizard extends Mage_Core_Helper_Abstract
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_ACTIVE      = 1;
    const STATUS_COMPLETED   = 2;
    const STATUS_SKIPPED     = 3;

    const KEY_STATUS         = 'status';
    const KEY_STEP           = 'step';
    const KEY_PRIORITY       = 'priority';

    //storage
    private $cache = array();

    // ########################################

    public function getEdition()
    {
        return 'wizard';
    }

    // ########################################

    /**
     * Wizards Factory
     * @param string $nick
     * @return Ess_M2ePro_Model_Wizard
     */
    public function getWizard($nick)
    {
        return Mage::getSingleton('M2ePro/'.$this->getEdition().'_'.ucfirst($nick));
    }

    // ########################################

    public function isNotStarted($nick)
    {
        return $this->getStatus($nick) == self::STATUS_NOT_STARTED &&
               $this->getWizard($nick)->isActive();
    }

    public function isActive($nick)
    {
        return $this->getStatus($nick) == self::STATUS_ACTIVE &&
               $this->getWizard($nick)->isActive();
    }

    public function isCompleted($nick)
    {
        return $this->getStatus($nick) == self::STATUS_COMPLETED;
    }

    public function isSkipped($nick)
    {
        return $this->getStatus($nick) == self::STATUS_SKIPPED;
    }

    public function isFinished($nick)
    {
        return $this->isCompleted($nick) || $this->isSkipped($nick);
    }

    // ########################################

    private function getConfigValue($nick, $key)
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/wizard/'.$nick.'/', $key);
    }

    private function setConfigValue($nick, $key, $value)
    {
        $this->clearCache();
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/wizard/'.$nick.'/', $key, $value);
    }

    // --------------------------------------------

    private function getConfigRowsByKeyAndValue($key, $value = NULL)
    {
        $cacheKey = sha1($key.'_'.json_encode($value));
        if (!isset($this->cache[$cacheKey])) {
            /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
            $collection = Mage::helper('M2ePro/Module')->getConfig()->getCollection();
            $collection->addFieldToFilter('`group`', array('like' => '/wizard/%'));
            $collection->addFieldToFilter('`key`', $key);
            !is_null($value) && $collection->addFieldToFilter('`value`', $value);

            $temp = $collection->toArray();

            $this->cache[$cacheKey] = $temp['items'];
        }

        return $this->cache[$cacheKey];
    }

    // ########################################

    public function getStatus($nick)
    {
        return $this->getConfigValue($nick, self::KEY_STATUS);
    }

    public function setStatus($nick, $status = self::STATUS_NOT_STARTED)
    {
        $this->setConfigValue($nick, self::KEY_STATUS, $status);
    }

    public function getStep($nick)
    {
        return $this->getConfigValue($nick, self::KEY_STEP);
    }

    public function setStep($nick, $step = NULL)
    {
        $this->setConfigValue($nick, self::KEY_STEP, $step);
    }

    public function getPriority($nick)
    {
        return $this->getConfigValue($nick, self::KEY_PRIORITY);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Wizard
     */
    public function getInstallatorWizard()
    {
        $allWizards = $this->getAllWizards();
        return reset($allWizards);
    }

    /**
     * @param bool $includeInstaller
     * @return null|Ess_M2ePro_Model_Wizard
     */
    public function getActiveWizard($includeInstaller = true)
    {
        $wizards = $this->getAllWizards($includeInstaller);

        /** @var $wizard Ess_M2ePro_Model_Wizard */
        foreach ($wizards as $wizard) {
            if ($this->isNotStarted($this->getNick($wizard)) || $this->isActive($this->getNick($wizard))) {
                return $wizard;
            }
        }

        return null;
    }

    // ------------------------------------------------------

    private function getAllWizards($includeInstaller = true)
    {
        $temp = $this->getConfigRowsByKeyAndValue(self::KEY_PRIORITY);

        $result = array();
        foreach ($temp as $item) {
            $wizardNick = trim(str_replace('/wizard/','',$item['group']),'/');
            $result[$item['value']] = $this->getWizard($wizardNick);
        }

        ksort($result);

        if (!$includeInstaller) {
            $result = array_slice($result,1);
        }

        return $result;
    }

    // ########################################

    public function isInstallationNotStarted()
    {
        $installer = $this->getInstallatorWizard();
        return $this->isNotStarted($this->getNick($installer));
    }

    public function isInstallationActive()
    {
        $installer = $this->getInstallatorWizard();
        return $this->isActive($this->getNick($installer));
    }

    public function isInstallationCompleted()
    {
        $installer = $this->getInstallatorWizard();
        return $this->isCompleted($this->getNick($installer));
    }

    public function isInstallationSkipped()
    {
        $installer = $this->getInstallatorWizard();
        return $this->isSkipped($this->getNick($installer));
    }

    public function isInstallationFinished()
    {
        return $this->isInstallationCompleted() || $this->isInstallationSkipped();
    }

    // ---------------------------------------------

    public function getActiveUpgrade()
    {
        return $this->getActiveWizard(false);
    }

    // ---------------------------------------------

    public function getActiveStep()
    {
        $activeWizard = $this->getActiveUpgrade();

        if (!$activeWizard) {
            return null;
        }

        return $this->getStep($this->getNick($activeWizard));
    }

    // ########################################

    private function clearCache()
    {
        $this->cache = array();
    }

    public function clearMenuCache()
    {
        Mage::app()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array(Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS)
        );
    }

    // ########################################

    /**
     * @param string $block
     * @param string $nick
     * @return Mage_Core_Block_Abstract
     * */

    public function createBlock($block,$nick = '')
    {
        return Mage::getSingleton('core/layout')->createBlock(
            'M2ePro/adminhtml_'.$this->getEdition().'_'.$nick.'_'.$block,
            null,
            array('nick' => $nick)
        );
    }

    // ########################################

    public function addWizardHandlerJs()
    {
        Mage::getSingleton('core/layout')->getBlock('head')->addJs(
            'M2ePro/'.ucfirst($this->getEdition()).'Handler.js'
        );
    }

    // ########################################

    public function getNick($wizard)
    {
        $parts = explode('_',get_class($wizard));
        $nick = array_pop($parts);
        $nick{0} = strtolower($nick{0});
        return $nick;
    }

    // ########################################
}