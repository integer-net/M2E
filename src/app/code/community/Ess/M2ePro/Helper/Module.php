<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module extends Mage_Core_Helper_Abstract
{
    const SERVER_LOCK_NO = 0;
    const SERVER_LOCK_YES = 1;

    const SERVER_MESSAGE_TYPE_NOTICE = 0;
    const SERVER_MESSAGE_TYPE_ERROR = 1;
    const SERVER_MESSAGE_TYPE_WARNING = 2;
    const SERVER_MESSAGE_TYPE_SUCCESS = 3;

    const WIZARD_MIGRATION_NICK = 'migrationToV6';

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Config_Module
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Module');
    }

    /**
     * @return Ess_M2ePro_Model_Config_Cache
     */
    public function getCacheConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Cache');
    }

    /**
     * @return Ess_M2ePro_Model_Config_Synchronization
     */
    public function getSynchronizationConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Synchronization');
    }

    // ########################################

    public function getName()
    {
        return 'm2epro';
    }

    public function getVersion()
    {
        $version = (string)Mage::getConfig()->getNode('modules/Ess_M2ePro/version');
        $version = strtolower($version);

        if (Mage::helper('M2ePro/Data_Cache')->getValue('MODULE_VERSION_UPDATER') === false) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/modules/',$this->getName(),$version.'.r'.$this->getRevision()
            );
            Mage::helper('M2ePro/Data_Cache')->setValue('MODULE_VERSION_UPDATER',array(),array(),60*60*24);
        }

        return $version;
    }

    public function getRevision()
    {
        $revision = '6425';

        if ($revision == str_replace('|','#','|REVISION|')) {
            $revision = (int)exec('svnversion');
            $revision == 0 && $revision = 'N/A';
            $revision .= '-dev';
        }

        return $revision;
    }

    //----------------------------------------

    public function getVersionWithRevision()
    {
        return $this->getVersion().'r'.$this->getRevision();
    }

    // ########################################

    public function getInstallationKey()
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'installation_key'
        );
    }

    public function isMigrationWizardFinished()
    {
        return Mage::helper('M2ePro/Module_Wizard')->isFinished(
            self::WIZARD_MIGRATION_NICK
        );
    }

    // ########################################

    public function isLockedByServer()
    {
        $lock = (int)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'lock'
        );

        $validValues = array(self::SERVER_LOCK_NO, self::SERVER_LOCK_YES);

        if (in_array($lock,$validValues)) {
            return $lock;
        }

        return self::SERVER_LOCK_NO;
    }

    // -------------------------------------------

    public function getServerMessages()
    {
        $messages = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'messages'
        );

        $messages = (!is_null($messages) && $messages != '') ?
                    (array)json_decode((string)$messages,true) :
                    array();

        $messages = array_filter($messages,array($this,'getServerMessagesFilterModuleMessages'));
        !is_array($messages) && $messages = array();

        return $messages;
    }

    public function getServerMessagesFilterModuleMessages($message)
    {
        if (!isset($message['text']) || !isset($message['type'])) {
            return false;
        }

        return true;
    }

    // ########################################

    public function getFoldersAndFiles()
    {
        $paths = array(
            'app/code/community/Ess/',
            'app/code/community/Ess/M2ePro/*',

            // todo uncomment when translations will be available
            //'app/locale/*/Ess_M2ePro.csv',
            'app/etc/modules/Ess_M2ePro.xml',
            'app/design/adminhtml/default/default/layout/M2ePro.xml',

            'js/M2ePro/*',
            'skin/adminhtml/default/default/M2ePro/*',
            'skin/adminhtml/default/enterprise/M2ePro/*',
            'app/design/adminhtml/default/default/template/M2ePro/*'
        );

        return $paths;
    }

    public function getRequirementsInfo()
    {
        $clientPhpData = Mage::helper('M2ePro/Client')->getPhpSettings();

        $requirements = array (

            'php_version' => array(
                'title' => $this->__('PHP Version'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => '5.3.0'
                ),
                'current' => array(
                    'value' => Mage::helper('M2ePro/Client')->getPhpVersion(),
                    'status' => true
                )
            ),

            'memory_limit' => array(
                'title' => $this->__('Memory Limit'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => '256 MB'
                ),
                'current' => array(
                    'value' => (int)$clientPhpData['memory_limit'] . ' MB',
                    'status' => true
                )
            ),

            'magento_version' => array(
                'title' => $this->__('Magento Version'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => (Mage::helper('M2ePro/Magento')->isGoEdition()           ? '1.9.0.0' :
                               (Mage::helper('M2ePro/Magento')->isEnterpriseEdition()   ? '1.7.0.0' :
                               (Mage::helper('M2ePro/Magento')->isProfessionalEdition() ? '1.7.0.0' : '1.4.1.0')))
                ),
                'current' => array(
                    'value' => Mage::helper('M2ePro/Magento')->getVersion(false),
                    'status' => true
                )
            ),

            'max_execution_time' => array(
                'title' => $this->__('Max Execution Time'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => '360 sec'
                ),
                'current' => array(
                    'value' => (int)$clientPhpData['max_execution_time'] . ' sec',
                    'status' => true
                )
            )
        );

        foreach ($requirements as &$requirement) {
            $requirement['current']['status'] = version_compare(
                $requirement['current']['value'],
                $requirement['condition']['value'],
                $requirement['condition']['sign']
            );
        }

        return $requirements;
    }

    // ########################################

    public function getUnWritableDirectories()
    {
        $directoriesForCheck = array();
        foreach ($this->getFoldersAndFiles() as $item) {

            $fullDirPath = Mage::getBaseDir().DS.$item;

            if (preg_match('/\*$/',$item)) {
                $fullDirPath = preg_replace('/\*$/', '', $fullDirPath);
                $directoriesForCheck = array_merge($directoriesForCheck, $this->getDirectories($fullDirPath));
            }

            $directoriesForCheck[] = dirname($fullDirPath);
            is_dir($fullDirPath) && $directoriesForCheck[] = rtrim($fullDirPath, '/\\');
        }
        $directoriesForCheck = array_unique($directoriesForCheck);

        $unWritableDirs = array();
        foreach ($directoriesForCheck as $directory) {
            !is_dir_writeable($directory) && $unWritableDirs[] = $directory;
        }

        return $unWritableDirs;
    }

    private function getDirectories($dirPath)
    {
        $directoryIterator = new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        $directories = array();
        foreach($iterator as $path) {
            $path->isDir() && $directories[] = rtrim($path->getPathname(),'/\\');
        }

        return $directories;
    }

    // ########################################

    public function clearConfigCache()
    {
        $this->getCacheConfig()->clear();
    }

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache')->removeAllValues();
    }

    // ########################################
}