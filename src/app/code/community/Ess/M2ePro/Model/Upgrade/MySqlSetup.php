<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup extends Mage_Core_Model_Resource_Setup
{
    private $moduleTables = array();

    //####################################

    public function __construct($resourceName)
    {
        // Get needed mysql tables
        $tempTables = Mage::helper('M2ePro/Module')->getMySqlTables();
        $tempTables = array_merge($this->getMySqlTablesV3(),$tempTables);
        $tempTables = array_merge($this->getMySqlTablesWhichWereRenamed(),$tempTables);
        $tempTables = array_values(array_unique($tempTables));

        // Sort by length tables
        do {
            $hasChanges = false;
            for ($i=0;$i<count($tempTables)-1; $i++) {
                if (strlen($tempTables[$i]) < strlen($tempTables[$i+1])) {
                    $temp = $tempTables[$i];
                    $tempTables[$i] = $tempTables[$i+1];
                    $tempTables[$i+1] = $temp;
                    $hasChanges = true;
                }
            }
        } while ($hasChanges);

        // Prepare sql tables
        //--------------------
        foreach ($tempTables as $table) {
            $this->moduleTables[$table] = $this->getTable($table);
        }
        //--------------------

        parent::__construct($resourceName);
    }

    //####################################

    public function startSetup()
    {
        return parent::startSetup();
    }

    public function endSetup()
    {
        Mage::helper('M2ePro/Module')->clearCache();
        return parent::endSetup();
    }

    //####################################

    public function run($sql)
    {
        if (trim($sql) == '') {
            return $this;
        }
        $sql = $this->prepareSql($sql);
        $this->_conn->multi_query($sql);
        return $this;
    }

    public function runSqlFile($path)
    {
        if (!is_file($path)) {
            return $this;
        }
        $sql = file_get_contents($path);
        return $this->run($sql);
    }

    //####################################

    public function getModuleTables()
    {
        return $this->moduleTables;
    }

    public function getRelatedSqlFilePath($pathPhpFile)
    {
        return dirname($pathPhpFile).DS.basename($pathPhpFile,'.php').'.sql';
    }

    public function removeConfigDuplicates()
    {
        $this->removeConfigDuplicatesByTable('ess_config');
        $this->removeConfigDuplicatesByTable('m2epro_config');
    }

    private function removeConfigDuplicatesByTable($tableName)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTable($tableName);

        $configRows = $connection->query("SELECT *
                                          FROM `{$tableName}`
                                          GROUP BY `group`,`key`
                                          ORDER BY `id` ASC")
                                 ->fetchAll();

        $insertQuery = '';

        foreach ($configRows as $configRow) {

            foreach ($configRow as &$temp) {
                if (is_null($temp)) {
                    $temp = 'NULL';
                } else {
                    $temp = $connection->quote($temp);
                }
            }

            $insertQuery != '' && $insertQuery .= ' , ';
            $insertQuery .= "({$configRow['group']}, {$configRow['key']}, {$configRow['value']},
                             {$configRow['notice']}, {$configRow['update_date']}, {$configRow['create_date']})";
        }

        if (!empty($insertQuery)) {

            $insertQuery = "INSERT INTO `{$tableName}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`)
                            VALUES ".$insertQuery.';';

            $connection->query("TRUNCATE `{$tableName}`");
            $connection->query($insertQuery);
        }
    }

    //####################################

    private function prepareSql($sql)
    {
        foreach ($this->moduleTables as $tableFrom=>$tableTo) {
            $sql = str_replace(' `'.$tableFrom.'`',' `'.$tableTo.'`',$sql);
            $sql = str_replace(' '.$tableFrom,' `'.$tableTo.'`',$sql);
        }
        return $sql;
    }

    private function getMySqlTablesV3()
    {
        return array(
            'ess_config',
            'm2epro_accounts',
            'm2epro_accounts_store_categories',
            'm2epro_config',
            'm2epro_descriptions_templates',
            'm2epro_dictionary_categories',
            'm2epro_dictionary_marketplaces',
            'm2epro_dictionary_shippings',
            'm2epro_dictionary_shippings_categories',
            'm2epro_ebay_items',
            'm2epro_ebay_listings',
            'm2epro_ebay_listings_logs',
            'm2epro_ebay_orders',
            'm2epro_ebay_orders_external_transactions',
            'm2epro_ebay_orders_items',
            'm2epro_ebay_orders_logs',
            'm2epro_feedbacks',
            'm2epro_feedbacks_templates',
            'm2epro_listings',
            'm2epro_listings_categories',
            'm2epro_listings_logs',
            'm2epro_listings_products',
            'm2epro_listings_products_variations',
            'm2epro_listings_products_variations_options',
            'm2epro_listings_templates',
            'm2epro_listings_templates_calculated_shipping',
            'm2epro_listings_templates_payments',
            'm2epro_listings_templates_shippings',
            'm2epro_listings_templates_specifics',
            'm2epro_lock_items',
            'm2epro_marketplaces',
            'm2epro_messages',
            'm2epro_migration_temp',
            'm2epro_products_changes',
            'm2epro_selling_formats_templates',
            'm2epro_synchronizations_logs',
            'm2epro_synchronizations_runs',
            'm2epro_synchronizations_templates',
            'm2epro_templates_attribute_sets'
        );
    }

    private function getMySqlTablesWhichWereRenamed()
    {
        return array(
            'm2epro_amazon_category',
            'm2epro_amazon_category_specific',
            'm2epro_amazon_category_description'
        );
    }

    //####################################
}