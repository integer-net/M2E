<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Server extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getHost()
    {
        $domain = $this->getDomain();
        return $domain == '' ? $this->getIp() : $domain;
    }

    //-----------------------------------------

    public function getDomain()
    {
        $backupDomain = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cache/location_info/', 'domain');

        if (!is_null($backupDomain)) {
            strpos($backupDomain,'www.') === 0 && $backupDomain = substr($backupDomain,4);
            return strtolower(trim($backupDomain));
        }

        $serverDomain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : NULL;

        if (!is_null($serverDomain)) {
            strpos($serverDomain,'www.') === 0 && $serverDomain = substr($serverDomain,4);
            return strtolower(trim($serverDomain));
        }

        throw new Exception('Server domain is not defined');
    }

    public function getIp()
    {
        $backupIp = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cache/location_info/', 'ip');

        if (!is_null($backupIp)) {
            return strtolower(trim($backupIp));
        }

        $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : NULL;
        is_null($serverIp) && $serverIp = isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : NULL;

        if (!is_null($serverIp)) {
            return strtolower(trim($serverIp));
        }

        throw new Exception('Server IP is not defined');
    }

    public function getBaseDirectory()
    {
        $backupDirectory = Mage::helper('M2ePro/Module')->getConfig()
                                    ->getGroupValue('/cache/location_info/', 'directory');

        if (!is_null($backupDirectory)) {
            return $backupDirectory;
        }

        return Mage::getBaseDir();
    }

    // ########################################

    public function isDeveloper()
    {
        return (bool)Mage::getIsDeveloperMode();
    }

    public function getSystem()
    {
        return php_uname();
    }

    public function getBaseUrl()
    {
        return str_replace('index.php/','',Mage::getBaseUrl());
    }

    // ########################################

    public function getPhpVersion()
    {
        return @phpversion();
    }

    public function getPhpApiName()
    {
        return @php_sapi_name();
    }

    public function getPhpSettings()
    {
        return array(
            'memory_limit' => $this->getMemoryLimit(),
            'max_execution_time' => @ini_get('max_execution_time'),
            'phpinfo' => $this->getPhpInfoArray()
        );
    }

    public function getMemoryLimit($inMegabytes = true)
    {
        $memoryLimit = trim(ini_get('memory_limit'));

        if ($memoryLimit == '') {
            return 0;
        }

        $lastMemoryLimitLetter = strtolower(substr($memoryLimit, -1));
        switch($lastMemoryLimitLetter) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }

        if ($inMegabytes) {
            $memoryLimit /= 1024 * 1024;
        }

        return $memoryLimit;
    }

    public function getPhpInfoArray()
    {
        try {

            ob_start(); phpinfo(INFO_ALL);

            $pi = preg_replace(
            array(
                '#^.*<body>(.*)</body>.*$#m', '#<h2>PHP License</h2>.*$#ms',
                '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
                "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
                '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
                "# +#", '#<tr>#', '#</tr>#'),
            array(
                '$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
                '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
                "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
                '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
                '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
                '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'
            ), ob_get_clean()
            );

            $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
            unset($sections[0]);

            $pi = array();
            foreach ($sections as $section) {
                $n = substr($section, 0, strpos($section, '</h2>'));
                preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
                    $section,
                    $askapache,
                    PREG_SET_ORDER
                );
                foreach ($askapache as $m) {
                    if (!isset($m[0]) || !isset($m[1]) || !isset($m[2])) {
                        continue;
                    }
                    $pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
                }
            }

        } catch (Exception $exception) {
            return array();
        }

        return $pi;
    }

    // ########################################

    public function getMysqlVersion()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->getServerVersion();
    }

    public function getMysqlApiName()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read')->getConnection();
        return $connection instanceof PDO ? $connection->getAttribute(PDO::ATTR_CLIENT_VERSION) : 'N/A';
    }

    public function getMysqlSettings()
    {
        $sqlQuery = "SHOW VARIABLES
                     WHERE `Variable_name` IN ('connect_timeout','wait_timeout')";

        $settingsArray = Mage::getSingleton('core/resource')
                            ->getConnection('core_read')
                            ->fetchAll($sqlQuery);

        $settings = array();
        foreach ($settingsArray as $settingItem) {
            $settings[$settingItem['Variable_name']] = $settingItem['Value'];
        }

        $phpInfo = $this->getPhpInfoArray();
        $settings = array_merge($settings,isset($phpInfo['mysql'])?$phpInfo['mysql']:array());

        return $settings;
    }

    public function getMysqlTotals()
    {
        $moduleTables = Mage::helper('M2ePro/Module')->getMySqlTables();
        $magentoTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $totalRecords = 0;
        foreach ($moduleTables as $moduleTable) {
            $moduleTable = Mage::getSingleton('core/resource')->getTableName($moduleTable);
            $dbSelect = $connRead->select()->from($moduleTable,new Zend_Db_Expr('COUNT(*)'));
            $totalRecords += (int)$connRead->fetchOne($dbSelect);
        }

        return array(
            'magento_tables' => count($magentoTables),
            'module_tables' => count($moduleTables),
            'module_records' => $totalRecords
        );
    }

    // ########################################

    public function setMemoryLimit($maxSize = 512)
    {
        $minSize = 32;
        $currentMemoryLimit = Mage::helper('M2ePro/Server')->getMemoryLimit();

        if ($maxSize < $minSize || (int)$currentMemoryLimit >= $maxSize) {
            return false;
        }

        for ($i=$minSize; $i<=$maxSize; $i*=2) {

            if (@ini_set('memory_limit',"{$i}M") === false) {
                if ($i == $minSize) {
                    return false;
                } else {
                    return $i/2;
                }
            }
        }

        return true;
    }

    public function updateMySqlConnection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        try {
            $connRead->query('SELECT 1');
        } catch (Exception $exception) {
            $connRead->closeConnection();
        }
    }

    // ########################################
}