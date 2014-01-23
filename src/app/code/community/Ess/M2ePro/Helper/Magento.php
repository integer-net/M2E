<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento extends Mage_Core_Helper_Abstract
{
    // ########################################

    public function getName()
    {
        return 'magento';
    }

    public function getVersion($asArray = false)
    {
        $versionString = Mage::getVersion();
        return $asArray ? explode('.',$versionString) : $versionString;
    }

    public function getRevision()
    {
        return 'undefined';
    }

    // ########################################

    public function getEditionName()
    {
        if ($this->isProfessionalEdition()) {
            return 'professional';
        }
        if ($this->isEnterpriseEdition()) {
            return 'enterprise';
        }
        if ($this->isCommunityEdition()) {
            return 'community';
        }

        if ($this->isGoUsEdition()) {
            return 'magento go US';
        }
        if ($this->isGoUkEdition()) {
            return 'magento go UK';
        }
        if ($this->isGoAuEdition()) {
            return 'magento go AU';
        }

        if ($this->isGoEdition()) {
            return 'magento go';
        }

        return 'undefined';
    }

    //----------------------------------------

    public function isGoEdition()
    {
        return class_exists('Saas_Db',false);
    }

    public function isProfessionalEdition()
    {
        if ($this->isGoEdition()) {
            return false;
        }

        $modules = $this->getModules();
        if (in_array('Professional_License',$modules)) {
            return true;
        }

        return false;
    }

    public function isEnterpriseEdition()
    {
        if ($this->isGoEdition()) {
            return false;
        }

        $modules = $this->getModules();
        if (in_array('Enterprise_License',$modules)) {
            return true;
        }

        return false;
    }

    public function isCommunityEdition()
    {
        if ($this->isGoEdition()) {
            return false;
        }

        if ($this->isProfessionalEdition()) {
            return false;
        }

        if ($this->isEnterpriseEdition()) {
            return false;
        }

        return true;
    }

    //----------------------------------------

    public function isGoUsEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_us';
    }

    public function isGoUkEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_gb';
    }

    public function isGoAuEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_au';
    }

    //----------------------------------------

    public function isGoCustomEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        return $this->isGoUsEdition() ||
               $this->isGoUkEdition() ||
               $this->isGoAuEdition();
    }

    // ########################################

    public function getMySqlTables()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->listTables();
    }

    public function getDatabaseTablesPrefix()
    {
        return (string)Mage::getConfig()->getTablePrefix();
    }

    public function getDatabaseName()
    {
        return (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
    }

    // ########################################

    public function getModules()
    {
        return array_keys((array)Mage::getConfig()->getNode('modules')->children());
    }

    public function getConflictedModules()
    {
        $modules = Mage::getConfig()->getNode('modules')->asArray();

        $conflictedModules = array(
            '/TBT_Enhancedgrid/i' => '',
            '/warp/i' => '',
            '/Auctionmaid_/i' => '',

            '/Exactor_Tax/i' => '',
            '/Exactory_Core/i' => '',
            '/Exactor_ExactorSettings/i' => '',
            '/Exactor_Sales/i' => '',
            '/Aoe_AsyncCache/i' => '',
            '/Idev_OneStepCheckout/i' => '',

            '/Mercent_Sales/i' => '',
            '/Webtex_Fba/i' => '',

            '/MW_FreeGift/i' => 'last item in combined amazon orders has zero price
                                 (observing event sales_quote_product_add_after)'
        );

        $result = array();
        foreach($conflictedModules as $expression=>$description) {

            foreach ($modules as $module => $data) {
                if (preg_match($expression, $module)) {
                    $result[$module] = array_merge($data, array('description'=>$description));
                }
            }
        }

        return $result;
    }

    public function isTinyMceAvailable()
    {
        if ($this->isCommunityEdition()) {
            return version_compare($this->getVersion(false), '1.4.0.0', '>=');
        }
        return true;
    }

    public function getBaseCurrency()
    {
        return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }

    //----------------------------------------

    public function isSecretKeyToUrl()
    {
        return (bool)Mage::getStoreConfigFlag('admin/security/use_form_key');
    }

    public function getCurrentSecretKey()
    {
        if (!$this->isSecretKeyToUrl()) {
            return '';
        }
        return Mage::getSingleton('adminhtml/url')->getSecretKey();
    }

    // ########################################

    public function isDeveloper()
    {
        return (bool)Mage::getIsDeveloperMode();
    }

    public function isCronWorking()
    {
        $minDateTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $minDateTime->modify('-1 day');
        $minDateTime = Mage::helper('M2ePro')->getDate($minDateTime->format('U'));

        $collection = Mage::getModel('cron/schedule')->getCollection();
        $collection->addFieldToFilter('executed_at',array('gt'=>$minDateTime));

        return $collection->getSize() > 0;
    }

    public function getBaseUrl()
    {
        return str_replace('index.php/','',Mage::getBaseUrl());
    }

    public function getLocale()
    {
        $localeComponents = explode('_' , Mage::app()->getLocale()->getLocale());
        return strtolower($localeComponents[0]);
    }

    public function getTranslatedCountryName($countryId, $localeCode = 'en_US')
    {
        /** @var $locale Mage_Core_Model_Locale */
        $locale = Mage::getSingleton('core/locale');
        if ($locale->getLocaleCode() != $localeCode) {
            $locale->setLocaleCode($localeCode);
        }

        return $locale->getCountryTranslation($countryId);
    }

    public function getCountries()
    {
        $unsortedCountries = Mage::getModel('directory/country_api')->items();

        $unsortedCountriesNames = array();
        foreach($unsortedCountries as $country) {
            $unsortedCountriesNames[] = $country['name'];
        }

        sort($unsortedCountriesNames, SORT_STRING);

        $sortedCountries = array();
        foreach($unsortedCountriesNames as $name) {
            foreach($unsortedCountries as $country) {
                if ($country['name'] == $name) {
                    $sortedCountries[] = $country;
                    break;
                }
            }
        }

        return $sortedCountries;
    }

    public function addGlobalNotification($title,
                                          $description,
                                          $type = Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL,
                                          $url = NULL)
    {
        $dataForAdd = array(
            'title' => $title,
            'description' => $description,
            'url' => !is_null($url) ? $url : 'http://m2epro.com/?'.sha1($title),
            'severity' => $type,
            'date_added' => now()
        );

        Mage::getModel('adminnotification/inbox')->parse(array($dataForAdd));
    }

    // ########################################

    public function getRewrites($entity = 'models')
    {
        $config = Mage::getConfig()->getNode('global/' . $entity)->children();
        $rewrites = array();

        foreach ($config as $node) {
            foreach ($node->rewrite as $rewriteNode) {
                foreach ($rewriteNode->children() as $rewrite) {
                    if (!$node->class) {
                        continue;
                    }

                    $classNameParts = explode('_', $rewrite->getName());
                    foreach ($classNameParts as &$part) {
                        $part = strtolower($part);
                        $part{0} = strtoupper($part{0});
                    }

                    $classNameParts = array_merge(array($node->class), $classNameParts);

                    $rewrites[] = array(
                        'from' => implode('_', $classNameParts),
                        'to'   => (string)$rewrite
                    );
                }
            }
        }

        return $rewrites;
    }

    //-----------------------------------------

    public function getLocalPoolOverwrites()
    {
        $paths = array(
            'app/code/local/Mage',
            'app/code/local/Zend',
            'app/code/local/Ess'
        );

        foreach ($paths as &$patch) {
            $patch = Mage::getBaseDir() . DS . $patch;
        }

        $overwritesResult = array();
        foreach ($paths as $path) {

            $overwritesResult = array_merge(
                $overwritesResult,
                $this->getLocalPoolOverwritesRec($path)
            );
        }

        $result = array();
        foreach ($overwritesResult as $item) {

            $isOriginalCoreFileExist = is_file(str_replace('/local/', '/core/', $item));
            $isOriginalCommunityFileExist = is_file(str_replace('/local/', '/community/', $item));

            if ($isOriginalCoreFileExist || $isOriginalCommunityFileExist) {
                $result[] = str_replace(Mage::getBaseDir() . DS,'',$item);
            }
        }

        return $result;
    }

    private function getLocalPoolOverwritesRec($path)
    {
        $overridesResult = array();

        if (!is_dir($path)) {
            return array();
        }

        $folderItems = scandir($path);
        foreach ($folderItems as $folderItem) {

            if (is_file($path . DS . $folderItem)) {
                $overridesResult[] = $path . DS . $folderItem;
            }

            if ($folderItem != '.' && $folderItem != '..' && is_dir($path . DS . $folderItem)) {
                $overridesResult = array_merge(
                    $overridesResult,
                    $this->getLocalPoolOverwritesRec($path . DS . $folderItem)
                );
            }
        }

        return $overridesResult;
    }

    // ########################################

    public function clearMenuCache()
    {
        Mage::app()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array(Mage_Adminhtml_Block_Page_Menu::CACHE_TAGS)
        );
    }

    public function clearCache()
    {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    // ########################################
}